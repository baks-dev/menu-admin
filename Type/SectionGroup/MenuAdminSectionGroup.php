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

use BaksDev\Menu\Admin\Type\SectionGroup\Group\Collection\MenuAdminSectionGroupCollectionInterface;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;

final class MenuAdminSectionGroup
{
    public const TYPE = 'menu_admin_section_group';

    private ?MenuAdminSectionGroupCollectionInterface $type = null;


    public function __construct(self|string|MenuAdminSectionGroupCollectionInterface $type)
    {

        if($type instanceof MenuAdminSectionGroupCollectionInterface)
        {
            $this->type = $type;
            return;
        }

        if($type instanceof $this)
        {
            $this->type = $type->getType();
            return;
        }

        if(is_string($type))
        {

            /** @var MenuAdminSectionGroupCollectionInterface $class */
            foreach(self::getDeclaredSectionGroupType() as $class)
            {
                if($class::equals($type))
                {
                    $this->type = new $class;
                    return;
                }
            }
        }


        throw new InvalidArgumentException(sprintf('Not found Menu Section Group %s', $type));

    }


    public function __toString(): string
    {
        return $this->type ? $this->type->getvalue() : '';
    }


    /** Возвращает значение ColorsInterface */
    public function getType(): MenuAdminSectionGroupCollectionInterface
    {
        return $this->type;
    }


    /** Возвращает значение ColorsInterface */
    public function getTypeValue(): string
    {
        return $this->type->getValue();
    }


    public static function cases(): array
    {
        $case = [];

        foreach(self::getDeclaredSectionGroupType() as $type)
        {
            /** @var MenuAdminSectionGroupCollectionInterface $type */
            $types = new $type;
            $case[$types::sort()] = new self($types);
        }

        ksort($case);

        return $case;
    }


    public static function getDeclaredSectionGroupType(): array
    {
        return array_filter(get_declared_classes(), static function($className) {
            return in_array(MenuAdminSectionGroupCollectionInterface::class, class_implements($className), true);
        });
    }


    public static function diffType(ArrayCollection|array $diffArray)
    {
        $search = [];

        foreach($diffArray as $item)
        {
            $search[] = $item->getGroup();
        }

        /* Вычисляем расхождение массивов */
        return array_diff(self::cases(), $search);
    }







    //	public function __toString() : string
    //	{
    //		return $this->type->value;
    //	}




    //	/** Возвращает значение (value) String */
    //	public function getValue() : string
    //	{
    //		return $this->type->value;
    //	}
    //
    //
    //	/** Возвращает ключ (name) Enum */
    //	public function getName() : string
    //	{
    //		return $this->type->name;
    //	}
    //






}