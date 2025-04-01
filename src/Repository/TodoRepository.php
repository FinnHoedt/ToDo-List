<?php

namespace App\Repository;

use App\Dto\PrioritizationDto;
use App\Dto\TodoDto;
use App\Entity\Category;
use App\Entity\Todo;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Collection;
use function Sodium\add;

/**
 * @extends ServiceEntityRepository<Todo>
 */
class TodoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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

    public function getTodosOfUser(User $user): array
    {
        $categories = $user->getCategories();
        $groupedTodos = [
            'uncategorized' => $this->getTodosOfUserOfSpecificCategory($user, null),
            'categorized' => [],
        ];

        foreach ($categories as $category) {
            $groupedTodos['categorized'][$category->getTitle()] = $this->getTodosOfUserOfSpecificCategory($user, $category);
        }

        return $groupedTodos;
    }


    public function getTodosOfUserOfSpecificCategory(User $user, ?Category $category): ArrayCollection
    {
        $query = $this->createQueryBuilder('t')
            ->innerJoin('t.todoAccesses', 'ta')
            ->addSelect('ta')
            ->where('ta.assignee = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('ta.prioritization', 'ASC');

        if ($category !== null) {
            $query->andWhere('ta.category = :category')
                ->setParameter('category', $category->getId());
        } else {
            $query->andWhere('ta.category IS NULL');
        }

        return new ArrayCollection($query->getQuery()->getResult());
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
