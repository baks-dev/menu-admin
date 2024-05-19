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

namespace BaksDev\Menu\Admin\UseCase\Command\Menu\MenuAdminPath\Section\Path;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Menu\Admin\Entity\Section\Path\MenuAdminSectionPathInterface;
use BaksDev\Users\Profile\Group\Type\Prefix\Role\GroupRolePrefix;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see MenuAdminSectionPath */
final class MenuAdminPathSectionPathDTO implements MenuAdminSectionPathInterface
{
    /**
     * Перевод раздела
     */
    #[Assert\Valid]
    private ArrayCollection $translate;

    /**
     * Роль доступа
     */
    #[Assert\NotBlank]
    private GroupRolePrefix $role;

    /**
     * Path вида User:admin.index
     */
    #[Assert\Length(max: 255)]
    private ?string $path = null;

    /**
     * Показать в выпадающем меню
     */
    private bool $dropdown = true;

    /**
     * Показать в выпадающем меню
     */
    private bool $modal = false;

    /** Сортировка */
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 999)]
    private int $sort = 500;

    public function __construct()
    {
        $this->translate = new ArrayCollection();
    }

    public function getTranslate(): ArrayCollection
    {
        // Вычисляем расхождение и добавляем неопределенные локали
        foreach (Locale::diffLocale($this->translate) as $locale)
        {
            $TransFormDTO = new Trans\MenuAdminPathSectionPathTransDTO();
            $TransFormDTO->setLocal($locale);
            $this->addTranslate($TransFormDTO);
        }

        return $this->translate;
    }

    public function addTranslate(Trans\MenuAdminPathSectionPathTransDTO $trans): void
    {
        if(empty($trans->getLocal()->getLocalValue()))
        {
            return;
        }

        if (!$this->translate->contains($trans))
        {
            $this->translate->add($trans);
        }
    }

    public function removeTranslate(Trans\MenuAdminPathSectionPathTransDTO $trans): void
    {
        $this->translate->removeElement($trans);
    }

    /** Роль доступа */
    public function getRole(): GroupRolePrefix
    {
        return $this->role;
    }

    public function setRole(GroupRolePrefix $role): void
    {
        $this->role = $role;
    }

    /** Path вида User:admin.index  */
    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    /** Сортировка */
    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }

    /** Показать в выпадающем меню */
    public function getDropdown(): bool
    {
        return $this->dropdown;
    }

    public function setDropdown(bool $dropdown): void
    {
        $this->dropdown = $dropdown;
    }

    /**
     * Модальное окно.
     */

    public function getModal(): bool
    {
        return $this->modal;
    }

    public function setModal(bool $modal): void
    {
        $this->modal = $modal;
    }
}
