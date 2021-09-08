<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }
    public function findPostComments($value)
    { $entityManager = $this->getEntityManager();


        $query = $entityManager->createQuery(
            'SELECT c
            FROM App\Entity\Comment c
             WHERE c.idpost = :id
             Order by c.commentdate'
            
        )->setParameter('id', $value);
            
        
        return $query->getResult();}


    public function findBySearchCategorie($search,$categorie)
        { $entityManager = $this->getEntityManager();
    if ($categorie=="") 
            $query = $entityManager->createQuery(
                'SELECT p
                FROM App\Entity\Post p
                 WHERE (p.title like :search
                 or p.content like :search)
                '
                
    )->setParameter('search', '%'.$search.'%');
    else $query = $entityManager->createQuery(
                    'SELECT p
                    FROM App\Entity\Post p
                     WHERE (p.title like :search
                     or p.content like :search)
                     and p.idcategorie = :idcategorie
                    '
                    
        )->setParameter('search', '%'.$search.'%')
        ->setParameter('idcategorie', $categorie);
                               
            
            return $query->getResult();}
    
    public function findPostByCategorie($value)
    { $entityManager = $this->getEntityManager();


        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Post p
             WHERE p.idcategorie = :id
             and p.isdeleted=false
             Order by p.postdate desc'
            
)->setParameter('id', $value);
            
        
        return $query->getResult();}
        
        public function findMostViewedPosts()
    { $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Post p
             
             where p.isdeleted=false
             Order by p.views desc
             '
            
)->setMaxResults(5);
        
        return $query->getResult();}

        public function findRecentlyActivePosts()
        { $entityManager = $this->getEntityManager();
            $query = $entityManager->createQuery(
                'SELECT p
                FROM App\Entity\Post p,App\Entity\Comment c
                 where p.id = c.idpost
                 Order by c.commentdate desc
                 '
                
    )->setMaxResults(5);
            
            return $query->getResult();}
    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
