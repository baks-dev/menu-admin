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

namespace BaksDev\Menu\Admin\Entity\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Entity\EntityReadonly;
use BaksDev\Menu\Admin\Entity\MenuAdmin;
use BaksDev\Menu\Admin\Entity\Modify\MenuAdminModify;
use BaksDev\Menu\Admin\Entity\Section\MenuAdminSection;
use BaksDev\Menu\Admin\Type\Event\MenuAdminEventUid;
use BaksDev\Menu\Admin\Type\Id\MenuAdminIdentificator;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* MenuAdminEvent */

#[ORM\Entity]
#[ORM\Table(name: 'menu_admin_event')]
class MenuAdminEvent extends EntityEvent
{
    /** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: MenuAdminEventUid::TYPE)]
    private MenuAdminEventUid $id;

    /** ID MenuAdmin */
    #[Assert\NotBlank]
    #[Assert\Length(max: 10)]
    #[ORM\Column(type: MenuAdminIdentificator::TYPE, nullable: false)]
    private ?MenuAdminIdentificator $main = null;

    /** Модификатор */
    #[Assert\Valid]
    #[ORM\OneToOne(targetEntity: MenuAdminModify::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private MenuAdminModify $modify;

    /** Секции меню */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: MenuAdminSection::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $section;

    public function __construct()
    {
        $this->id = new MenuAdminEventUid();
        $this->modify = new MenuAdminModify($this);
    }

    public function __clone()
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): MenuAdminEventUid
    {
        return $this->id;
    }

    public function setMain(MenuAdminIdentificator|MenuAdmin $main): void
    {
        $this->main = $main instanceof MenuAdmin ? $main->getId() : $main;
    }

    public function getMain(): ?MenuAdminIdentificator
    {
        return $this->main;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof MenuAdminEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof MenuAdminEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function getSection(): Collection
    {
        return $this->section;
    }
}
