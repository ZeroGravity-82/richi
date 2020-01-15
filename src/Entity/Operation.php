<?php

namespace App\Entity;

use App\Enum\OperationTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OperationRepository")
 */
class Operation
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="operations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="`date`", type="date")
     */
    private $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="`type`", type="smallint")
     */
    private $type;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Account")
     */
    private $source;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Account")
     */
    private $target;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

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
     * Operation constructor.
     */
    public function __construct()
    {
        $now             = new \DateTime();
        $this->date      = $now;
        $this->createdAt = $now;
        $this->updatedAt = $now;
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
     * @param UserInterface|null $user
     *
     * @return Operation
     */
    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @param \DateTimeInterface $date
     *
     * @return Operation
     */
    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return integer|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param integer $type
     *
     * @return Operation
     */
    public function setType(int $type): self
    {
        if (!in_array($type, OperationTypeEnum::getAvailableTypes())) {
            throw new \InvalidArgumentException('Invalid operation type.');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return Account|null
     */
    public function getSource(): ?Account
    {
        return $this->source;
    }

    /**
     * @param Account|null $source
     *
     * @return Operation
     */
    public function setSource(?Account $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return Account|null
     */
    public function getTarget(): ?Account
    {
        return $this->target;
    }

    /**
     * @param Account|null $target
     *
     * @return Operation
     */
    public function setTarget(?Account $target): self
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return integer|null
     */
    public function getAmount(): ?int
    {
        return $this->amount;
    }

    /**
     * @param integer $amount
     *
     * @return Operation
     */
    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return Operation
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
     * @return Operation
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @Assert\Callback
     *
     * @param ExecutionContextInterface $context
     *
     * @return void
     */
    public function validateFields(ExecutionContextInterface $context): void
    {
        $incomeOperation   = $this->getType() === OperationTypeEnum::TYPE_INCOME;
        $expenseOperation  = $this->getType() === OperationTypeEnum::TYPE_EXPENSE;
        $transferOperation = $this->getType() === OperationTypeEnum::TYPE_TRANSFER;
        if (!$this->source && ($expenseOperation || $transferOperation)) {
            $context
                ->buildViolation('Source account should not be blank')
                ->atPath('source')
                ->addViolation();
        }

        if (!$this->target && ($incomeOperation || $transferOperation)) {
            $context
                ->buildViolation('Target account should not be blank')
                ->atPath('target')
                ->addViolation();
        }

        if ($this->amount < 0) {
            $context
                ->buildViolation('Amount should be equal or greater than zero')
                ->atPath('amount')
                ->addViolation();
        }
    }
}
