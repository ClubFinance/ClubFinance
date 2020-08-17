<?php

namespace App\Repository;

use App\Entity\Hauptbuch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Hauptbuch|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hauptbuch|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hauptbuch[]    findAll()
 * @method Hauptbuch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HauptbuchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hauptbuch::class);
    }

    // /**
    //  * @return Hauptbuch[] Returns an array of Hauptbuch objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Hauptbuch
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
