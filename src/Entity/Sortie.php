<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateHeureDebut = null;

    #[ORM\Column]
    private ?int $duree = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateLimiteInscription = null;

    #[ORM\Column]
    private ?int $nbInscriptionsMax = null;

    #[ORM\Column(length: 100)]
    private ?string $infosSortie = null;


    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lieu $lieu = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Site $sites = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $ParticipantOrganise = null;

    #[ORM\ManyToMany(targetEntity: Participant::class, inversedBy: 'sortiesRelations')]
    private Collection $ParticipantInscrit;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etat $etat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sortieImageUpload = null;

    #[ORM\OneToMany(mappedBy: 'sortie', targetEntity: InscriptionEvenements::class, orphanRemoval: true)]
    private Collection $inscriptions;


    public function __construct()
    {
        $this->ParticipantInscrit = new ArrayCollection();
        $this->inscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeInterface $dateHeureDebut): self
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeInterface
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeInterface $dateLimiteInscription): self
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionsMax(): ?int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(int $nbInscriptionsMax): self
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(string $infosSortie): self
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getSites(): ?site
    {
        return $this->sites;
    }

    public function setSites(?site $sites): self
    {
        $this->sites = $sites;

        return $this;
    }


    public function getParticipantOrganise(): ?Participant
    {
        return $this->ParticipantOrganise;
    }

    public function setParticipantOrganise(?Participant $ParticipantOrganise): self
    {
        $this->ParticipantOrganise = $ParticipantOrganise;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipantInscrit(): Collection
    {
        return $this->ParticipantInscrit;
    }

    public function addParticipantInscrit(Participant $participantInscrit): self
    {
        if (!$this->ParticipantInscrit->contains($participantInscrit)) {
            $this->ParticipantInscrit->add($participantInscrit);
        }

        return $this;
    }

    public function removeParticipantInscrit(Participant $participantInscrit): self
    {
        $this->ParticipantInscrit->removeElement($participantInscrit);

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getSortieImageUpload(): ?string
    {
        return $this->sortieImageUpload;
    }

    public function setSortieImageUpload(?string $sortieImageUpload): self
    {
        $this->sortieImageUpload = $sortieImageUpload;

        return $this;
    }

    /**
     * @return Collection<int, InscriptionEvenements>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(InscriptionEvenements $inscription): self
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
            $inscription->setSortie($this);
        }

        return $this;
    }

    public function removeInscription(InscriptionEvenements $inscription): self
    {
        if ($this->inscriptions->removeElement($inscription)) {
            // set the owning side to null (unless already changed)
            if ($inscription->getSortie() === $this) {
                $inscription->setSortie(null);
            }
        }

        return $this;
    }


}
