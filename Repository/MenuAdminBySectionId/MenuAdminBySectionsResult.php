<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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
 *
 */

declare(strict_types=1);

namespace BaksDev\Menu\Admin\Repository\MenuAdminBySectionId;

use BaksDev\Menu\Admin\Repository\MenuAdmin\MenuAdminPathResult;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/** @see MenuAdminBySectionIdRepository */
#[Exclude]
final readonly class MenuAdminBySectionsResult
{

    public function __construct(
        private string $section_name,
        private string $path,
    ) {}

    public function getSectionName(): string
    {
        return $this->section_name;
    }

    /** @return  array<int,MenuAdminPathResult>|null */
    public function getPath(): array|null
    {
        if(is_null($this->path))
        {
            return null;
        }

        if(false === json_validate($this->path))
        {
            return null;
        }

        $path = json_decode($this->path, true, 512, JSON_THROW_ON_ERROR);

        if(null === current($path))
        {
            return null;
        }

        $menuAdminPathResults = [];
        foreach($path as $section)
        {
            // первый ключ в массиве - ключ для сортировки при сортировке в JSON_BUILD - удаляем его
            unset($section[0]);

            $menuAdminPathResults[] = new MenuAdminPathResult(...$section);
        }

        return $menuAdminPathResults;
    }

}
