<?php

namespace App\Entity;

use App\Repository\CPersonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=CPersonRepository::class)
 * @UniqueEntity(fields={"c_number"}, message="Un sujet avec ce numéro a déjé été ajouté!")
 */
class CPerson
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     */
    private $c_number;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $a_nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $c_adress;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $c_operator;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $c_file_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, options={"default": "icon-default.png"})
     */
    private $c_pic_name;




    public function __construct()
    {
        $this->c_pic_name= "icon-default.png";
        $this->a_nom = "0";
    }




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCNumber(): ?string
    {
        return $this->c_number;
    }

    public function setCNumber(string $c_number): self
    {
        $this->c_number = $c_number;

        return $this;
    }

    public function getCAdress(): ?string
    {
        return $this->c_adress;
    }

    public function setCAdress(?string $c_adress): self
    {
        $this->c_adress = $c_adress;

        return $this;
    }

    public function getCOperator(): ?string
    {
        return $this->c_operator;
    }

    public function setCOperator(string $c_operator): self
    {
        $this->c_operator = $c_operator;

        return $this;
    }

    public function getCFileName(): ?string
    {
        return $this->c_file_name;
    }

    public function setCFileName(string $c_file_name): self
    {
        $this->c_file_name = $c_file_name;

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

    public function getCPicName(): ?string
    {
        return $this->c_pic_name;
    }

    public function setCPicName(string $c_pic_name): self
    {
        $this->c_pic_name = $c_pic_name;

        return $this;
    }

}
