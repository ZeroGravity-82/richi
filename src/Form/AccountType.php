<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Person;
use App\Form\DataTransformer\KopecksToRublesTransformer;
use App\Repository\AccountRepository;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountType extends AbstractType
{
    /** @var Security */
    private $security;

    /** @var EntityManagerInterface */
    private $em;

    /** @var KopecksToRublesTransformer */
    private $transformer;

    /**
     * CategoryType constructor.
     *
     * @param Security                   $security
     * @param EntityManagerInterface     $em
     * @param KopecksToRublesTransformer $transformer
     */
    public function __construct(Security $security, EntityManagerInterface $em, KopecksToRublesTransformer $transformer)
    {
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
        $user        = $this->security->getUser();
        /** @var AccountRepository $accountRepo */
        $accountRepo = $this->em->getRepository(Account::class);
        /** @var PersonRepository $personRepo */
        $personRepo  = $this->em->getRepository(Person::class);

        $builder
            ->add('parent', EntityType::class, [
                'class'        => Account::class,
                'choices'      => $accountRepo->findAbleToBeParent($user),
                'empty_data'   => null,
                'placeholder'  => '---',
                'required'     => false,
            ])
            ->add('name')
            ->add('icon')
            ->add('person', EntityType::class, [
                'class'        => Person::class,
                'choices'      => $personRepo->findByUser($user),
                'choice_label' => 'name',
                'empty_data'   => null,
                'placeholder'  => '---',
                'required'     => false,
            ])
            ->add('initialBalance', NumberType::class, [
                'scale' => 2,
            ])
        ;

        $builder->get('initialBalance')
            ->addModelTransformer($this->transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Account::class,
        ]);
    }
}
