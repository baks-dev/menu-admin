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

final class MenuAuthorityRepository implements MenuAuthorityRepositoryInterface
{
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(DBALQueryBuilder $DBALQueryBuilder)
    {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    /**
     * Возвращает доверенные профили активного профиля пользователя
     */
    public function findAll(?UserProfileUid $profile): ?array
    {
        if(!class_exists(ProfileGroupUsers::class) || $profile === null)
        {
            return null;
        }

        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb->select('usr.authority');
        $qb->addSelect('usr.profile');
        $qb->addSelect('usr.profile = :profile AS active ');
        $qb->from(ProfileGroupUsers::TABLE, 'usr');

        $qb
            ->where('usr.profile = :profile')
            ->setParameter('profile', $profile, UserProfileUid::TYPE);

        $qb->join(
            'usr',
            UserProfile::TABLE, 'authority_profile',
            'authority_profile.id = usr.authority'
        );

        $qb->addSelect('authority_personal.username AS authority_username');
        $qb->leftJoin(
            'authority_profile',
            UserProfilePersonal::TABLE, 'authority_personal',
            'authority_personal.event = authority_profile.event'
        );

        $qb->leftJoin(
            'usr',
            UserProfile::TABLE, 'profile',
            'profile.id = usr.profile'
        );

        $qb->addSelect('profile_personal.username AS profile_username');
        $qb->leftJoin(
            'profile',
            UserProfilePersonal::TABLE, 'profile_personal',
            'profile_personal.event = profile.event'
        );

        return $qb
            ->enableCache('menu-admin', 86400)
            ->fetchAllAssociative();
    }



}