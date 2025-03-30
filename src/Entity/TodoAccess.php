<?php

namespace App\Entity;

use App\Repository\TodoAccessRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TodoAccessRepository::class)]
class TodoAccess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['todoAccess:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['todoAccess:read'])]
    private ?int $prioritization = null;

    #[ORM\Column]
    #[Groups(['todoAccess:read'])]
    private ?bool $shared = false;

    #[ORM\ManyToOne(targetEntity: Todo::class, inversedBy: 'todoAccess')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Todo $todo = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'todoAccess')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $assignee = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'todoAccess')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Category $category = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrioritization(): ?int
    {
        return $this->prioritization;
    }

    public function setPrioritization(int $prioritization): self
    {
        $this->prioritization = $prioritization;

        return $this;
    }

    public function isShared(): ?bool
    {
        return $this->shared;
    }

    public function setShared(bool $shared): self
    {
        $this->shared = $shared;

        return $this;
    }

    public function getTodo(): ?Todo
    {
        return $this->todo;
    }

    #[Groups(['todoAccess:read'])]
    public function getTodoId(): ?int
    {
        return $this->todo->getId();
    }

    public function setTodo(Todo $todo): self
    {
        $this->todo = $todo;

        return $this;
    }

    public function getAssignee(): ?User
    {
        return $this->assignee;
    }

    #[Groups(['todoAccess:read'])]
    public function getAssigneeId(): ?int
    {
        return $this->assignee->getId();
    }

    public function setAssignee(?User $assignee): self
    {
        $this->assignee = $assignee;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    #[Groups(['todoAccess:read'])]
    public function getCategoryId(): ?int
    {
        return $this->category?->getId();
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
