<?php

namespace App\Entity;

use App\Repository\DumpTRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DumpTRepository::class)
 */
class DumpT
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
    private $flux_appel;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $data_type;

    /**
     * @ORM\Column(type="datetime")
     */
    private $day_time;

    /**
     * @ORM\Column(type="integer")
     */
    private $duration;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imei;

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
    private $cell_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $location_num_a;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $brand;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $os;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $model;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $a_nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $a_piece;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $a_adresse;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $b_nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $b_piece;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $b_adresse;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFluxAppel(): ?string
    {
        return $this->flux_appel;
    }

    public function setFluxAppel(string $flux_appel): self
    {
        $this->flux_appel = $flux_appel;

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

    public function getDayTime(): ?\DateTimeInterface
    {
        return $this->day_time;
    }

    public function setDayTime(\DateTimeInterface $day_time): self
    {
        $this->day_time = $day_time;

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

    public function getImei(): ?string
    {
        return $this->imei;
    }

    public function setImei(string $imei): self
    {
        $this->imei = $imei;

        return $this;
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

    public function getCellId(): ?string
    {
        return $this->cell_id;
    }

    public function setCellId(?string $cell_id): self
    {
        $this->cell_id = $cell_id;

        return $this;
    }

    public function getLocationNumA(): ?string
    {
        return $this->location_num_a;
    }

    public function setLocationNumA(?string $location_num_a): self
    {
        $this->location_num_a = $location_num_a;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getOs(): ?string
    {
        return $this->os;
    }

    public function setOs(?string $os): self
    {
        $this->os = $os;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): self
    {
        $this->model = $model;

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

    public function getAPiece(): ?string
    {
        return $this->a_piece;
    }

    public function setAPiece(?string $a_piece): self
    {
        $this->a_piece = $a_piece;

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

    public function getBNom(): ?string
    {
        return $this->b_nom;
    }

    public function setBNom(?string $b_nom): self
    {
        $this->b_nom = $b_nom;

        return $this;
    }

    public function getBPiece(): ?string
    {
        return $this->b_piece;
    }

    public function setBPiece(?string $b_piece): self
    {
        $this->b_piece = $b_piece;

        return $this;
    }

    public function getBAdresse(): ?string
    {
        return $this->b_adresse;
    }

    public function setBAdresse(?string $b_adresse): self
    {
        $this->b_adresse = $b_adresse;

        return $this;
    }
}
