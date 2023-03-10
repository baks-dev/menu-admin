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

namespace BaksDev\Menu\Admin\Entity\Section;

use BaksDev\Menu\Admin\Entity\Event\MenuAdminEvent;
use BaksDev\Menu\Admin\Entity\Section\Path\MenuAdminSectionPath;
use BaksDev\Menu\Admin\Entity\Section\Trans\MenuAdminSectionTrans;
use BaksDev\Menu\Admin\Type\Section\MenuAdminSectionUid;
use BaksDev\Menu\Admin\Type\SectionGroup\MenuAdminSectionGroup;
use BaksDev\Core\Entity\EntityEvent;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/* Section */


#[ORM\Entity]
#[ORM\Table(name: 'menu_admin_section')]
class MenuAdminSection extends EntityEvent
{
	const TABLE = 'menu_admin_section';
	
	/** ID */
	#[ORM\Id]
	#[ORM\Column(type: MenuAdminSectionUid::TYPE)]
	private MenuAdminSectionUid $id;
	
	/** Связь на событие Event */
	#[ORM\ManyToOne(targetEntity: MenuAdminEvent::class, inversedBy: "section")]
	#[ORM\JoinColumn(name: 'event', referencedColumnName: "id", nullable: true)]
	private ?MenuAdminEvent $event;
	
	#[ORM\Column(name: 'groups', type: MenuAdminSectionGroup::TYPE, length: 10)]
	private MenuAdminSectionGroup $group;
	
	/** Перевод екции */
	#[ORM\OneToMany(mappedBy: 'section', targetEntity: MenuAdminSectionTrans::class, cascade: ['all'])]
	private Collection $translate;
	
	/** Разделы */
	#[ORM\OneToMany(mappedBy: 'section', targetEntity: MenuAdminSectionPath::class, cascade: ['all'])]
	#[ORM\OrderBy(['sort' => 'ASC'])]
	private Collection $path;
	
	/** Сортировка */
	#[ORM\Column(name: 'sort', type: Types::SMALLINT, length: 3, nullable: false, options: ['default' => 500])]
	private int $sort = 500;
	
	
	public function __construct(MenuAdminEvent $event)
	{
		$this->id = new MenuAdminSectionUid();
		$this->event = $event;
	}
	
	
	public function __clone() : void
	{
		$this->id = new MenuAdminSectionUid();
	}
	
	
	public function getId() : MenuAdminSectionUid
	{
		return $this->id;
	}
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof MenuAdminSectionInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto) : mixed
	{
		
		if($dto instanceof MenuAdminSectionInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
}