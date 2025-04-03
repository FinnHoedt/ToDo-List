<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Todo;
use App\Entity\TodoAccess;
use App\Entity\User;
use App\Service\PrioritizationService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function PHPUnit\Framework\isEmpty;

/**
 * @extends ServiceEntityRepository<TodoAccess>
 */
class TodoAccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TodoAccess::class);
    }

    public function save(Todo $todo, User $user, ?Category $category): TodoAccess
    {
        $todoAccess = new TodoAccess();

        $todoAccess->setTodo($todo);
        $todoAccess->setPrioritization($this->getNextPrioritization($user, $category));
        $todoAccess->setAssignee($user);
        $todoAccess->setCategory($category);

        $this->getEntityManager()->persist($todoAccess);
        $this->getEntityManager()->flush();

        return $todoAccess;
    }

    public function share(Todo $todo, User $userToGetShared): ?TodoAccess
    {
        if($this->getTodoAccessOfTodoForUser($todo, $userToGetShared) !== null)
        {
            return null;
        }

        $todoAccess = new TodoAccess();

        $todoAccess->setTodo($todo);
        $todoAccess->setPrioritization($this->getNextPrioritization($userToGetShared, null));
        $todoAccess->setAssignee($userToGetShared);
        $todoAccess->setShared(true);

        $this->getEntityManager()->persist($todoAccess);
        $this->getEntityManager()->flush();

        return $todoAccess;
    }

    public function revoke(Todo $todo, User $userToGetRevoked): bool
    {
        $todoAccess = $this->getTodoAccessOfTodoForUser($todo, $userToGetRevoked);

        if($todoAccess === null)
        {
            return false;
        }

        $this->getEntityManager()->remove($todoAccess);
        $this->getEntityManager()->flush();

        return true;
    }

    public function getTodoAccessOfTodoForUser(Todo $todo, User $user): ?TodoAccess
    {
        return $this->createQueryBuilder('ta')
            ->leftJoin('ta.todo', 't')
            ->leftJoin('ta.assignee', 'u')
            ->where('t.id = :todoId')
            ->andWhere('u.id = :userId')
            ->setParameter('todoId', $todo->getId())
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getTodoAccessesOfCategory(Category $category): ArrayCollection
    {
        return new ArrayCollection(
            $this->createQueryBuilder('ta')
                ->leftJoin('ta.category', 'c')
                ->where('c.id = :categoryId')
                ->setParameter('categoryId', $category->getId())
                ->orderBy('ta.prioritization', 'ASC')
                ->getQuery()
                ->getResult()
        );
    }

    public function getUncategorizedTodoAccessesOfUser(User $user): ArrayCollection
    {
        return new ArrayCollection(
            $this->createQueryBuilder('ta')
                ->leftJoin('ta.category', 'c')
                ->where('c.id IS NULL')
                ->andWhere('ta.assignee = :userId')
                ->setParameter('userId', $user->getId())
                ->orderBy('ta.prioritization', 'ASC')
                ->getQuery()
                ->getResult()
        );
    }

    public function prioritizeTodo(Todo $todo, User $user, int $newPriority): TodoAccess
    {
        $todoAccess = $this->getTodoAccessOfTodoForUser($todo, $user);

        $todoAccess->setPrioritization($newPriority);
        $this->getEntityManager()->flush();

        return $todoAccess;
    }

    public function normalizePriorities(ArrayCollection $todoAccesses): void
    {
        $step = 1000;
        $priority = $step;

        foreach($todoAccesses as $todoAccess) {
            $todoAccess->setPrioritization($priority);
            $priority += $step;
        }
        $this->getEntityManager()->flush();
    }

    private function getNextPrioritization(User $user, ?Category $category): int
    {
        $todoAccesses = $category === null
            ? $this->getUncategorizedTodoAccessesOfUser($user)
            : $this->getTodoAccessesOfCategory($category);

        if($todoAccesses->isEmpty())
            return 1000;

        return $todoAccesses->last()->getPrioritization() + 1000;
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
