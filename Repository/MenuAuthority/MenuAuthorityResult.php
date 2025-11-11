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

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/** @see MenuAuthorityRepository */
#[Exclude]
final readonly class MenuAuthorityResult
{

    public function __construct(
        private string $profile,
        private bool $active,
        private string $profile_username,
        private string $authority,
        private string $authority_username,
        private string $authority_domain,

    ) {}

    public function getAuthority(): UserProfileUid
    {
        return new UserProfileUid($this->authority);
    }

    public function getProfile(): UserProfileUid
    {
        return new UserProfileUid($this->profile);
    }

    public function getActive(): bool
    {
        return $this->active === true;
    }

    public function getAuthorityUsername(): string
    {
        return $this->authority_username;
    }

    public function getProfileUsername(): string
    {
        return $this->profile_username;
    }

    public function getAuthorityDomain(): string|false
    {
        return empty($this->authority_domain) ? false : 'https://'.str_replace('https://', '', $this->authority_domain);
    }
}
