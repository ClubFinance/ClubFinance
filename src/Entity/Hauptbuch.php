<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HauptbuchRepository")
 */
class Hauptbuch
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
    private $soll;

    /**
     * @ORM\Column(type="integer")
     */
    private $haben;

    /**
     * @ORM\Column(type="date")
     */
    private $datum;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $beschreibung;

    /**
     * @ORM\Column(type="float")
     */
    private $betrag;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $beleg;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDatum(): ?\DateTimeInterface
    {
        return $this->datum;
    }

    public function setDatum(\DateTimeInterface $datum): self
    {
        $this->datum = $datum;

        return $this;
    }

    public function getBeschreibung(): ?string
    {
        return $this->beschreibung;
    }

    public function setBeschreibung(?string $beschreibung): self
    {
        $this->beschreibung = $beschreibung;

        return $this;
    }

    public function getBetrag(): ?float
    {
        return $this->betrag;
    }

    public function setBetrag(float $betrag): self
    {
        $this->betrag = $betrag;

        return $this;
    }

    public function getBeleg(): ?string
    {
        return $this->beleg;
    }

    public function setBeleg(?string $beleg): self
    {
        $this->beleg = $beleg;

        return $this;
    }

    // Nicht in DB
    public function getSollT(): ?string
    {
        return $this->soll;
    }

    public function setSollT(string $soll): self
    {
        $this->soll = $soll;

        return $this;
    }

    public function getHabenT(): ?string
    {
        return $this->haben;
    }

    public function setHabenT(string $haben): self
    {
        $this->haben = $haben;

        return $this;
    }
}
