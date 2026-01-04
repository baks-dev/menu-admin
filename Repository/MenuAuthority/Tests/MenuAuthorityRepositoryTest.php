<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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
 */

declare(strict_types=1);

namespace BaksDev\Menu\Admin\Repository\MenuAuthority\Tests;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Menu\Admin\Repository\MenuAuthority\MenuAuthorityInterface;
use BaksDev\Menu\Admin\Repository\MenuAuthority\MenuAuthorityResult;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('menu-admin')]
class MenuAuthorityRepositoryTest extends KernelTestCase
{
    public function testFindAll(): void
    {
        /** @var ORMQueryBuilder $ormQueryBuilder */
        $ormQueryBuilder = self::getContainer()->get(ORMQueryBuilder::class);
        $qb = $ormQueryBuilder->createQueryBuilder(self::class);

        $qb
            ->from(UserProfile::class, 'user_profile')
            ->select('user_profile.id AS profile')
            ->join(
                UserProfileEvent::class,
                'users_profile_event',
                'WITH',
                'users_profile_event.id = user_profile.event'
            )
            ->join(
                UserProfileInfo::class,
                'users_profile_info',
                'WITH',
                "
                    users_profile_info.event = users_profile_event.id AND
                    users_profile_info.status = :status"
            )
            ->setParameter('status', UserProfileStatusActive::STATUS, UserProfileStatus::TYPE);

        $profiles = $qb->getResult();

        if(is_null($profiles))
        {
            self::assertTrue(true);
        }

        /** @var MenuAuthorityInterface $menuAuthority */
        $menuAuthority = self::getContainer()->get(MenuAuthorityInterface::class);

        /** @var UserProfileUid $profile */
        foreach($profiles as $profile)
        {

            $results = $menuAuthority
                ->onProfile($profile['profile'])
                ->findAllResults();
            // ->findAll($profile['profile']);

            if(false === $results || false === $results->valid())
            {
                continue;
            }

            /** @var MenuAuthorityResult $result */
            foreach($results as $MenuAuthorityResult)
            {
                // Вызываем все геттеры
                $reflectionClass = new ReflectionClass(MenuAuthorityResult::class);
                $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

                foreach($methods as $method)
                {
                    // Методы без аргументов
                    if($method->getNumberOfParameters() === 0)
                    {
                        // Вызываем метод
                        $data = $method->invoke($MenuAuthorityResult);
                        // dump($data);
                    }
                }
            }

        }

        self::assertTrue(true);
    }

    public function testFindAllResults(): void
    {
        /** @var ORMQueryBuilder $ormQueryBuilder */
        $ormQueryBuilder = self::getContainer()->get(ORMQueryBuilder::class);

        $qb = $ormQueryBuilder->createQueryBuilder(self::class);

        $qb
            ->from(UserProfile::class, 'user_profile')
            ->select('user_profile.id AS profile')
            ->join(
                UserProfileEvent::class,
                'users_profile_event',
                'WITH',
                'users_profile_event.id = user_profile.event'
            )
            ->join(
                UserProfileInfo::class,
                'users_profile_info',
                'WITH',
                "
                    users_profile_info.event = users_profile_event.id AND
                    users_profile_info.status = :status"
            )
            ->setParameter('status', UserProfileStatusActive::STATUS, UserProfileStatus::TYPE);

        $profiles = $qb->getResult();

        if(is_null($profiles))
        {
            self::assertTrue(true);
        }

        /** @var MenuAuthorityInterface $menuAuthority */
        $menuAuthority = self::getContainer()->get(MenuAuthorityInterface::class);

        /** @var UserProfileUid $profile */
        foreach($profiles as $profile)
        {

            $results = $menuAuthority
                ->onProfile($profile['profile'])
                ->findAllResults();

            if(false === $results || false === $results->valid())
            {
                continue;
            }

            /** @var MenuAuthorityResult $result */
            foreach($results as $result)
            {
                self::assertInstanceOf(UserProfileUid::class, $result->getAuthority());
                self::assertInstanceOf(UserProfileUid::class, $result->getProfile());
                self::assertIsBool($result->getActive());
                self::assertIsString($result->getAuthorityUsername());
                self::assertIsString($result->getProfileUsername());
            }
        }

        self::assertTrue(true);
    }
}
