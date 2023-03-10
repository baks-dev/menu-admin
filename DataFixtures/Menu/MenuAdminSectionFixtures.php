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

namespace BaksDev\Menu\Admin\DataFixtures\Menu;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Menu\Admin\Entity as EntityMenuAdmin;
use BaksDev\Menu\Admin\Repository\ActiveEventMenuAdmin\ActiveMenuAdminEventRepositoryInterface;
use BaksDev\Menu\Admin\Type\Id\MenuAdminIdentificator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class MenuAdminSectionFixtures extends Fixture
{
	
	private MenuAdminFixturesHandler $handler;
	
	private EntityManagerInterface $entityManager;
	
	private ActiveMenuAdminEventRepositoryInterface $menuAdminEventRepository;
	
	
	public function __construct(
		MenuAdminFixturesHandler $handler,
		EntityManagerInterface $entityManager,
		ActiveMenuAdminEventRepositoryInterface $menuAdminEventRepository,
	)
	{
		$this->handler = $handler;
		$this->entityManager = $entityManager;
		$this->menuAdminEventRepository = $menuAdminEventRepository;
	}
	
	
	public function load(ObjectManager $manager)
	{
		# php bin/console doctrine:fixtures:load --append
		
		/* ???????????????????? ?????? ???????? */
		$cache = new FilesystemAdapter('CacheMenuAdmin');
		foreach(Locale::cases() as $locale)
		{
			$cache->delete(MenuAdminIdentificator::TYPE.$locale);
		}
		
		/** @var EntityMenuAdmin\Event\MenuAdminEvent $Event */
		$Event = $this->menuAdminEventRepository->getEventOrNullResult();
		$this->entityManager->clear();
		
		$MenuAdminDTO = new MenuAdminSection\MenuAdminDTO();
		
		if($Event)
		{
			$Event->getDto($MenuAdminDTO);
		}
		
		$MenuAdminSectionDTO = $MenuAdminDTO->getSection();
		
		/* ???? ?????????????????? ??????????????, ???????? ???? ???????? ?????????????????? ?? ?????????????? */
		if($MenuAdminDTO->isUpdate() === false)
		{
			return;
		}
		
		/* ???????? ?????????????????? ???????????? */
		$sectionTrans = require __DIR__.'/MenuAdminSection/Section/Trans/translate.php';
		
		/** @var MenuAdminSection\Section\MenuAdminSectionDTO $MenuAdminSection */
		foreach($MenuAdminSectionDTO as $MenuAdminSection)
		{
			$MenuAdminSection->setSort($sectionTrans[$MenuAdminSection->getGroup()->getValue()]['sort']);
			$MenuAdminSectionTransDTO = $MenuAdminSection->getTranslate();
			
			/** @var MenuAdminSection\Section\Trans\MenuAdminSectionTransDTO $MenuAdminSectionTrans */
			foreach($MenuAdminSectionTransDTO as $MenuAdminSectionTrans)
			{
				$MenuAdminSectionTrans->setName(
					$sectionTrans[$MenuAdminSection->getGroup()->getValue()][$MenuAdminSectionTrans->getLocal()
						->getValue()]['name']
				);
				$MenuAdminSectionTrans->setDescription(
					$sectionTrans[$MenuAdminSection->getGroup()->getValue()][$MenuAdminSectionTrans->getLocal()
						->getValue()]['desc']
				);
			}
		}
		
		$this->handler->handle($MenuAdminDTO);
	}
	
}