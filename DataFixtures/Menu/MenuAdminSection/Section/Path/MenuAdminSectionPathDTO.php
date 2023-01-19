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

namespace BaksDev\Menu\Admin\DataFixtures\Menu\MenuAdminSection\Section\Path;

use BaksDev\Menu\Admin\Entity\Section\Path\MenuAdminSectionPathInterface;
use BaksDev\Users\Groups\Type\RolePrefix\RolePrefix;
use BaksDev\Core\Type\Locale\Locale;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/* Перевод MenuAdminSectionPath */

class MenuAdminSectionPathDTO implements MenuAdminSectionPathInterface
{
	/** Перевод раздела */
	#[Assert\Valid]
	private ArrayCollection $translate;
	
	/** Роль доступа */
	#[Assert\NotBlank]
	private RolePrefix $role;
	
	/** Path вида User:admin.index  */
	#[Assert\NotBlank]
	private string $path;
	
	/** Сортировка */
	#[Assert\NotBlank]
	#[Assert\Range(min: 1, max: 999)]
	private int $sort = 500;
	
	public function __construct()
	{
		$this->translate = new ArrayCollection();
	}
	
	
	public function getTranslate() : ArrayCollection
	{
		/* Вычисляем расхождение и добавляем неопределенные локали */
		foreach(Locale::diffLocale($this->translate) as $locale)
		{
			$TransFormDTO = new Trans\MenuAdminSectionPathTransDTO();
			$TransFormDTO->setLocal($locale);
			$this->addTranslate($TransFormDTO);
		}
		
		return $this->translate;
	}
	
	public function addTranslate(Trans\MenuAdminSectionPathTransDTO $trans) : void
	{
		if(!$this->translate->contains($trans))
		{
			$this->translate->add($trans);
		}
	}
	
	public function removeTranslate(Trans\MenuAdminSectionPathTransDTO $trans) : void
	{
		$this->translate->removeElement($trans);
	}
	
	
	
	/** Роль доступа */
	
	
	public function getRole() : RolePrefix
	{
		return $this->role;
	}

	public function setRole(RolePrefix $role) : void
	{
		$this->role = $role;
	}
	
	
	/** Path вида User:admin.index  */
	
	
	public function getPath() : string
	{
		return $this->path;
	}

	public function setPath(string $path) : void
	{
		$this->path = $path;
	}
	
	/** Сортировка */
	
	
	public function getSort() : int
	{
		return $this->sort;
	}
	
	public function setSort(int $sort) : void
	{
		$this->sort = $sort;
	}
	
	
	
	
}