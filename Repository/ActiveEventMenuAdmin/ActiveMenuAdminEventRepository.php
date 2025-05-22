<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Menu\Admin\Repository\ActiveEventMenuAdmin;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Menu\Admin\Entity\Event\MenuAdminEvent;
use BaksDev\Menu\Admin\Entity\MenuAdmin;
use BaksDev\Menu\Admin\Type\Id\MenuAdminIdentificator;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ActiveMenuAdminEventRepository implements ActiveMenuAdminEventInterface
{
    public function __construct(private ORMQueryBuilder $ORMQueryBuilder) {}

    /** Метод возвращает активное событие MenuAdminEvent  */
    public function find(): MenuAdminEvent|false
    {
        $orm = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $orm
            ->select('event')
            ->from(MenuAdmin::class, 'menu')
            ->where('menu.id = :menu')
            ->setParameter('menu', MenuAdminIdentificator::TYPE);

        $orm
            ->join(
                MenuAdminEvent::class,
                'event',
                'WITH',
                'event.id = menu.event',
            );

        return $orm->getOneOrNullResult() ?: false;
    }
}
