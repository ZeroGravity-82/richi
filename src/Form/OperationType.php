<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Category;
use App\Entity\Fund;
use App\Entity\Operation;
use App\Entity\Person;
use App\Entity\Tag;
use App\Form\DataTransformer\KopecksToRublesTransformer;
use App\Repository\AccountRepository;
use App\Repository\CategoryRepository;
use App\Repository\FundRepository;
use App\Repository\PersonRepository;
use App\Repository\TagRepository;
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
        /** @var PersonRepository $personRepo */
        $personRepo   = $this->em->getRepository(Person::class);
        /** @var TagRepository $tagRepo */
        $tagRepo      = $this->em->getRepository(Tag::class);
        /** @var FundRepository $fundRepo */
        $fundRepo     = $this->em->getRepository(Fund::class);

        $builder
            ->add('date', DateType::class, [
                'widget'       => 'single_text',
            ])
            ->add('source', EntityType::class, [
                'class'        => Account::class,
                'choices'      => $accountRepo->findNotArchived($user),
            ])
            ->add('target', EntityType::class, [
                'class'        => Account::class,
                'choices'      => $accountRepo->findNotArchived($user),
            ])
            ->add('amount', NumberType::class, [
                'scale' => 2,
                'html5' => true,
                'attr' => [
                    'min'  => 0.01,
                    'step' => 0.01,
                ],
            ])
            ->add('category', EntityType::class, [
                'class'        => Category::class,
                'choices'      => $categoryRepo->findByOperationType($user, $operationType),
                'empty_data'   => null,
                'placeholder'  => '---',
                'required'     => false,
            ])
            ->add('person', EntityType::class, [
                'class'        => Person::class,
                'choices'      => $personRepo->findByUser($user),
                'empty_data'   => null,
                'placeholder'  => '---',
                'required'     => true,
            ])
            ->add('tag', EntityType::class, [
                'class'        => Tag::class,
                'choices'      => $tagRepo->findByUser($user),
                'empty_data'   => null,
                'placeholder'  => '---',
                'required'     => false,
            ])
            ->add('fund', EntityType::class, [
                'class'        => Fund::class,
                'choices'      => $fundRepo->findByUser($user),
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
