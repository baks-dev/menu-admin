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

namespace BaksDev\Menu\Admin\DataFixtures\Menu\MenuAdminSection;

use BaksDev\Menu\Admin\Entity\Event\MenuAdminEventInterface;
use BaksDev\Menu\Admin\Type\Event\MenuAdminEventUid;
use BaksDev\Menu\Admin\Type\SectionGroup\MenuAdminSectionGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

final class MenuAdminDTO implements MenuAdminEventInterface
{
	
	/** Идентификатор события */
	#[Assert\Uuid]
	private ?MenuAdminEventUid $id = null;
	
	/** Секции меню */
	#[Assert\Valid]
	private ArrayCollection $section;
	
	
	private bool $update = false;

	public function __construct()
	{
		$this->section = new ArrayCollection();
	}
	
	
	/** Идентификатор события */
	
	
	public function getEvent() : ?MenuAdminEventUid
	{
		return $this->id;
	}
	
	
	/** Секции меню */
	
	
	public function getSection() : ArrayCollection
	{
		/* Вычисляем расхождение и добавляем неопределенные локали */
		foreach(MenuAdminSectionGroup::diffLocale($this->section) as $section)
		{
			$MenuAdminSectionGroupDTO = new Section\MenuAdminSectionDTO();
			$MenuAdminSectionGroupDTO->setGroup($section);
			$this->addSection($MenuAdminSectionGroupDTO);
			
			$this->update = true;
		}

		return $this->section;
	}
	
	public function addSection(Section\MenuAdminSectionDTO $section) : void
	{
		if(!$this->section->contains($section))
		{
			$this->section->add($section);
		}
	}
	
	public function removeSection(Section\MenuAdminSectionDTO $section) : void
	{
		$this->section->removeElement($section);
		
	}
	
	/**
	 * @return bool
	 */
	public function isUpdate() : bool
	{
		return $this->update;
	}

}