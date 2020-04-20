<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * This class represent users
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="username", message= "Ce nom d'utilisateur est déja utilisé!")
 * @UniqueEntity(fields="email", message= "Cette email est déja utilisé!")
 */
class User implements UserInterface
{
    /**
     * @var int $id Identifier
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string $username The pseudonym of the user
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min = 2, max = 255)
     * @Assert\NotBlank
     */
    private $username;

    /**
     * @var string $email Email of the user
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min = 5, max = 255)
     * @Assert\Email()
     * @Assert\NotBlank
     */
    private $email;

    /**
     * @var string $password The password hash
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="8", minMessage="Votre mot de passe doit avoir au moins 8 caractères", max = 255)
     * @Assert\NotBlank
     */
    private $password;

    /**
     * @var string $confirmPassword The unpersisted equal password check when suscribe
     * @Assert\EqualTo(propertyPath="password", message="Vous n'avez pas tapé le même mot de passe")
     */
    private $confirmPassword;

    /**
     * @var Collection|Trick[] $tricks The related tricks
     * @ORM\OneToMany(targetEntity="App\Entity\Trick", mappedBy="user")
     */
    private $tricks;

    /**
     * @var Collection|Comment[] $comments The related comments
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="user")
     */
    private $comments;

    /**
     * @var boolean $confirmed The character 
     * @ORM\Column(type="boolean")
     */
    private $confirmed;

    /**
     * @var string $token Confirmation token
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @var datetime $creationMoment Creation timestamp
     * @ORM\Column(type="datetime")
     */
    private $creationMoment;

    /**
     * @var datetime $forgotPasswordMoment Forgot password timestamp
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $forgotPasswordMoment;

    /**
     * @var string $pictureFilename Filename of the picture
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pictureFilename;

    /**
     * @var array $roles The owned roles
     * @ORM\Column(type="json", nullable=true)
     */
    private $roles = [];

    public function __construct()
    {
        $this->tricks = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(string $confirmPassword): self
    {
        $this->confirmPassword = $confirmPassword;

        return $this;
    }

    /**
     * @return Collection|Trick[]
     */
    public function getTricks(): Collection
    {
        return $this->tricks;
    }

    public function addTrick(Trick $trick): self
    {
        if (!$this->tricks->contains($trick)) {
            $this->tricks[] = $trick;
            $trick->setUser($this);
        }

        return $this;
    }

    public function removeTrick(Trick $trick): self
    {
        if ($this->tricks->contains($trick)) {
            $this->tricks->removeElement($trick);
            // set the owning side to null (unless already changed)
            if ($trick->getUser() === $this) {
                $trick->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    public function eraseCredentials() {}

    public function getSalt() {}

    public function getRoles() {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        /*return ['ROLE_USER'];*/
        return array_unique($roles);
    }

    public function getConfirmed(): ?bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(bool $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getCreationMoment(): ?\DateTimeInterface
    {
        return $this->creationMoment;
    }

    public function setCreationMoment(\DateTimeInterface $creationMoment): self
    {
        $this->creationMoment = $creationMoment;

        return $this;
    }

    public function getForgotPasswordMoment(): ?\DateTimeInterface
    {
        return $this->forgotPasswordMoment;
    }

    public function setForgotPasswordMoment(?\DateTimeInterface $forgotPasswordMoment): self
    {
        $this->forgotPasswordMoment = $forgotPasswordMoment;

        return $this;
    }

    public function getPictureFilename(): ?string
    {
        return $this->pictureFilename;
    }

    public function setPictureFilename(?string $pictureFilename): self
    {
        $this->pictureFilename = $pictureFilename;

        return $this;
    }

    public function setRoles(?array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}
