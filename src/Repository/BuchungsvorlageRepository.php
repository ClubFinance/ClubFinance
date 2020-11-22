<?php

namespace App\Repository;

use App\Entity\Buchungsvorlage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Buchungsvorlage|null find($id, $lockMode = null, $lockVersion = null)
 * @method Buchungsvorlage|null findOneBy(array $criteria, array $orderBy = null)
 * @method Buchungsvorlage[]    findAll()
 * @method Buchungsvorlage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BuchungsvorlageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Buchungsvorlage::class);
    }

    // /**
    //  * @return Buchungsvorlage[] Returns an array of Buchungsvorlage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Buchungsvorlage
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
