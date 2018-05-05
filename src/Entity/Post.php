<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 */
class Post
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $Title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $Body;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank()
     * @Assert\Type("\DateTime")
     */
    private $date_added;
    
    /**
    * @ORM\Column(type="string", length=255)
    */
    private $slug;
    
    /**
    * @ORM\Column(type="text")
    */
    private $introduction;

    public function getId()
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(string $Title): self
    {
        $this->Title = $Title;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->Body;
    }

    public function setBody(string $Body): self
    {
        $this->Body = $Body;

        return $this;
    }

    public function getDateAdded(): ?\DateTimeInterface
    {
        return $this->date_added;
    }

    public function setDateAdded(\DateTimeInterface $date_added): self
    {
        $this->date_added = $date_added;

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
    
    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }
    
    public function setIntroduction(string $introduction): self
    {
        $this->introduction = $introduction;
        
        return $this;
    }
}
