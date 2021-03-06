<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Entity;

use App\Attribute\Loggable;
use App\Enum\PreferredAddress;
use App\Repository\PersonRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Slug;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Serializable;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Ignore;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[Vich\Uploadable]
class Person implements UserInterface, PasswordAuthenticatedUserInterface, Serializable
// TODO Is it a bug that we have to implement PasswordAuthenticatedUserInterface even though this entity doesn't handle authentication?
{
    use TimestampableEntity;

    const USER_ROLES = [
        'CONNECT Admin' => 'ROLE_ADMIN',
        'CNRG' => 'ROLE_CNRG',
        'Op/Fac' => 'ROLE_OP_FAC',
        'Key Manager' => 'ROLE_KEY_MANAGER',
        'Director\'s Office' => 'ROLE_DIRECTORS_OFFICE',
        'Human Resources' => 'ROLE_HUMAN_RESOURCES',
        'Business Office' => 'ROLE_BUSINESS_OFFICE',
        'Communications' => 'ROLE_COMMUNICATIONS',
        'Outreach' => 'ROLE_OUTREACH',
        'Core Facilities' => 'ROLE_CORE_FACILITIES',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    private ?string $firstName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    private ?string $lastName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    private ?string $middleInitial;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable(displayName: 'netID')]
    private ?string $netid;

    #[ORM\Column(type: 'string', length: 180, unique: true, nullable: true)]
    #[Loggable]
    private ?string $username;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Loggable(displayName: 'UIN')]
    private ?int $uin;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    private ?string $email;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    private ?string $officeNumber;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    private ?string $officePhone;

    #[ORM\Column(type: 'boolean')]
    #[Loggable(displayName: 'DRS training')]
    private bool $isDrsTrainingComplete = false;

    #[ORM\Column(type: 'boolean')]
    #[Loggable(displayName: 'IGB training')]
    private bool $isIgbTrainingComplete = false;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Loggable]
    private ?DateTimeInterface $offerLetterDate;

    #[ORM\Column(type: 'boolean')]
    #[Loggable(displayName: 'key deposit')]
    private bool $hasGivenKeyDeposit = false;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: RoomAffiliation::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $roomAffiliations;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: KeyAffiliation::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['startedAt' => 'ASC'])]
    private Collection $keyAffiliations;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: ThemeAffiliation::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['startedAt' => 'DESC'])]
    private Collection $themeAffiliations;

    #[ORM\OneToMany(mappedBy: 'supervisee', targetEntity: SupervisorAffiliation::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $supervisorAffiliations;

    #[ORM\OneToMany(mappedBy: 'supervisor', targetEntity: SupervisorAffiliation::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $superviseeAffiliations;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: DepartmentAffiliation::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $departmentAffiliations;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Note::class, orphanRemoval: true)]
    private Collection $notes;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Log::class, orphanRemoval: true)]
    private Collection $ownedLogs;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Log::class)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $logs;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: WorkflowProgress::class, orphanRemoval: true)]
    private Collection $workflowProgress;

    #[ORM\ManyToOne(targetEntity: Building::class, inversedBy: 'people')]
    private ?Building $officeBuilding;

    #[ORM\Column(type: 'string', length: 255, enumType: PreferredAddress::class)]
    #[Loggable]
    private PreferredAddress $preferredAddress = PreferredAddress::IGB;

    #[ORM\Column(type: 'json')]
    #[Loggable(type: 'array')]
    private ?array $roles = [];

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable]
    private ?string $preferredFirstName = null;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Document::class, orphanRemoval: true)]
    private Collection $documents;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Loggable(displayName: 'portrait', details: false)]
    private ?string $imageName = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $mimeType;

    #[Vich\UploadableField(mapping: 'person_image', fileNameProperty: 'imageName', size: 'imageSize', mimeType: 'mimeType')]
    #[Ignore]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $imageSize;

    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: Note::class)]
    private Collection $createdNotes;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Slug(fields: ['firstName', 'lastName'], unique_base: 'id')]
    private ?string $slug;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $otherAddress;

    public function __construct()
    {
        $this->roomAffiliations = new ArrayCollection();
        $this->keyAffiliations = new ArrayCollection();
        $this->themeAffiliations = new ArrayCollection();
        $this->supervisorAffiliations = new ArrayCollection();
        $this->superviseeAffiliations = new ArrayCollection();
        $this->departmentAffiliations = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->ownedLogs = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->workflowProgress = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->createdNotes = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    /* Helper Functions */

    public function getName(): string
    {
        if ($this->getPreferredFirstName()) {
            return $this->getPreferredFirstName() . ' ' . $this->getLastName();
        } else {
            return $this->getFirstName() . ' ' . $this->getLastName(); // TODO this should be a little smarter
        }
    }

    public function getIsCurrent(): bool
    {
        return $this->getThemeAffiliations()->filter(function(ThemeAffiliation $themeAffiliation){
            return $themeAffiliation->isCurrent();
        })->count()>0;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getThemeAdminThemeAffiliations(): Collection
    {
        // todo unit test this
        return $this->getThemeAffiliations()->filter(function (ThemeAffiliation $themeAffiliation) {
            return $themeAffiliation->isCurrent() && $themeAffiliation->getIsThemeAdmin();
        });
    }

    public function getLabManagerThemeAffiliations(): Collection
    {
        // todo unit test this
        return $this->getThemeAffiliations()->filter(function (ThemeAffiliation $themeAffiliation) {
            return $themeAffiliation->isCurrent() && $themeAffiliation->getIsLabManager();
        });
    }

    /* Getters/Setters */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getMiddleInitial(): ?string
    {
        return $this->middleInitial;
    }

    public function setMiddleInitial(?string $middleInitial): self
    {
        $this->middleInitial = $middleInitial;

        return $this;
    }

    public function getNetid(): ?string
    {
        return $this->netid;
    }

    public function setNetid(?string $netid): self
    {
        $this->netid = $netid;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUin(): ?int
    {
        return $this->uin;
    }

    public function setUin(?int $uin): self
    {
        $this->uin = $uin;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getOfficeNumber(): ?string
    {
        return $this->officeNumber;
    }

    public function setOfficeNumber(?string $officeNumber): self
    {
        $this->officeNumber = $officeNumber;

        return $this;
    }

    public function getOfficePhone(): ?string
    {
        return $this->officePhone;
    }

    public function setOfficePhone(?string $officePhone): self
    {
        $this->officePhone = $officePhone;

        return $this;
    }

    public function getIsDrsTrainingComplete(): ?bool
    {
        return $this->isDrsTrainingComplete;
    }

    public function setIsDrsTrainingComplete(bool $isDrsTrainingComplete): self
    {
        $this->isDrsTrainingComplete = $isDrsTrainingComplete;

        return $this;
    }

    public function getIsIgbTrainingComplete(): ?bool
    {
        return $this->isIgbTrainingComplete;
    }

    public function setIsIgbTrainingComplete(bool $isIgbTrainingComplete): self
    {
        $this->isIgbTrainingComplete = $isIgbTrainingComplete;

        return $this;
    }

    public function getOfferLetterDate(): ?DateTimeInterface
    {
        return $this->offerLetterDate;
    }

    public function setOfferLetterDate(?DateTimeInterface $offerLetterDate): self
    {
        $this->offerLetterDate = $offerLetterDate;

        return $this;
    }

    public function getHasGivenKeyDeposit(): ?bool
    {
        return $this->hasGivenKeyDeposit;
    }

    public function setHasGivenKeyDeposit(bool $hasGivenKeyDeposit): self
    {
        $this->hasGivenKeyDeposit = $hasGivenKeyDeposit;

        return $this;
    }

    /**
     * @return Collection<int, RoomAffiliation>|RoomAffiliation[]
     */
    public function getRoomAffiliations(): Collection
    {
        return $this->roomAffiliations;
    }

    public function addRoomAffiliation(RoomAffiliation $roomAffiliation): self
    {
        if (!$this->roomAffiliations->contains($roomAffiliation)) {
            $this->roomAffiliations[] = $roomAffiliation;
            $roomAffiliation->setPerson($this);
        }

        return $this;
    }

    public function removeRoomAffiliation(RoomAffiliation $roomAffiliation): self
    {
        if ($this->roomAffiliations->removeElement($roomAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($roomAffiliation->getPerson() === $this) {
                $roomAffiliation->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, KeyAffiliation>|KeyAffiliation[]
     */
    public function getKeyAffiliations(): Collection
    {
        return $this->keyAffiliations;
    }

    public function addKeyAffiliation(KeyAffiliation $keyAffiliation): self
    {
        if (!$this->keyAffiliations->contains($keyAffiliation)) {
            $this->keyAffiliations[] = $keyAffiliation;
            $keyAffiliation->setPerson($this);
        }

        return $this;
    }

    public function removeKeyAffiliation(KeyAffiliation $keyAffiliation): self
    {
        if ($this->keyAffiliations->removeElement($keyAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($keyAffiliation->getPerson() === $this) {
                $keyAffiliation->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ThemeAffiliation>|ThemeAffiliation[]
     */
    public function getThemeAffiliations(): Collection
    {
        return $this->themeAffiliations;
    }

    public function addThemeAffiliation(ThemeAffiliation $themeAffiliation): self
    {
        if (!$this->themeAffiliations->contains($themeAffiliation)) {
            $this->themeAffiliations[] = $themeAffiliation;
            $themeAffiliation->setPerson($this);
        }

        return $this;
    }

    public function removeThemeAffiliation(ThemeAffiliation $themeAffiliation): self
    {
        if ($this->themeAffiliations->removeElement($themeAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($themeAffiliation->getPerson() === $this) {
                $themeAffiliation->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SupervisorAffiliation>|SupervisorAffiliation[]
     */
    public function getSupervisorAffiliations(): Collection
    {
        return $this->supervisorAffiliations;
    }

    public function addSupervisorAffiliation(SupervisorAffiliation $supervisorAffiliation): self
    {
        if (!$this->supervisorAffiliations->contains($supervisorAffiliation)) {
            $this->supervisorAffiliations[] = $supervisorAffiliation;
            $supervisorAffiliation->setSupervisee($this);
        }

        return $this;
    }

    public function removeSupervisorAffiliation(SupervisorAffiliation $supervisorAffiliation): self
    {
        if ($this->supervisorAffiliations->removeElement($supervisorAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($supervisorAffiliation->getSupervisee() === $this) {
                $supervisorAffiliation->setSupervisee(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SupervisorAffiliation>|SupervisorAffiliation[]
     */
    public function getSuperviseeAffiliations(): Collection
    {
        return $this->superviseeAffiliations;
    }

    public function addSuperviseeAffiliation(SupervisorAffiliation $superviseeAffiliation): self
    {
        if (!$this->superviseeAffiliations->contains($superviseeAffiliation)) {
            $this->superviseeAffiliations[] = $superviseeAffiliation;
            $superviseeAffiliation->setSupervisor($this);
        }

        return $this;
    }

    public function removeSuperviseeAffiliation(SupervisorAffiliation $superviseeAffiliation): self
    {
        if ($this->superviseeAffiliations->removeElement($superviseeAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($superviseeAffiliation->getSupervisor() === $this) {
                $superviseeAffiliation->setSupervisor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DepartmentAffiliation>|DepartmentAffiliation[]
     */
    public function getDepartmentAffiliations(): Collection
    {
        return $this->departmentAffiliations;
    }

    public function addDepartmentAffiliation(DepartmentAffiliation $departmentAffiliation): self
    {
        if (!$this->departmentAffiliations->contains($departmentAffiliation)) {
            $this->departmentAffiliations[] = $departmentAffiliation;
            $departmentAffiliation->setPerson($this);
        }

        return $this;
    }

    public function removeDepartmentAffiliation(DepartmentAffiliation $departmentAffiliation): self
    {
        if ($this->departmentAffiliations->removeElement($departmentAffiliation)) {
            // set the owning side to null (unless already changed)
            if ($departmentAffiliation->getPerson() === $this) {
                $departmentAffiliation->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Note>|Note[]
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setPerson($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getPerson() === $this) {
                $note->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Log>|Log[]
     */
    public function getOwnedLogs(): Collection
    {
        return $this->ownedLogs;
    }

    public function addOwnedLog(Log $ownedLog): self
    {
        if (!$this->ownedLogs->contains($ownedLog)) {
            $this->ownedLogs[] = $ownedLog;
            $ownedLog->setUser($this);
        }

        return $this;
    }

    public function removeOwnedLog(Log $ownedLog): self
    {
        if ($this->ownedLogs->removeElement($ownedLog)) {
            // set the owning side to null (unless already changed)
            if ($ownedLog->getUser() === $this) {
                $ownedLog->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Log>|Log[]
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setPerson($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getPerson() === $this) {
                $log->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WorkflowProgress>
     */
    public function getWorkflowProgress(): Collection
    {
        return $this->workflowProgress;
    }

    public function addWorkflowProgress(WorkflowProgress $workflowProgress): self
    {
        if (!$this->workflowProgress->contains($workflowProgress)) {
            $this->workflowProgress[] = $workflowProgress;
            $workflowProgress->setPerson($this);
        }

        return $this;
    }

    public function removeWorkflowProgress(WorkflowProgress $workflowProgress): self
    {
        if ($this->workflowProgress->removeElement($workflowProgress)) {
            // set the owning side to null (unless already changed)
            if ($workflowProgress->getPerson() === $this) {
                $workflowProgress->setPerson(null);
            }
        }

        return $this;
    }

    public function getOfficeBuilding(): ?Building
    {
        return $this->officeBuilding;
    }

    public function setOfficeBuilding(?Building $officeBuilding): self
    {
        $this->officeBuilding = $officeBuilding;

        return $this;
    }

    public function getPreferredAddress(): ?PreferredAddress
    {
        return $this->preferredAddress;
    }

    public function setPreferredAddress(PreferredAddress $preferredAddress): self
    {
        $this->preferredAddress = $preferredAddress;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getDisplayRoles(): array
    {
        return $this->roles;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPassword(): ?string
    {
        return ''; // TODO I have no idea why it's necessary to implement this
    }

    public function getPreferredFirstName(): ?string
    {
        return $this->preferredFirstName;
    }

    public function setPreferredFirstName(?string $preferredFirstName): self
    {
        $this->preferredFirstName = $preferredFirstName;

        return $this;
    }

    /**
     * @return Collection<int, Document>|Document[]
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setPerson($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getPerson() === $this) {
                $document->setPerson(null);
            }
        }

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function setImageSize(?int $imageSize): self
    {
        $this->imageSize = $imageSize;

        return $this;
    }

    public function serialize()
    {
        return serialize([
            'id' => $this->id,
            'username' => $this->username,
        ]);
    }

    public function unserialize(string $serialized)
    {
        $data = unserialize($serialized);
        $this->id = $data['id'];
        $this->username = $data['username'];
    }

    /**
     * @return Collection<int, Note>
     */
    public function getCreatedNotes(): Collection
    {
        return $this->createdNotes;
    }

    public function addCreatedNote(Note $createdNote): self
    {
        if (!$this->createdNotes->contains($createdNote)) {
            $this->createdNotes[] = $createdNote;
            $createdNote->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCreatedNote(Note $createdNote): self
    {
        if ($this->createdNotes->removeElement($createdNote)) {
            // set the owning side to null (unless already changed)
            if ($createdNote->getCreatedBy() === $this) {
                $createdNote->setCreatedBy(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->username = $data['username'];
    }

    public function getOtherAddress(): ?string
    {
        return $this->otherAddress;
    }

    public function setOtherAddress(?string $otherAddress): self
    {
        $this->otherAddress = $otherAddress;

        return $this;
    }

    public function getStartedAt(): ?DateTimeInterface
    {
        if ($this->getThemeAffiliations()->count() > 0) {
            return array_reduce(
                $this->getThemeAffiliations()->toArray(),
                function (?DateTimeInterface $carry, ThemeAffiliation $themeAffiliation) {
                    if ($carry === null || $themeAffiliation->getStartedAt() > $carry) {
                        return $carry;
                    }
                    return $themeAffiliation->getStartedAt();
                },
                $this->getThemeAffiliations()->toArray()[0]->getStartedAt()
            );
        }
        return null;
    }

    public function getEndedAt(): ?DateTimeInterface
    {
        if ($this->getThemeAffiliations()->count() > 0) {
            return array_reduce(
                $this->getThemeAffiliations()->toArray(),
                function (?DateTimeInterface $carry, ThemeAffiliation $themeAffiliation) {
                    if ($carry === null || $themeAffiliation->getEndedAt() < $carry) {
                        return $carry;
                    }
                    return $themeAffiliation->getEndedAt();
                },
                $this->getThemeAffiliations()->toArray()[0]->getEndedAt()
            );
        }
        return null;
    }
}
