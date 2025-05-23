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

namespace BaksDev\Menu\Admin\Entity\Section\Path;

use BaksDev\Core\Entity\EntityReadonly;
use BaksDev\Menu\Admin\Entity\Section\MenuAdminSection;
use BaksDev\Menu\Admin\Entity\Section\Path\Key\MenuAdminSectionPathKey;
use BaksDev\Menu\Admin\Type\Path\MenuAdminSectionPathUid;
use BaksDev\Users\Profile\Group\Type\Prefix\Role\GroupRolePrefix;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/** Пункты меню MenuAdminSectionPath */
#[ORM\Entity]
#[ORM\Table(name: 'menu_admin_section_path')]
class MenuAdminSectionPath extends EntityReadonly
{
    /** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: MenuAdminSectionPathUid::TYPE)]
    private MenuAdminSectionPathUid $id;

    /** Связь на секцию */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\ManyToOne(targetEntity: MenuAdminSection::class, inversedBy: "path")]
    #[ORM\JoinColumn(name: 'section', referencedColumnName: "id")]
    private MenuAdminSection $section;

    /**
     * Перевод раздела
     */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: Trans\MenuAdminSectionPathTrans::class, mappedBy: 'path', cascade: ['all'], fetch: 'EAGER')]
    private Collection $translate;

    /**
     * Роль доступа
     */
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[ORM\Column(type: GroupRolePrefix::TYPE, options: ['default' => 'ROLE_ADMIN'])]
    private GroupRolePrefix $role;

    /**
     * Path вида User:admin.index
     */
    #[Assert\Length(max: 255)]
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $path = null;

    /** Сортировка */
    #[Assert\NotBlank]
    #[Assert\Range(min: 0, max: 999)]
    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 500])]
    private int $sort = 500;

    /**
     * Показать в выпадающем меню
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $dropdown = true;

    /**
     * Модальное окно
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $modal = false;

    /**
     * Уникальный ключ пункта меню
     */
    #[ORM\OneToOne(targetEntity: MenuAdminSectionPathKey::class, mappedBy: 'path', cascade: ['all'])]
    private ?MenuAdminSectionPathKey $key = null;


    public function __construct(MenuAdminSection $section)
    {
        $this->id = new MenuAdminSectionPathUid();

        $this->section = $section;
        $this->role = new GroupRolePrefix('ROLE_ADMIN');
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

        if($dto instanceof MenuAdminSectionPathInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {
        if($dto instanceof MenuAdminSectionPathInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

}