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

namespace BaksDev\Menu\Admin\Repository\MenuAdmin\Tests;

use BaksDev\Menu\Admin\Repository\MenuAdmin\MenuAdminInterface;
use BaksDev\Menu\Admin\Repository\MenuAdmin\MenuAdminPathResult;
use BaksDev\Menu\Admin\Repository\MenuAdmin\MenuAdminResult;
use BaksDev\Menu\Admin\Type\Section\MenuAdminSectionUid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group menu-admin
 */
#[When(env: 'test')]
class MenuAdminRepositoryTest extends KernelTestCase
{
    public function testFind(): void
    {
        /** @var MenuAdminInterface $repository */
        $repository = self::getContainer()->get(MenuAdminInterface::class);

        $results = $repository->find();

        /** @var MenuAdminResult $result */
        foreach($results as $result)
        {
            self::assertInstanceOf(MenuAdminResult::class, $result);

            self::assertIsInt($result->getSort());
            self::assertIsString($result->getName());
            self::assertInstanceOf(MenuAdminSectionUid::class, $result->getSectionId());

            $sections = $result->getPath();

            if(false === (is_null($sections)))
            {
                /** @var MenuAdminPathResult $section */
                foreach($sections as $section)
                {
                    is_string($section->getKey()) ?: self::assertNull($section->getKey());
                    is_string($section->getHref()) ?: self::assertNull($section->getHref());
                    self::assertIsString($section->getName());
                    self::assertIsString($section->getRole());
                    self::assertIsBool($section->getModal());
                    self::assertIsBool($section->getDropdown());
                }
            }
        }

        self::assertTrue(true);
    }
}
