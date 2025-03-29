<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Todo;
use App\Entity\TodoAccess;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TodoAccess>
 */
class TodoAccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TodoAccess::class);
    }

    public function save(Todo $todo, User $user, Category $category): TodoAccess
    {
        $todoAccess = new TodoAccess();

        $todoAccess->setTodo($todo);
        $todoAccess->setPrioritization(100);
        $todoAccess->setAssignee($user);
        $todoAccess->setCategory($category);

        $this->getEntityManager()->persist($todoAccess);
        $this->getEntityManager()->flush();

        return $todoAccess;
    }

    public function doesUserHaveAccessToTodo(Todo $todo, User $user): bool
    {
        return (bool) $this->createQueryBuilder('ta')
            ->leftJoin('t.todo', 't')
            ->leftJoin('ta.assignee', 'u')
            ->where('t.id = :todoId')
            ->andWhere('u.id = :userId')
            ->setParameter('todoId', $todo->getId())
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return TodoAccess[] Returns an array of TodoAccess objects
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

    //    public function findOneBySomeField($value): ?TodoAccess
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
