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

namespace BaksDev\Menu\Admin\Entity;

use BaksDev\Menu\Admin\Entity\Event\MenuAdminEvent;
use BaksDev\Menu\Admin\Type\Event\MenuAdminEventUid;
use BaksDev\Menu\Admin\Type\Id\MenuAdminIdentificator;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/* MenuAdmin */


#[ORM\Entity]
#[ORM\Table(name: 'menu_admin')]
class MenuAdmin
{
    public const TABLE = 'menu_admin';

    /**
     * Идентификатор корня
     */
    #[Assert\NotBlank]
    #[Assert\Length(max: 10)]
    #[ORM\Id]
    #[ORM\Column(type: MenuAdminIdentificator::TYPE, nullable: false)]
    private MenuAdminIdentificator $id;

    /**
     * Идентификатор События
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: MenuAdminEventUid::TYPE, unique: true)]
    private MenuAdminEventUid $event;


    public function __construct()
    {
        $this->id = new MenuAdminIdentificator();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): MenuAdminIdentificator
    {
        return $this->id;
    }


    public function getEvent(): MenuAdminEventUid
    {
        return $this->event;
    }


    public function setEvent(MenuAdminEventUid|MenuAdminEvent $event): void
    {
        $this->event = $event instanceof MenuAdminEvent ? $event->getId() : $event;
    }

}