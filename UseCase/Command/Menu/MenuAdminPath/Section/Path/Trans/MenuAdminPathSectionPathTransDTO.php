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

namespace BaksDev\Menu\Admin\UseCase\Command\Menu\MenuAdminPath\Section\Path\Trans;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Menu\Admin\Entity\Section\Path\Trans\MenuAdminSectionPathTransInterface;
use ReflectionProperty;
use Symfony\Component\Validator\Constraints as Assert;

/* Перевод MenuAdminSectionPathTrans */


class MenuAdminPathSectionPathTransDTO implements MenuAdminSectionPathTransInterface
{
	/**
     * Локаль
     */
	#[Assert\NotBlank]
	private readonly Locale $local;
	
	/**
     * Название
     */
	#[Assert\NotBlank]
	private string $name;
	
	/**
     * Описание
     */
	private ?string $description;
	
	
	/**
     * Локаль
     */
	
	public function setLocal(string $local) : void
	{
		if(!(new ReflectionProperty($this::class, 'local'))->isInitialized($this))
		{
			$this->local = new Locale($local);
		}
	}
	
	
	public function getLocal() : Locale
	{
		return $this->local;
	}
	
	
	/**
     * Название
     */
	
	public function getName() : string
	{
		return $this->name;
	}
	
	
	public function setName(string $name) : void
	{
		$this->name = $name;
	}
	
	
	/**
     * Описание
     */
	
	public function getDescription() : ?string
	{
		return $this->description;
	}
	
	
	public function setDescription(?string $description) : void
	{
		$this->description = $description;
	}
	
}