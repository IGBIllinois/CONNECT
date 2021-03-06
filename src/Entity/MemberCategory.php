<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Repository\MemberCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: MemberCategoryRepository::class)]
class MemberCategory
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name;

    #[ORM\OneToMany(mappedBy: 'memberCategory', targetEntity: ThemeAffiliation::class, orphanRemoval: true)]
    private Collection $themeAffiliations;

    #[ORM\OneToMany(mappedBy: 'memberCategory', targetEntity: WorkflowStepCategory::class, orphanRemoval: true)]
    private Collection $workflowStepCategories;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $shortName;

    public function __construct()
    {
        $this->themeAffiliations = new ArrayCollection();
        $this->workflowStepCategories = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, ThemeAffiliation>
     */
    public function getThemeAffiliations(): Collection
    {
        return $this->themeAffiliations;
    }

    public function addThemeAffiliation(ThemeAffiliation $themeAffiliation): self
    {
        if (!$this->themeAffiliations->contains($themeAffiliation)) {
            $this->themeAffiliations[] = $themeAffiliation;
            $themeAffiliation->setMemberCategory($this);
        }

        return $this;
    }

    public function removeThemeAffiliation(ThemeAffiliation $themeAffiliation): self
    {
        if ($this->themeAffiliations->removeElement($themeAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($themeAffiliation->getMemberCategory() === $this) {
                $themeAffiliation->setMemberCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WorkflowStepCategory>
     */
    public function getWorkflowStepCategories(): Collection
    {
        return $this->workflowStepCategories;
    }

    public function addWorkflowStepCategory(WorkflowStepCategory $workflowStepCategory): self
    {
        if (!$this->workflowStepCategories->contains($workflowStepCategory)) {
            $this->workflowStepCategories[] = $workflowStepCategory;
            $workflowStepCategory->setMemberCategory($this);
        }

        return $this;
    }

    public function removeWorkflowStepCategory(WorkflowStepCategory $workflowStepCategory): self
    {
        if ($this->workflowStepCategories->removeElement($workflowStepCategory)) {
            // set the owning side to null (unless already changed)
            if ($workflowStepCategory->getMemberCategory() === $this) {
                $workflowStepCategory->setMemberCategory(null);
            }
        }

        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }
}
