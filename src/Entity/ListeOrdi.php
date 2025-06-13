<?php

namespace App\Entity;

use App\Repository\ListeOrdiRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ListeOrdiRepository::class)]
class ListeOrdi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $DateFirstDotation = null;

    #[ORM\Column(length: 255)]
    private ?string $DaOdoo = null;

    #[ORM\Column]
    private ?int $PrixUnitaire = null;

    #[ORM\Column]
    private ?int $CoutJournalierFixe = null;

    #[ORM\Column(nullable: true)]
    private ?int $NbJourFixe = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $DateFinAmort = null;

    #[ORM\Column(nullable: true)]
    private ?int $NbJoursRestants = null;

    #[ORM\Column(nullable: true)]
    private ?int $PrixAmort = null;

    #[ORM\Column(length: 255)]
    private ?string $IM = null;

    #[ORM\Column(length: 255)]
    private ?string $Detenteur = null;

    #[ORM\Column(length: 255)]
    private ?string $Fonction = null;

    #[ORM\Column(length: 255)]
    private ?string $Marque = null;

    #[ORM\Column(length: 255)]
    private ?string $NumSerie = null;

    public function getId(): ?int { return $this->id; }

    public function getDateFirstDotation(): ?\DateTime { return $this->DateFirstDotation; }
    public function setDateFirstDotation(\DateTime $DateFirstDotation): static {
        $this->DateFirstDotation = $DateFirstDotation;
        $this->recalculerChamps();
        return $this;
    }

    public function getDaOdoo(): ?string { return $this->DaOdoo; }
    public function setDaOdoo(string $DaOdoo): static { $this->DaOdoo = $DaOdoo; return $this; }

    public function getPrixUnitaire(): ?int { return $this->PrixUnitaire; }
    public function setPrixUnitaire(int $PrixUnitaire): static {
        $this->PrixUnitaire = $PrixUnitaire;
        $this->recalculerChamps();
        return $this;
    }

    public function getCoutJournalierFixe(): ?int { return $this->CoutJournalierFixe; }
    public function setCoutJournalierFixe(int $CoutJournalierFixe): static {
        $this->CoutJournalierFixe = $CoutJournalierFixe;
        $this->recalculerChamps();
        return $this;
    }

    public function getNbJourFixe(): ?int { return $this->NbJourFixe; }
    public function getDateFinAmort(): ?\DateTime { return $this->DateFinAmort; }
    public function getNbJoursRestants(): ?int { return $this->NbJoursRestants; }
    public function getPrixAmort(): ?int { return $this->PrixAmort; }

    public function getIM(): ?string { return $this->IM; }
    public function setIM(string $IM): static { $this->IM = $IM; return $this; }

    public function getDetenteur(): ?string { return $this->Detenteur; }
    public function setDetenteur(string $Detenteur): static { $this->Detenteur = $Detenteur; return $this; }

    public function getFonction(): ?string { return $this->Fonction; }
    public function setFonction(string $Fonction): static { $this->Fonction = $Fonction; return $this; }

    public function getMarque(): ?string { return $this->Marque; }
    public function setMarque(string $Marque): static { $this->Marque = $Marque; return $this; }

    public function getNumSerie(): ?string { return $this->NumSerie; }
    public function setNumSerie(string $NumSerie): static { $this->NumSerie = $NumSerie; return $this; }

    public function recalculerChamps(): void
    {
        if ($this->PrixUnitaire && $this->CoutJournalierFixe && $this->DateFirstDotation) {
            // Calcul du nombre de jours d'amortissement
            $this->NbJourFixe = intdiv($this->PrixUnitaire, $this->CoutJournalierFixe);

            // Date de fin d’amortissement
            $this->DateFinAmort = (clone $this->DateFirstDotation)->modify("+{$this->NbJourFixe} days");

            // Date actuelle (système)
            $today = (new \DateTimeImmutable())->setTime(0, 0);

            // Jours restants
            $this->NbJoursRestants = max(0, $today->diff($this->DateFinAmort)->days);

            // Prix restant à amortir
            $this->PrixAmort = $this->NbJoursRestants * $this->CoutJournalierFixe;
        }
    }

    #[ORM\PostLoad]
    public function updateChampsApresChargement(): void
    {
        $this->recalculerChamps();
    }
}
