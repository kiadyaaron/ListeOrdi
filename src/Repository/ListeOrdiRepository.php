<?php

namespace App\Repository;

use App\Entity\ListeOrdi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ListeOrdiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListeOrdi::class);
    }

    public function search(string $term): array
    {
        $qb = $this->createQueryBuilder('l');

        if ($term) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(l.DaOdoo)', ':term'),
                    $qb->expr()->like('LOWER(l.IM)', ':term'),
                    $qb->expr()->like('LOWER(l.Detenteur)', ':term'),
                    $qb->expr()->like('LOWER(l.Fonction)', ':term'),
                    $qb->expr()->like('LOWER(l.Marque)', ':term'),
                    $qb->expr()->like('LOWER(l.NumSerie)', ':term'),
                    $qb->expr()->like("CONCAT(l.PrixUnitaire, '')", ':term'),
                    $qb->expr()->like("CONCAT(l.CoutJournalierFixe, '')", ':term'),
                    $qb->expr()->like("CONCAT(l.NbJourFixe, '')", ':term'),
                    $qb->expr()->like("CONCAT(l.NbJoursRestants, '')", ':term'),
                    $qb->expr()->like("CONCAT(l.PrixAmort, '')", ':term'),
                    $qb->expr()->like("DATE_FORMAT(l.DateFirstDotation, '%d-%m-%Y')", ':term'),
                    $qb->expr()->like("DATE_FORMAT(l.DateFinAmort, '%d-%m-%Y')", ':term')
                )
            )
            ->setParameter('term', '%' . strtolower($term) . '%');
        }

        return $qb->getQuery()->getResult();
    }
}

