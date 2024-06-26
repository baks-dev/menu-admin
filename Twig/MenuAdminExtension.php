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

namespace BaksDev\Menu\Admin\Twig;

use BaksDev\Menu\Admin\Repository\MenuAdmin\MenuAdminInterface;
use BaksDev\Menu\Admin\Repository\MenuAuthority\MenuAuthorityInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MenuAdminExtension extends AbstractExtension
{
    private MenuAdminInterface $MenuAdmin;
    private MenuAuthorityInterface $menuAuthority;
    private TokenStorageInterface $tokenStorage;
    private string $project_dir;

    public function __construct(
        #[Autowire('%kernel.project_dir%')] string $project_dir,
        MenuAdminInterface $repository,
        MenuAuthorityInterface $menuAuthority,
        TokenStorageInterface $tokenStorage,
    )
    {
        $this->MenuAdmin = $repository;
        $this->menuAuthority = $menuAuthority;
        $this->project_dir = $project_dir;
        $this->tokenStorage = $tokenStorage;
    }


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
        /** Меню навигации */
        $menu = $this->MenuAdmin->fetchAllAssociativeIndexed();

        /** Меню доверенностей */
        $token = $this->tokenStorage->getToken();
        $user = $token instanceof SwitchUserToken ? $token->getOriginalToken()->getUser() : $token?->getUser();

        /**
         * Если авторизован администратор ресурса - подгружаем профили
         */

        $authority = null;

        if($user)
        {
            $authority = $this->menuAuthority->findAll($user?->getProfile());
        }

        /** Подключаем если определен пользовательский шаблон */
        if(file_exists($this->project_dir.'/templates/menu-admin/twig/menu.admin.html.twig'))
        {
            return $twig->render(
                '@Template/menu-admin/twig/menu.admin.html.twig',
                context: [
                    'data' => $menu,
                    'authority' => $authority
                ]);
        }

        return $twig->render(
            '@menu-admin/twig/menu.admin.html.twig',
            context: [
                'data' => $menu,
                'authority' => $authority,
            ]);
    }

}