<?php

namespace App\Entity;

use App\Enum\OperationTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 * @ORM\Table(
 *     name="category",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="category_uq", columns={"user_id", "name"})
 *     }
 * )
 * @UniqueEntity(
 *     fields={"user", "name"},
 *     errorPath="name",
 *     message="Category with the same name already exists."
 * )
 */
class Category
{
    /**
     * @var integer
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var UserInterface
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="categories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var integer
     *
     * @ORM\Column(name="operation_type", type="smallint")
     *
     * @see OperationTypeEnum
     */
    private $operationType;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var Category|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Category")
     */
    private $parent;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * Category constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $now              = new \DateTime();
        $this->createdAt  = $now;
        $this->updatedAt  = $now;
    }

    /**
     * @return integer|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * @param UserInterface $user
     *
     * @return Category
     */
    public function setUser(UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return integer|null
     *
     * @see OperationTypeEnum
     */
    public function getOperationType(): ?int
    {
        return $this->operationType;
    }

    /**
     * @param integer $operationType
     *
     * @see OperationTypeEnum
     *
     * @return Category
     */
    public function setOperationType(int $operationType): self
    {
        if (!in_array($operationType, [
            OperationTypeEnum::TYPE_INCOME,
            OperationTypeEnum::TYPE_EXPENSE,
        ])) {
            throw new \InvalidArgumentException('Unsupported operation type.');
        }

        $this->operationType = $operationType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Category
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Category|null
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * @param Category|null $parent
     *
     * @return Category
     */
    public function setParent(?Category $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeInterface $updatedAt
     *
     * @return Category
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
