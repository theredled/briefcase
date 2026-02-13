<?php

namespace App\Repository;

use App\Entity\Briefcase;
use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 *
 * @method Document|null find($id, $lockMode = null, $lockVersion = null)
 * @method Document|null findOneBy(array $criteria, array $orderBy = null)
 * @method Document[]    findAll()
 * @method Document[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    public function save(Document $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findFromBriefcase(string|Briefcase $bc)
    {
        $bc = $bc instanceof Briefcase
            ? $bc
            : $this->getEntityManager()->getRepository(Briefcase::class)->findOneBy(['token' => $bc]);

        $qb = $this->createQueryBuilder('i')
            ->andWhere('i.briefcase = :bc')->setParameter('bc', $bc);

        if (!$this->getAuthChecker()->isGranted('briefcase_fullaccess', $bc))
            $qb->andWhere('i.sensible = FALSE OR i.sensible IS NULL');

        return $qb->getQuery()->getResult();
    }

    public function remove(Document $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
