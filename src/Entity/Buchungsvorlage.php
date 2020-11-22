<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BuchungsvorlageRepository")
 */
class Buchungsvorlage
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $beschreibung;

    /**
     * @ORM\Column(type="integer")
     */
    private $soll;

    /**
     * @ORM\Column(type="integer")
     */
    private $haben;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBeschreibung(): ?string
    {
        return $this->beschreibung;
    }

    public function setBeschreibung(string $beschreibung): self
    {
        $this->beschreibung = $beschreibung;

        return $this;
    }

    public function getSoll(): ?int
    {
        return $this->soll;
    }

    public function setSoll(int $soll): self
    {
        $this->soll = $soll;

        return $this;
    }

    public function getHaben(): ?int
    {
        return $this->haben;
    }

    public function setHaben(int $haben): self
    {
        $this->haben = $haben;

        return $this;
    }
}
