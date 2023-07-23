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

namespace BaksDev\Menu\Admin\Entity\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Modify\ModifyActionEnum;
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
    public const TABLE = 'menu_admin_event';

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
    #[ORM\OneToOne(mappedBy: 'event', targetEntity: MenuAdminModify::class, cascade: ['all'])]
    private MenuAdminModify $modify;

    /** Секции меню */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: MenuAdminSection::class, cascade: ['all'])]
    private Collection $section;

    public function __toString(): string
    {
        return $this->id->getValue();
    }

    public function __construct()
    {
        $this->id = new MenuAdminEventUid();
        $this->modify = new MenuAdminModify($this);
    }

    public function __clone()
    {
        $this->id = new MenuAdminEventUid();
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
        if($dto instanceof MenuAdminEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof MenuAdminEventInterface)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function isModifyActionEquals(ModifyActionEnum $action): bool
    {
        return $this->modify->equals($action);
    }

    /**
     * @return Collection
     */
    public function getSection(): Collection
    {
        return $this->section;
    }

    //	public function getUploadClass() : MenuAdminImage
    //	{
    //		return $this->image ?: $this->image = new MenuAdminImage($this);
    //	}

    //	public function getNameByLocale(Locale $locale) : ?string
    //	{
    //		$name = null;
    //
    //		/** @var MenuAdminTrans $trans */
    //		foreach($this->translate as $trans)
    //		{
    //			if($name = $trans->name($locale))
    //			{
    //				break;
    //			}
    //		}
    //
    //		return $name;
    //	}
}
