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

use BaksDev\Menu\Admin\Repository\MenuAdmin\MenuAdminRepositoryInterface;
use BaksDev\Menu\Admin\Repository\MenuAuthority\MenuAuthorityRepositoryInterface;
use BaksDev\Users\User\Repository\GetUserById\GetUserByIdInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MenuAdminExtension extends AbstractExtension
{
    private MenuAdminRepositoryInterface $MenuAdmin;
    private Security $security;
    private MenuAuthorityRepositoryInterface $menuAuthority;
    private string $project_dir;
    private GetUserByIdInterface $getUserById;

    public function __construct(
        #[Autowire('%kernel.project_dir%')] string $project_dir,
        MenuAdminRepositoryInterface $repository,
        MenuAuthorityRepositoryInterface $menuAuthority,
        GetUserByIdInterface $getUserById,
        Security $security,
    )
    {
        $this->MenuAdmin = $repository;
        $this->security = $security;
        $this->menuAuthority = $menuAuthority;
        $this->project_dir = $project_dir;
        $this->getUserById = $getUserById;
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
        $token = $this->security->getToken();
        $user = $token instanceof SwitchUserToken ? $token->getOriginalToken()->getUser() : $token?->getUser();


        /** Получаем активный профиль пользовтаеля */

        $user = $user ? $this->getUserById->get($user->getId()) : null;

        /** Если авторизован администратор ресурса - подгружаем профили */

        $authority = null;

        if($user)
        {
            if(in_array('ROLE_ADMIN', $user?->getRoles()))
            {
                $authority = $this->menuAuthority->fetchAllMenuAuthorityAssociative($token->getUser()?->getProfile());
            }
            else
            {
                $authority = $this->menuAuthority->fetchAllMenuAuthorityAssociative($user?->getProfile());
            }
        }

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
                //'user' => $user
            ]);
    }

}