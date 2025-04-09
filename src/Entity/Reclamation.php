<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReclamationRepository;


/**
 * Reclamation
 *
 * @ORM\Table(name="reclamation")
 * @ORM\Entity(repositoryClass=ReclamationRepository::class)
 */
class Reclamation
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_reclamation", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idReclamation;

    /**
     * @var string
     *
     * @ORM\Column(name="reclamation", type="string", length=255, nullable=false)
     */
    private $reclamation;

    /**
     * @var string
     *
     * @ORM\Column(name="resolution", type="string", length=255, nullable=false)
     */
    private $resolution;

    /**
     * @var string
     *
     * @ORM\Column(name="type_reclamation", type="string", length=255, nullable=false)
     */
    private $typeReclamation;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="temps", type="datetime", nullable=true)
     */
    private $temps;



    public function getIdReclamation(): ?int
    {
        return $this->idReclamation;
    }

    public function getReclamation(): ?string
    {
        return $this->reclamation;
    }

    public function setReclamation(string $reclamation): self
    {
        $this->reclamation = $reclamation;

        return $this;
    }

    public function getResolution(): ?string
    {
        return $this->resolution;
    }

    public function setResolution(string $resolution): self
    {
        $this->resolution = $resolution;

        return $this;
    }

    public function getTypeReclamation(): ?string
    {
        return $this->typeReclamation;
    }

    public function setTypeReclamation(string $typeReclamation): self
    {
        $this->typeReclamation = $typeReclamation;

        return $this;
    }

    public function getTemps(): ?\DateTimeInterface
    {
        return $this->temps;
    }

    public function setTemps(?\DateTimeInterface $temps): self
    {
        $this->temps = $temps;

        return $this;
    }

    public function __toString(): string
    {
        return $this->idReclamation; // replace with the property you want to use as a string representation
    }
  
    
}
