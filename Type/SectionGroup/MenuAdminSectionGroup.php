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

namespace BaksDev\Menu\Admin\Type\SectionGroup;

use Doctrine\Common\Collections\ArrayCollection;

final class MenuAdminSectionGroup
{
	public const TYPE = 'menu_admin_section_group';
	
	private MenuAdminSectionGroupEnum $type;
	
	public function __construct(string|MenuAdminSectionGroupEnum $type)
	{
		if($type instanceof MenuAdminSectionGroupEnum)
		{
			$this->type = $type;
		}
		else
		{
			$this->type = MenuAdminSectionGroupEnum::from($type);
		}
	}
	
	
	public function __toString() : string
	{
		return $this->type->value;
	}
	
	/** Возвращает Enum   */
	public function getType() : MenuAdminSectionGroupEnum
	{
		return $this->type;
	}
	
	/** Возвращает значение (value) String */
	public function getValue() : string
	{
		return $this->type->value;
	}
	
	/** Возвращает ключ (name) Enum */
	public function getName() : string
	{
		return $this->type->name;
	}
	
	/** Возвращает массив из значнией ColorEnum */
	public static function cases() : array
	{
		$case = null;
		
		foreach(MenuAdminSectionGroupEnum::cases() as $type)
		{
			$case[] = new self($type);
		}
		
		return $case;
	}
	
	
	public static function diffLocale(ArrayCollection|array $diffArray)
	{
		$search = [];
		
		foreach($diffArray as $item)
		{
			$search[] = $item->getGroup();
		}
		
		/* Вычисляем расхождение массивов */
		return array_diff(self::cases(), $search);
	}
}