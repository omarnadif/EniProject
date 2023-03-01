<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
#[ORM\Table(name: 'Participants')]
#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Ce mail est déjà utilisé!')]
#[UniqueEntity(fields: ['pseudo'], message: 'Ce pseudo est déjà utilisé!')]
class Participant implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'json', length: '1000')]
    private array $roles = [];

    #[ORM\Column(name: 'pseudo', type: 'string', length: 50, unique: true)]
    private ?string $pseudo = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string', length: '200')]
    private ?string $password = null;

    #[ORM\Column(type: 'string', length: '200',nullable: 'false')]
    private ?string $resetToken ;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(type: 'integer', length: '10')]
    private ?int $telephone = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $actif = null;

    #[ORM\ManyToOne(targetEntity: Site::class, inversedBy: "participants")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Site $site;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageParticipant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    /**
     * @param string|null $pseudo
     */
    public function setPseudo(?string $pseudo): void
    {
        $this->pseudo = $pseudo;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
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

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    /**
     * @param string|null $resetToken
     */
    public function setResetToken(?string $resetToken): void
    {
        $this->resetToken = $resetToken;
    }



    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?int
    {
        return $this->telephone;
    }

    public function setTelephone(int $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }
    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * @return Site|null
     */
    public function getSite(): ?Site
    {
        return $this->site;
    }

    /**
     * @param Site|null $site
     */
    public function setSite(?Site $site): void
    {
        $this->site = $site;
    }

    public function getImageParticipant(): ?string
    {
        return $this->imageParticipant;
    }

    public function setImageParticipant(?string $imageParticipant): self
    {
        $this->imageParticipant = $imageParticipant;

        return $this;
    }
}
