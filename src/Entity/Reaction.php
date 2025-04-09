<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReactionRepository;

/**
 * Reaction
 *
 * @ORM\Table(name="reaction", indexes={@ORM\Index(name="fk_reaction_portfolio", columns={"id_portfolio"})})
 * @ORM\Entity(repositoryClass=ReactionRepository::class)
 */
class Reaction
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    private $type;

    /**
     * @var \Portfolio
     *
     * @ORM\ManyToOne(targetEntity="Portfolio")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_portfolio", referencedColumnName="id")
     * })
     */
    private $idPortfolio;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getIdPortfolio(): ?int
    {
        return $this->idPortfolio ? $this->idPortfolio->getId() : null;
    }

    public function setIdPortfolio(?Portfolio $idPortfolio): self
    {
        $this->idPortfolio = $idPortfolio;

        return $this;
    }
}
