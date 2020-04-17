<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This class represent videos of tricks
 * @ORM\Entity(repositoryClass="App\Repository\VideoRepository")
 */
class Video
{
    /**
     * @var int $id Identifier
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string $embedLink The embed link of the video
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min = 2, max = 255)
     * @Assert\NotBlank
     */
    private $embedLink;

    /**
     * @var Trick $trick The related trick
     * @ORM\ManyToOne(targetEntity="App\Entity\Trick", inversedBy="videos")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\Url
     */
    private $trick;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmbedLink(): ?string
    {
        return $this->embedLink;
    }

    public function setEmbedLink(string $embedLink): self
    {
        $this->embedLink = $embedLink;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }
}
