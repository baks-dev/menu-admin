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
 */

declare(strict_types=1);

namespace BaksDev\Menu\Admin\Repository\MenuAuthority;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\Group\Entity\Users\ProfileGroupUsers;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Generator;
use InvalidArgumentException;

final class MenuAuthorityRepository implements MenuAuthorityInterface
{
    private UserProfileUid|false $profile = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder
    ) {}

    /**
     * Фильтр по профилю пользователя
     */
    public function onProfile(UserProfile|UserProfileUid|string $profile): self
    {
        if(empty($profile))
        {
            $this->profile = false;
            return $this;
        }

        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        $this->profile = $profile;
        return $this;
    }

    /**
     * Возвращает доверенные профили активного профиля пользователя
     * @return Generator<int, MenuAuthorityResult>|false
     */
    public function findAllResults(): Generator|false
    {
        if(false === ($this->profile instanceof UserProfileUid))
        {
            throw new InvalidArgumentException(sprintf(
                'Некорректной тип для параметра $this->profile: `%s`. Ожидаемый тип %s',
                var_export($this->profile, true), UserProfileUid::class
            ));
        }

        $builder = $this->builder();

        $builder->enableCache('profile-group-users', 86400);

        $result = $builder->fetchAllHydrate(MenuAuthorityResult::class);

        return (true === $result->valid()) ? $result : false;
    }

    /**
     * Возвращает доверенные профили активного профиля пользователя
     */
    public function findAll(?UserProfileUid $profile): ?array
    {
        $this->onProfile($profile);

        $builder = $this->builder();

        if(is_null($builder))
        {
            return null;
        }

        $builder->enableCache('profile-group-users', 86400);

        return $builder->fetchAllAssociative();
    }

    private function builder(): DBALQueryBuilder|null
    {
        if(
            false === class_exists(ProfileGroupUsers::class) ||
            false === ($this->profile instanceof UserProfileUid)
        )
        {
            return null;
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal->select('usr.authority');
        $dbal->addSelect('usr.profile');
        $dbal->addSelect('usr.profile = :profile AS active ');
        $dbal->from(ProfileGroupUsers::class, 'usr');

        $dbal->join(
            'usr',
            UserProfile::class, 'authority_profile',
            'authority_profile.id = usr.authority'
        );

        $dbal->addSelect('authority_personal.username AS authority_username');
        $dbal->leftJoin(
            'authority_profile',
            UserProfilePersonal::class, 'authority_personal',
            'authority_personal.event = authority_profile.event'
        );

        $dbal->leftJoin(
            'usr',
            UserProfile::class, 'profile',
            'profile.id = usr.profile'
        );

        $dbal->addSelect('profile_personal.username AS profile_username');
        $dbal->leftJoin(
            'profile',
            UserProfilePersonal::class, 'profile_personal',
            'profile_personal.event = profile.event'
        );

        $dbal
            ->where('usr.profile = :profile')
            ->setParameter('profile', $this->profile, UserProfileUid::TYPE);

        return $dbal;
    }
}