<?php

namespace App\Repository;

use App\Entity\Categorie;
use App\Entity\Post;
use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Categorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Categorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Categorie[]    findAll()
 * @method Categorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
    }
// /**
    //  * @return Categorie[] Returns an array of Categorie objects
    //  */
    public function findAllLastPost()
    { $entityManager = $this->getEntityManager();
$categorielastpost=array();
$categories=$this->findAll();
//dump($categoriepost);
foreach ($categories as $categorie)
 {
        $query = $entityManager->createQuery(
            'SELECT p.id,p.title
            FROM App\Entity\Post p
             WHERE p.idcategorie = :id
             and p.isdeleted=false
             AND p.postdate = (
                select max(pa.postdate) 
                FROM App\Entity\Post pa
                where pa.id = p.id
                            )
                            order by p.postdate desc'
             
    
)->setParameter('id', $categorie);            
        
         array_push($categorielastpost,$query->getResult()) ;
 }
 return $categorielastpost;
}
    public function findAllPostCount()
    { $entityManager = $this->getEntityManager();
$categoriepostcount=array();
$categoriepost=$this->findAll();
//dump($categoriepost);
foreach ($categoriepost as $categorie)
 {
        $query = $entityManager->createQuery(
            'SELECT count(p) as total
            FROM App\Entity\Post p
            
             WHERE p.idcategorie = :id
             and p.isdeleted=false
'
                      
    
)->setParameter('id', $categorie)
;
            
        
         array_push($categoriepostcount,$query->getResult()) ;
 }
 return $categoriepostcount;
    }
    public function findAllExceptDeleted()
    { $entityManager = $this->getEntityManager();

        return $query = $entityManager->createQuery(
            'SELECT p as
            FROM App\Entity\Post p
                 where p.isdeleted=:bool'   
             
    
)
->setParameter('bool', 'true');
;    }
    // /**
    //  * @return Categorie[] Returns an array of Categorie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Categorie
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
