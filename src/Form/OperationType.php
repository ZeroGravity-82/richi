<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Category;
use App\Entity\Operation;
use App\Form\DataTransformer\KopecksToRublesTransformer;
use App\Repository\AccountRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class OperationType extends AbstractType
{
    /** @var Security */
    private $security;

    /** @var EntityManagerInterface */
    private $em;

    /** @var KopecksToRublesTransformer */
    private $transformer;

    /**
     * OperationType constructor.
     *
     * @param Security                   $security
     * @param EntityManagerInterface     $em
     * @param KopecksToRublesTransformer $transformer
     */
    public function __construct(
        Security $security,
        EntityManagerInterface $em,
        KopecksToRublesTransformer $transformer
    ) {
        $this->security    = $security;
        $this->em          = $em;
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var UserInterface $user */
        $user          = $this->security->getUser();
        $operationType = $options['operation_type'];
        /** @var AccountRepository $accountRepo */
        $accountRepo  = $this->em->getRepository(Account::class);
        /** @var CategoryRepository $categoryRepo */
        $categoryRepo = $this->em->getRepository(Category::class);

        $builder
            ->add('date', DateType::class)
            ->add('source', EntityType::class, [
                'class'        => Account::class,
                'choices'      => $accountRepo->findByUser($user),
                'choice_label' => 'name',
            ])
            ->add('target', EntityType::class, [
                'class'        => Account::class,
                'choices'      => $accountRepo->findByUser($user),
                'choice_label' => 'name',
            ])
            ->add('amount', NumberType::class, [
                'scale' => 2,
            ])
            ->add('category', EntityType::class, [
                'class'        => Category::class,
                'choices'      => $categoryRepo->findByOperationType($user, $operationType),
                'choice_label' => 'name',
                'empty_data'   => null,
                'placeholder'  => '---',
                'required'     => false,
            ])
            ->add('description')
        ;

        $builder->get('amount')
            ->addModelTransformer($this->transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Operation::class,
        ]);

        $resolver->setRequired('operation_type');
        $resolver->setAllowedTypes('operation_type', 'int');
    }
}
