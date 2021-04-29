<?php

namespace App\Entity;

use App\Repository\DumpHuriEntrantRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DumpHuriEntrantRepository::class)
 */
class DumpHuriEntrant
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
     * @ORM\Column(type="string", length=255)
     */
    private $b_nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $b_adresse;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $b_num_id;

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

    public function getBNom(): ?string
    {
        return $this->b_nom;
    }

    public function setBNom(string $b_nom): self
    {
        $this->b_nom = $b_nom;

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

    public function getBNumId(): ?string
    {
        return $this->b_num_id;
    }

    public function setBNumId(?string $b_num_id): self
    {
        $this->b_num_id = $b_num_id;

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
