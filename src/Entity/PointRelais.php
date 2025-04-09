<?php

namespace App\Entity;

use App\Repository\PointRelaisRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=PointRelaisRepository::class)
 * @ORM\Table(name="point_relais")
 */
class PointRelais
{
     /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id_pt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $longitude;
     /**
     * @ORM\OneToMany(targetEntity="App\Entity\Livraison", mappedBy="pointRelais")
     */
    private $livraisons;
   

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le nom est obligatoire.")
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z]+$/",
     *     message="Le nom ne doit contenir que des lettres de l'alphabet."
     * )
     */
    #[ORM\Column(length: 255)]
    private ?string $nom_pt = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="La ville est obligatoire.")
     * @Assert\Choice(
     * choices={"bizerte", "ariana", "tunis", "sfax"},
     * message="La ville doit être l'une des valeurs suivantes : bizerte, ariana, tunis, sfax."
     * )
     */
    #[ORM\Column(length: 255)]
    private ?string $ville = null;

    public function getId(): ?int
    {
        return $this->id_pt;
    }

    public function getNomPt(): ?string
    {
        return $this->nom_pt;
    }

    public function setNomPt(string $nom_pt): self
    {
        $this->nom_pt = $nom_pt;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function __construct()
    {
        $this->livraisons = new ArrayCollection();
    }

    public function addLivraison(Livraison $livraison): self
    {
        if (!$this->livraisons->contains($livraison)) {
            $this->livraisons[] = $livraison;
            $livraison->setPointRelais($this);
        }

        return $this;
    }
    public function removeLivraison(Livraison $livraison): self
    {
        if ($this->livraisons->contains($livraison)) {
            $this->livraisons->removeElement($livraison);
            // set the owning side to null (unless already changed)
            if ($livraison->getPointRelais() === $this) {
                $livraison->setPointRelais(null);
            }
        }

        return $this;
    }

    public function getIdPt(): ?int
    {
        return $this->id_pt;
    }

    /**
     * @return Collection<int, Livraison>
     */
    public function getLivraisons(): Collection
    {
        return $this->livraisons;
    }

    public function __toString(): string
    {
        return $this->id_pt; // replace with the property you want to use as a string representation
    }
    




}
