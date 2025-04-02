<?php

namespace App\Service;

use App\Dto\PrioritizationDto;
use App\Entity\Todo;
use App\Entity\User;
use App\Repository\TodoAccessRepository;
use Doctrine\Common\Collections\ArrayCollection;

readonly class PrioritizationService
{

    public function __construct(private TodoAccessRepository $todoAccessRepository)
    {

    }

    public function prioritizeTodo(PrioritizationDto $prioritizationDto, Todo $todo, User $user): int
    {
        $todoAccess = $this->todoAccessRepository->getTodoAccessOfTodoForUser($todo, $user);

        $category = $todoAccess->getCategory();

        $todoAccesses = $this->todoAccessRepository->getTodoAccessesOfCategory($category);

        if($this->isAlreadyInOrder($todoAccesses, $prioritizationDto->position, $todoAccess))
            return $todoAccess->getPrioritization();


        return $this->getInbetweenPrioritization($todoAccesses, $prioritizationDto->position);
    }

    // geht platz verloren, weil normalisierung vor veränderung gemacht wird
    private function getInbetweenPrioritization(ArrayCollection $todoAccesses, int $position): int
    {
        $count = $todoAccesses->count();

        $position = max(0, min($position, $count - 1));

        $beforeTodoAccess = null;
        $afterTodoAccess = null;

        if($position === 0) {
            $afterTodoAccess = $todoAccesses[$position];
        } elseif ($position === $count - 1) {
            $beforeTodoAccess = $todoAccesses[$position];
        } else {
            $beforeTodoAccess = $todoAccesses[$position - 1];
            $afterTodoAccess = $todoAccesses[$position];
        }

        if($beforeTodoAccess !== null && $afterTodoAccess !== null) {

            if($this->checkForNormalization($beforeTodoAccess->getPrioritization(), $afterTodoAccess->getPrioritization())) {
                $this->todoAccessRepository->normalizePriorities($todoAccesses);
                return $this->getInbetweenPrioritization($todoAccesses, $position);
            }

            return (int) ceil(($beforeTodoAccess->getPrioritization() + $afterTodoAccess->getPrioritization()) / 2);
        } elseif ($beforeTodoAccess !== null) {

            return $beforeTodoAccess->getPrioritization() + 1000;
        } elseif ($afterTodoAccess !== null) {

            if($this->checkForNormalization(0, $afterTodoAccess->getPrioritization())) {
                $this->todoAccessRepository->normalizePriorities($todoAccesses);
                return $this->getInbetweenPrioritization($todoAccesses, $position);
            }

            return (int) ceil($afterTodoAccess->getPrioritization() / 2);
        }

        throw new \LogicException('This should never happen!');
    }

    private function checkForNormalization($beforeTodoAccessPriority, $afterTodoAccessPriority): bool {
        $threshold = 10;
        if (($afterTodoAccessPriority - $beforeTodoAccessPriority) < $threshold) {
            return true;
        }
        return false;
    }

    private function isAlreadyInOrder(ArrayCollection $todoAccesses, $position, $todoAccess): bool
    {
        $count = $todoAccesses->count();

        $position = max(0, min($position, $count - 1));

        return isset($todoAccesses[$position]) && $todoAccesses[$position]->getId() === $todoAccess->getId();
    }


}