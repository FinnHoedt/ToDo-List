<?php

namespace App\Repository;

use App\Dto\TodoDto;
use App\Entity\Category;
use App\Entity\Todo;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Collection;

/**
 * @extends ServiceEntityRepository<Todo>
 */
class TodoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Todo::class);
    }

    public function save(TodoDto $todoDto): Todo
    {
        $todo = new Todo();

        $todo->setTitle($todoDto->title);
        $todo->setDescription($todoDto->description);

        $this->getEntityManager()->persist($todo);
        $this->getEntityManager()->flush();

        return $todo;
    }

    public function edit(TodoDto $todoDto, Todo $todo): Todo
    {
        $todo->setTitle($todoDto->title);
        $todo->setDescription($todoDto->description);

        $this->getEntityManager()->persist($todo);
        $this->getEntityManager()->flush();

        return $todo;
    }

    public function delete(Todo $todo): void
    {
        $this->getEntityManager()->remove($todo);
        $this->getEntityManager()->flush();
    }

    public function getTodosOfUser(User $user): ArrayCollection
    {
        return new ArrayCollection($this->createQueryBuilder('t')
            ->innerJoin('t.todoAccesses', 'ta')
            ->addSelect('ta')
            ->where('ta.assignee = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('ta.prioritization', 'DESC')
            ->getQuery()
            ->getResult());
    }

    public function getTodosOfUserOfSpecificCategory(User $user, Category $category): ArrayCollection
    {
        return new ArrayCollection($this->createQueryBuilder('t')
            ->innerJoin('t.todoAccesses', 'ta')
            ->addSelect('ta')
            ->where('ta.assignee = :userId')
            ->andWhere('ta.category = :category')
            ->setParameter('userId', $user->getId())
            ->setParameter('category', $category->getId())
            ->orderBy('ta.prioritization', 'DESC')
            ->getQuery()
            ->getResult());
    }

    public function toggleCompleted(Todo $todo): Todo
    {
        $todo->setCompleted(!$todo->getCompleted());

        $this->getEntityManager()->flush();

        return $todo;
    }


    //    /**
    //     * @return Todo[] Returns an array of Todo objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Todo
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
