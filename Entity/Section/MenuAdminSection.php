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

namespace BaksDev\Menu\Admin\Entity\Section;

use BaksDev\Core\Entity\EntityReadonly;
use BaksDev\Menu\Admin\Entity\Event\MenuAdminEvent;
use BaksDev\Menu\Admin\Entity\Section\Path\MenuAdminSectionPath;
use BaksDev\Menu\Admin\Entity\Section\Trans\MenuAdminSectionTrans;
use BaksDev\Menu\Admin\Type\Section\MenuAdminSectionUid;
use BaksDev\Menu\Admin\Type\SectionGroup\MenuAdminSectionGroup;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* Section */


#[ORM\Entity]
#[ORM\Table(name: 'menu_admin_section')]
class MenuAdminSection extends EntityReadonly
{
    const TABLE = 'menu_admin_section';

    /** Идентификатор секции */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: MenuAdminSectionUid::TYPE)]
    private MenuAdminSectionUid $id;

    /**
     * Связь на событие
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\ManyToOne(targetEntity: MenuAdminEvent::class, inversedBy: "section")]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: "id", nullable: true)]
    private ?MenuAdminEvent $event;

    /**
     * Группа секции
     */
    #[Assert\NotBlank]
    #[Assert\Length(max: 15)]
    #[ORM\Column(name: 'groups', type: MenuAdminSectionGroup::TYPE)]
    private MenuAdminSectionGroup $group;

    /**
     * Перевод cекции
     */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: MenuAdminSectionTrans::class, mappedBy: 'section', cascade: ['all'])]
    private Collection $translate;

    /**
     * Разделы
     */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: MenuAdminSectionPath::class, mappedBy: 'section', cascade: ['all'])]
    #[ORM\OrderBy(['sort' => 'ASC'])]
    private Collection $path;

    /** Сортировка */
    #[Assert\NotBlank]
    #[Assert\Range(min: 0, max: 999)]
    #[ORM\Column(name: 'sort', type: Types::SMALLINT, nullable: false, options: ['default' => 500])]
    private int $sort = 500;


    public function __construct(MenuAdminEvent $event)
    {
        $this->id = new MenuAdminSectionUid();
        $this->event = $event;
    }


    public function __clone(): void
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }


    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof MenuAdminSectionInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {

        if($dto instanceof MenuAdminSectionInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    /**
     * Id
     */
    public function getId(): MenuAdminSectionUid
    {
        return $this->id;
    }


    /**
     * Event
     */
    public function getEvent(): ?MenuAdminEvent
    {
        return $this->event;
    }
}