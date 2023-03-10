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

namespace BaksDev\Menu\Admin\Entity\Section\Path;

use BaksDev\Menu\Admin\Entity\Section\MenuAdminSection;
use BaksDev\Menu\Admin\Type\Path\MenuAdminSectionPathUid;
use BaksDev\Users\Groups\Role\Type\RolePrefix\RolePrefix;
use BaksDev\Core\Entity\EntityEvent;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/** Пунты меню MenuAdminSectionPath */
#[ORM\Entity]
#[ORM\Table(name: 'menu_admin_section_path')]
class MenuAdminSectionPath extends EntityEvent
{
	public const TABLE = 'menu_admin_section_path';
	
	/** ID */
	#[ORM\Id]
	#[ORM\Column(type: MenuAdminSectionPathUid::TYPE)]
	private MenuAdminSectionPathUid $id;
	
	/** Связь на секцию */
	#[ORM\ManyToOne(targetEntity: MenuAdminSection::class, inversedBy: "path")]
	#[ORM\JoinColumn(name: 'section', referencedColumnName: "id")]
	private MenuAdminSection $section;
	
	/** Перевод раздела */
	#[ORM\OneToMany(mappedBy: 'path', targetEntity: Trans\MenuAdminSectionPathTrans::class, cascade: ['all'])]
	private Collection $translate;
	
	/** Роль доступа */
	#[ORM\Column(type: RolePrefix::TYPE, length: 100, options: ['default' => 'ROLE_ADMIN'])]
	private RolePrefix $role;
	
	/** Path вида User:admin.index  */
	#[ORM\Column(type: Types::STRING, length: 255, options: ['default' => 'Pages:admin.index'])]
	private string $path;
	
	/** Сортировка */
	#[ORM\Column(type: Types::SMALLINT, length: 3, options: ['default' => 500])]
	private int $sort;
	
	
	public function __construct(MenuAdminSection $section)
	{
		$this->id = new MenuAdminSectionPathUid();
		
		$this->section = $section;
		$this->role = new RolePrefix('ROLE_ADMIN');
		$this->path = 'Pages:admin.index';
		$this->sort = 500;
	}
	
	
	public function __clone() : void
	{
		$this->id = new MenuAdminSectionPathUid();
	}
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof MenuAdminSectionPathInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto) : mixed
	{
		
		if($dto instanceof MenuAdminSectionPathInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
}