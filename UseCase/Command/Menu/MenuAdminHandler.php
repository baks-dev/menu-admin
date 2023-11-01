<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Menu\Admin\UseCase\Command\Menu;

use BaksDev\Core\Entity\AbstractHandler;
use BaksDev\Menu\Admin\Entity\Event\MenuAdminEvent;
use BaksDev\Menu\Admin\Entity\Event\MenuAdminEventInterface;
use BaksDev\Menu\Admin\Entity\MenuAdmin;
use BaksDev\Menu\Admin\Messenger\MenuAdminMessage;
use BaksDev\Menu\Admin\Type\Id\MenuAdminIdentificator;
use DomainException;

final class MenuAdminHandler extends AbstractHandler
{
    public function handle(MenuAdminEventInterface $command): string|MenuAdmin
    {
        /** Валидация WbSupplyNewDTO  */
        $this->validatorCollection->add($command);

        $this->main = new MenuAdmin();
        $this->event = new MenuAdminEvent();

        try
        {
            $command->getEvent() ? $this->preUpdate($command, true) : $this->prePersist($command);
        }
        catch(DomainException $errorUniqid)
        {
            return $errorUniqid;
        }


        /** Валидация всех объектов */
        if($this->validatorCollection->isInvalid())
        {
            return $this->validatorCollection->getErrorUniqid();
        }

        $this->entityManager->flush();

        /* Отправляем сообщение в шину */
        $this->messageDispatch->dispatch(
            message: new MenuAdminMessage($this->main->getEvent(), $command->getEvent()),
            transport: 'menu-admin'
        );

        return $this->main;
    }













    /** @see MenuAdmin */
    public function old_handle(MenuAdminEventInterface $command,): string|MenuAdmin
    {



        /**
         *  Валидация MenuAdminDTO
         */
        $errors = $this->validator->validate($command);

        if(count($errors) > 0)
        {
            $uniqid = uniqid('', false);
            $errorsString = (string) $errors;
            $this->logger->error($uniqid.': '.$errorsString);
            return $uniqid;
        }


        if($command->getEvent())
        {
            $EventRepo = $this->entityManager->getRepository(MenuAdminEvent::class)->find(
                $command->getEvent()
            );

            if($EventRepo === null)
            {
                $uniqid = uniqid('', false);
                $errorsString = sprintf(
                    'Not found %s by id: %s',
                    MenuAdminEvent::class,
                    $command->getEvent()
                );
                $this->logger->error($uniqid.': '.$errorsString);

                return $uniqid;
            }

            $EventRepo->setEntity($command);
            $EventRepo->setEntityManager($this->entityManager);
            $Event = $EventRepo->cloneEntity();
        }
        else
        {
            $Event = new MenuAdminEvent();
            $Event->setEntity($command);
            $this->entityManager->persist($Event);
        }

        //        $this->entityManager->clear();
        //        $this->entityManager->persist($Event);


        $Main = $this->entityManager->getRepository(MenuAdmin::class)->find(new MenuAdminIdentificator());

        if(!$Main)
        {
            $Main = new MenuAdmin();
            $this->entityManager->persist($Main);
        }

        $Event->setMain($Main);
        $Main->setEvent($Event);


        /**
         * Валидация Event
         */

        $errors = $this->validator->validate($Event);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [__FILE__.':'.__LINE__]);

            return $uniqid;
        }

        /**
         * Валидация Main
         */
        $errors = $this->validator->validate($Main);

        if(count($errors) > 0)
        {
            $uniqid = uniqid('', false);
            $errorsString = (string) $errors;
            $this->logger->error($uniqid.': '.$errorsString);
            return $uniqid;
        }


        $this->entityManager->flush();

        /* Отправляем сообщение в шину */
        $this->messageDispatch->dispatch(
            message: new MenuAdminMessage($Main->getEvent(), $command->getEvent()),
            transport: 'menu-admin'
        );

        // 'menu_admin_high'
        return $Main;
    }
}