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

namespace BaksDev\Menu\Admin\Repository\MenuAdminBySectionId\Tests;

use BaksDev\Menu\Admin\Repository\MenuAdmin\MenuAdminInterface;
use BaksDev\Menu\Admin\Repository\MenuAdmin\MenuAdminPathResult;
use BaksDev\Menu\Admin\Repository\MenuAdminBySectionId\MenuAdminBySectionIdInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group menu-admin
 */
#[When(env: 'test')]
class MenuAdminBySectionIdRepositoryTest extends KernelTestCase
{
    public function testFindOne(): void
    {

        /** @var MenuAdminInterface $menuAdminRepository */
        $menuAdminRepository = self::getContainer()->get(MenuAdminInterface::class);
        $menuAdmin = $menuAdminRepository->findAll();

        /** @var MenuAdminBySectionIdInterface $menuAdminSectionRepository */
        $menuAdminSectionRepository = self::getContainer()->get(MenuAdminBySectionIdInterface::class);

        foreach($menuAdmin as $menu)
        {
            $menuAdminSections = $menuAdminSectionRepository
                ->findOne($menu->getSectionId());

            self::assertNotFalse($menuAdminSections);

            self::assertIsString($menuAdminSections->getSectionName());
            $path = $menuAdminSections->getPath();

            if(is_null($path))
            {
                continue;
            }

            /** @var MenuAdminPathResult $path */
            foreach($path as $section)
            {
                is_string($section->getKey()) ?: self::assertNull($section->getKey());
                is_string($section->getHref()) ?: self::assertNull($section->getHref());
                self::assertIsString($section->getName());
                self::assertIsString($section->getRole());
                self::assertIsBool($section->getModal());
                self::assertIsBool($section->getDropdown());
            }
        }

        self::assertTrue(true);
    }
}
