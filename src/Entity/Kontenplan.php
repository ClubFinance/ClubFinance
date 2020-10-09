<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\KontenplanRepository")
 */
class Kontenplan
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $id1;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $id2;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $id3;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $id4;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getId1(): ?int
    {
        return $this->id1;
    }

    public function setId1(int $id1): self
    {
        $this->id1 = $id1;

        return $this;
    }

    public function getId2(): ?int
    {
        return $this->id2;
    }

    public function setId2(?int $id2): self
    {
        $this->id2 = $id2;

        return $this;
    }

    public function getId3(): ?int
    {
        return $this->id3;
    }

    public function setId3(?int $id3): self
    {
        $this->id3 = $id3;

        return $this;
    }

    public function getId4(): ?int
    {
        return $this->id4;
    }

    public function setId4(?int $id4): self
    {
        $this->id4 = $id4;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }
}
