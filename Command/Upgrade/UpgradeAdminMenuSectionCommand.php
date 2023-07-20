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

namespace BaksDev\Menu\Admin\Command\Upgrade;

use BaksDev\Core\Command\Update\ProjectUpgradeInterface;
use BaksDev\Menu\Admin\Entity\Event\MenuAdminEvent;
use BaksDev\Menu\Admin\Entity\MenuAdmin;
use BaksDev\Menu\Admin\Repository\ActiveEventMenuAdmin\ActiveMenuAdminEventRepositoryInterface;
use BaksDev\Menu\Admin\UseCase\Command\Menu\MenuAdminHandler;
use BaksDev\Menu\Admin\UseCase\Command\Menu\MenuAdminSection\MenuAdminSectionDTO;
use BaksDev\Menu\Admin\UseCase\Command\Menu\MenuAdminSection\Section\Trans\MenuAdminSectionTransDTO;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'baks:project:upgrade:menu-admin-section',
    description: 'Обновляет секции меню администратора',
)]
#[AutoconfigureTag('baks.project.upgrade')]
class UpgradeAdminMenuSectionCommand extends Command implements ProjectUpgradeInterface
{

    private MenuAdminHandler $handler;
    private EntityManagerInterface $entityManager;
    private ActiveMenuAdminEventRepositoryInterface $menuAdminEventRepository;
    private TranslatorInterface $translator;

    public function __construct(
        MenuAdminHandler $handler,
        EntityManagerInterface $entityManager,
        ActiveMenuAdminEventRepositoryInterface $menuAdminEventRepository,
        TranslatorInterface $translator
    )
    {
        parent::__construct();

        $this->handler = $handler;
        $this->entityManager = $entityManager;
        $this->menuAdminEventRepository = $menuAdminEventRepository;
        $this->translator = $translator;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->text('Обновляем меню секций администратора');

        /** @var MenuAdminEvent $Event */
        $Event = $this->menuAdminEventRepository->getEventOrNullResult();
        $this->entityManager->clear();

        $MenuAdminDTO = new MenuAdminSectionDTO();


        if($Event)
        {
            $Event->getDto($MenuAdminDTO);
        }


        $MenuAdminSectionDTO = $MenuAdminDTO->getSection();

        // Не обновляем событие, если не было изменений в секциях
        if($MenuAdminDTO->isUpdate() === false)
        {
            return Command::SUCCESS;
        }

        /** @var \BaksDev\Menu\Admin\UseCase\Command\Menu\MenuAdminSection\Section\MenuAdminSectionDTO $MenuAdminSection */
        foreach($MenuAdminSectionDTO as $MenuAdminSection)
        {
            $MenuAdminSection->setSort($MenuAdminSection->getGroup()->getType()::sort());
            $MenuAdminSectionTransDTO = $MenuAdminSection->getTranslate();


            /** @var MenuAdminSectionTransDTO $MenuAdminSectionTrans */
            foreach($MenuAdminSectionTransDTO as $MenuAdminSectionTrans)
            {

                // trans(?string $id, array $parameters = [], string $domain = null, string $locale = null)
                $name = $this->translator->trans(
                    id: $MenuAdminSection->getGroup()->getTypeValue().'.name',
                    domain: 'admin.menu.section',
                    locale: $MenuAdminSectionTrans->getLocal()->getValue()
                );

                if($MenuAdminSection->getGroup()->getTypeValue().'.name' === $name)
                {
                    throw new InvalidArgumentException(sprintf(
                        'Отсутствует файл переводов для секции "%s" в домене %s',
                        $MenuAdminSection->getGroup()->getTypeValue(),
                        'admin.menu.section.'.$MenuAdminSectionTrans->getLocal()->getValue().'.yaml'
                    ));
                }

                $MenuAdminSectionTrans->setName($name);

                $desc = $this->translator->trans(
                    id: $MenuAdminSection->getGroup()->getTypeValue().'.desc',
                    domain: 'admin.menu.section',
                    locale: $MenuAdminSectionTrans->getLocal()->getValue()
                );

                if($MenuAdminSection->getGroup()->getTypeValue().'.desc' !== $desc)
                {
                    $MenuAdminSectionTrans->setDescription($desc);
                }
            }
        }


        $MenuAdmin = $this->handler->handle($MenuAdminDTO);

        if(!$MenuAdmin instanceof MenuAdmin)
        {
            throw new InvalidArgumentException(
                sprintf('Ошибка %s при обновлении разделов меню администратора', $MenuAdmin)
            );
        }


        return Command::SUCCESS;
    }

    /** Чам выше число - тем первым в итерации будет значение */
    public static function priority(): int
    {
        return 100;
    }
}
