<?php

namespace App\Repository;

use App\Entity\DemandeDons;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<DemandeDons>
 */
class DemandeDonsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeDons::class);
    }

    public function findByDonneur(User $donneur)
{
    return $this->createQueryBuilder('d')
        ->join('d.dons', 'don')
        ->where('don.donneur = :donneur')
        ->setParameter('donneur', $donneur)
        ->getQuery()
        ->getResult();
}


//    /**
//     * @return DemandeDons[] Returns an array of DemandeDons objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DemandeDons
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
