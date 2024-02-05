<?php

namespace App\Repository;

use App\Entity\DownloadableFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DownloadableFile>
 *
 * @method DownloadableFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method DownloadableFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method DownloadableFile[]    findAll()
 * @method DownloadableFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DownloadableFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DownloadableFile::class);
    }

    public function save(DownloadableFile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findNotSensible()
    {
        $qb = $this->createQueryBuilder('i')->andWhere('i.sensible = FALSE OR i.sensible IS NULL');
        return $qb->getQuery()->getResult();
    }

    public function remove(DownloadableFile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return DownloadableFile[] Returns an array of DownloadableFile objects
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

//    public function findOneBySomeField($value): ?DownloadableFile
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
