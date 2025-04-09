<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\LivraisonRepository;

/**
 * Livraison
 *
 * @ORM\Table(name="livraison")
 * @ORM\Entity(repositoryClass=LivraisonRepository::class)
 */
class Livraison
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_liv", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idLiv;

        /**
    * @ORM\ManyToOne(targetEntity="App\Entity\PointRelais")
    * @ORM\JoinColumn(name="pt_rl", referencedColumnName="id_pt")
    */
    public $pointRelais;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse", type="string", length=255, nullable=false)
     */
    private $adresse;

    /**
     * @var int
     *
     * @ORM\Column(name="tel", type="integer", nullable=false)
     */
    private $tel;

    /**
     * @var string|null
     *
     * @ORM\Column(name="pt_rl", type="string", length=255, nullable=true)
     */
    private $ptRl;

    public function getIdLiv(): ?int
    {
        return $this->idLiv;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTel(): ?int
    {
        return $this->tel;
    }

    public function setTel(int $tel): self
    {
        $this->tel = $tel;

        return $this;
    }

    public function getPtRl(): ?string
    {
        return $this->ptRl;
    }

    public function setPtRl(?string $ptRl): self
    {
        $this->ptRl = $ptRl;

        return $this;
    }




public function getPointRelais(): ?PointRelais
{
    return $this->pointRelais;
}

public function setPointRelais(?PointRelais $pointRelais): self
{
    $this->pointRelais = $pointRelais;

    return $this;
}

}
