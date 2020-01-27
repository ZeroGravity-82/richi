<?php

namespace App\Form;

use App\Entity\Account;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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

    /**
     * CategoryType constructor.
     *
     * @param Security               $security
     * @param EntityManagerInterface $em
     */
    public function __construct(Security $security, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->em       = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var UserInterface $user */
        $user = $this->security->getUser();

        /** @var AccountRepository $accountRepo */
        $accountRepo = $this->em->getRepository(Account::class);

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
        ;
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
