<?php

namespace App\Repository;

use App\Entity\Acces;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Acces|null find($id, $lockMode = null, $lockVersion = null)
 * @method Acces|null findOneBy(array $criteria, array $orderBy = null)
 * @method Acces[]    findAll()
 * @method Acces[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Acces::class);
    }

    // /**
    //  * @return Acces[] Returns an array of Acces objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Acces
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
