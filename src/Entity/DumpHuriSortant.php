<?php

namespace App\Entity;

use App\Repository\DumpHuriSortantRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DumpHuriSortantRepository::class)
 */
class DumpHuriSortant
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\SequenceGenerator(sequenceName="id_seq", initialValue=1, allocationSize=100)
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $num_a;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $num_b;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $a_nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $a_adresse;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $a_piece;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $a_num_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $duration;

    /**
     * @ORM\Column(type="datetime")
     */
    private $day_time;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $data_type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumA(): ?string
    {
        return $this->num_a;
    }

    public function setNumA(string $num_a): self
    {
        $this->num_a = $num_a;

        return $this;
    }

    public function getNumB(): ?string
    {
        return $this->num_b;
    }

    public function setNumB(string $num_b): self
    {
        $this->num_b = $num_b;

        return $this;
    }

    public function getANom(): ?string
    {
        return $this->a_nom;
    }

    public function setANom(?string $a_nom): self
    {
        $this->a_nom = $a_nom;

        return $this;
    }

    public function getAAdresse(): ?string
    {
        return $this->a_adresse;
    }

    public function setAAdresse(?string $a_adresse): self
    {
        $this->a_adresse = $a_adresse;

        return $this;
    }

    public function getAPiece(): ?string
    {
        return $this->a_piece;
    }

    public function setAPiece(?string $a_piece): self
    {
        $this->a_piece = $a_piece;

        return $this;
    }

    public function getANumId(): ?string
    {
        return $this->a_num_id;
    }

    public function setANumId(?string $a_num_id): self
    {
        $this->a_num_id = $a_num_id;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDayTime(): ?\DateTimeInterface
    {
        return $this->day_time;
    }

    public function setDayTime(\DateTimeInterface $day_time): self
    {
        $this->day_time = $day_time;

        return $this;
    }

    public function getDataType(): ?string
    {
        return $this->data_type;
    }

    public function setDataType(string $data_type): self
    {
        $this->data_type = $data_type;

        return $this;
    }
}
