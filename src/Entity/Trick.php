<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * This class represent tricks
 * @ORM\Entity(repositoryClass="App\Repository\TrickRepository")
 * @UniqueEntity(fields="name", message= "Ce nom de figure existe déja")
 * @ORM\Table(indexes={@ORM\Index(name="slug_idx",columns={"slug"}, options={"length": 255})})
 */
class Trick
{
    /**
     * @var int $id Identifier
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string $name The trick name
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min = 2, max = 255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var string $description Description of the trick
     * @ORM\Column(type="text")
     * @Assert\Length(min = 2, max = 255)
     * @Assert\NotBlank
     */
    private $description;

    /**
     * @var datetime $creationMoment Creation timestamp
     * @ORM\Column(type="datetime")
     */
    private $creationMoment;

    /**
     * @var datetime $revisionMoment Revision timestamp
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $revisionMoment;

    /**
     * @var User $user The trick creator
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="tricks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var Comment $comments The related comments
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="trick")
     * @ORM\OrderBy({"creationMoment" = "DESC"})
     */
    private $comments;

    /**
     * @var int $category The related category
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="tricks")
     */
    private $category;

    /**
     * @var Collection|Picture[] $pictures Related pictures
     * @ORM\OneToMany(targetEntity="App\Entity\Picture", mappedBy="trick", cascade={"persist"})
     */
    private $pictures;

    /**
     * @var Collection|Video[] $videos Related videos
     * @ORM\OneToMany(targetEntity="App\Entity\Video", mappedBy="trick", cascade={"persist"})
     */
    private $videos;

    /**
     * @var string $mainPictureFilename The filename of the main picture
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mainPictureFilename;

    /**
     * @var string $slug The slug converted string from trick's name
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->pictures = new ArrayCollection();
        $this->videos = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getRevisionMoment(): ?\DateTimeInterface
    {
        return $this->revisionMoment;
    }

    public function setRevisionMoment(?\DateTimeInterface $revisionMoment): self
    {
        $this->revisionMoment = $revisionMoment;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getCommentsSlice(int $offset, int $length): Collection
    {
        $commentsSlice = $this->comments->slice($offset, $length); 
        return new ArrayCollection($commentsSlice);
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setTrick($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getTrick() === $this) {
                $comment->setTrick(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|Picture[]
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): self
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures[] = $picture;
            $picture->setTrick($this);
        }

        return $this;
    }

    public function removePicture(Picture $picture): self
    {
        if ($this->pictures->contains($picture)) {
            $this->pictures->removeElement($picture);
            // set the owning side to null (unless already changed)
            if ($picture->getTrick() === $this) {
                $picture->setTrick(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Video[]
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
            $video->setTrick($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): self
    {
        if ($this->videos->contains($video)) {
            $this->videos->removeElement($video);
            // set the owning side to null (unless already changed)
            if ($video->getTrick() === $this) {
                $video->setTrick(null);
            }
        }

        return $this;
    }

    public function getMainPictureFilename(): ?string
    {
        return $this->mainPictureFilename;
    }

    public function setMainPictureFilename(?string $mainPictureFilename): self
    {
        $this->mainPictureFilename = $mainPictureFilename;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
