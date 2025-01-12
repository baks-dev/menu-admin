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

namespace BaksDev\Menu\Admin\Twig;

use BaksDev\Core\Twig\TemplateExtension;
use BaksDev\Menu\Admin\Repository\MenuAdmin\MenuAdminInterface;
use BaksDev\Menu\Admin\Repository\MenuAuthority\MenuAuthorityInterface;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MenuAdminExtension extends AbstractExtension
{
    public function __construct(
        private readonly MenuAdminInterface $MenuAdmin,
        private readonly MenuAuthorityInterface $menuAuthority,
        private readonly UserProfileTokenStorageInterface $userProfileTokenStorage,
        private readonly TemplateExtension $template,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_menu_admin',
                [$this, 'renderMenuAdmin'],
                ['needs_environment' => true, 'is_safe' => ['html']]
            ),
        ];
    }

    public function renderMenuAdmin(Environment $twig): string
    {
        $user = $this->userProfileTokenStorage->getUserCurrent();

        /** Меню навигации */
        $menu = $this->MenuAdmin->fetchAllAssociativeIndexed();

        // доверенные профили для быстрой смены
        $authority = $user ? $this->menuAuthority->findAll($this->userProfileTokenStorage->getProfileCurrent()) : null;

        $path = $this->template->extends('@menu-admin:render_menu_admin/template.html.twig');

        return $twig->render(
            $path,
            context: [
                'data' => $menu,
                'authority' => $authority,
            ]);
    }

}