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
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 *
 *
 */

namespace BaksDev\Menu\Admin\Repository\EventBySectionGroup;

use BaksDev\Menu\Admin\Entity\Event\MenuAdminEvent;
use BaksDev\Menu\Admin\Entity\MenuAdmin;
use BaksDev\Menu\Admin\Entity\Section\MenuAdminSection;
use BaksDev\Menu\Admin\Type\SectionGroup\Group\Collection\MenuAdminSectionGroupCollectionInterface;
use BaksDev\Menu\Admin\Type\SectionGroup\MenuAdminSectionGroup;
use Doctrine\ORM\EntityManagerInterface;

final class MenuAdminEventBySectionGroupRepository implements MenuAdminEventBySectionGroupRepositoryInterface
{
	private EntityManagerInterface $entityManager;
	
	
	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}
	
	
	/** Метод возвращает активное событие MenuAdminEvent  */
	public function getOneOrNullResult(MenuAdminSectionGroupCollectionInterface $group) : ?MenuAdminEvent
	{
		$qb = $this->entityManager->createQueryBuilder();
		$qb->select('event');
		$qb->from(MenuAdmin::class, 'menu');
		$qb->join(MenuAdminEvent::class, 'event', 'WITH', 'event.id = menu.event');
		$qb->join(MenuAdminSection::class, 'section', 'WITH', 'section.event = event.id AND section.group = :group');
		$qb->setParameter('group', new MenuAdminSectionGroup($group), MenuAdminSectionGroup::TYPE);
		
		return $qb->getQuery()->getOneOrNullResult();
	}
	
}