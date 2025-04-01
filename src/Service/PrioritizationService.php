<?php

namespace App\Service;

use App\Dto\PrioritizationDto;
use App\Entity\Todo;
use App\Entity\User;
use App\Repository\TodoAccessRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PrioritizationService
{

    public function __construct(private readonly TodoAccessRepository $todoAccessRepository)
    {

    }

    public function prioritizeTodo(PrioritizationDto $prioritizationDto, Todo $todo, User $user): int
    {
        $todoAccess = $this->todoAccessRepository->getTodoAccessOfTodoForUser($todo, $user);

        $category = $todoAccess->getCategory();

        $todoAccesses = $this->todoAccessRepository->getTodoAccessesOfCategory($category);

        if($this->isAlreadyInOrder($todoAccesses, $prioritizationDto, $todoAccess))
            return $todoAccess->getPrioritization();

        return $this->getInbetweenPrioritization($todoAccesses, $prioritizationDto);
    }

    // geht platz verloren, weil normalisierung vor veränderung gemacht wird
    private function getInbetweenPrioritization(ArrayCollection $todoAccesses, PrioritizationDto $prioritizationDto): int
    {
        $beforeId = $prioritizationDto->beforeId;
        $afterId = $prioritizationDto->afterId;

        $beforeTodoAccess = null;
        $afterTodoAccess = null;

        foreach ($todoAccesses as $index => $todoAccess) {
            if($beforeId !== null && $todoAccess->getTodoId() === $beforeId) {
                $beforeTodoAccess = $todoAccess;
                $afterTodoAccess = $todoAccesses[$index + 1] ?? null;
                break;
            } elseif ($afterId !== null && $todoAccess->getTodoId() === $afterId) {
                $afterTodoAccess = $todoAccess;
                $beforeTodoAccess = $todoAccesses[$index - 1] ?? null;
                break;
            }
        }

        if($beforeTodoAccess !== null && $afterTodoAccess !== null) {

            if($this->checkForNormalization($beforeTodoAccess->getPrioritization(), $afterTodoAccess->getPrioritization())) {
                $this->todoAccessRepository->normalizePriorities($todoAccesses);
                return $this->getInbetweenPrioritization($todoAccesses, $prioritizationDto);
            }

            return (int) ceil(($beforeTodoAccess->getPrioritization() + $afterTodoAccess->getPrioritization()) / 2);
        } elseif ($beforeTodoAccess !== null) {

            return $beforeTodoAccess->getPrioritization() + 1000;
        } elseif ($afterTodoAccess !== null) {

            if($this->checkForNormalization(0, $afterTodoAccess->getPrioritization())) {
                $this->todoAccessRepository->normalizePriorities($todoAccesses);
                return $this->getInbetweenPrioritization($todoAccesses, $prioritizationDto);
            }

            return (int) ceil($afterTodoAccess->getPrioritization() / 2);
        }

        throw new UnprocessableEntityHttpException(
            'The provided ToDo item cannot be processed because it is not in the same category.'
        );
    }

    private function checkForNormalization($beforeTodoAccessPriority, $afterTodoAccessPriority): bool {
        $threshold = 10;
        if (($afterTodoAccessPriority - $beforeTodoAccessPriority) < $threshold) {
            return true;
        }
        return false;
    }

    private function isAlreadyInOrder(ArrayCollection $todoAccesses, PrioritizationDto $prioritizationDto, $todoAccess): bool
    {
        $beforeId = $prioritizationDto->beforeId;
        $afterId = $prioritizationDto->afterId;

        foreach ($todoAccesses as $index => $currentTodoAccess) {

            if ($currentTodoAccess->getId() === $beforeId) {
                return isset($todoAccesses[$index + 1]) && $todoAccesses[$index + 1]->getId() === $todoAccess->getId();
            }

            if ($currentTodoAccess->getId() === $afterId) {
                return isset($todoAccesses[$index - 1]) && $todoAccesses[$index - 1]->getId() === $todoAccess->getId();
            }
        }

        return false;
    }


}