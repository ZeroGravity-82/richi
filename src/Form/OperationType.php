<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Operation;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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

    /**
     * OperationType constructor.
     *
     * @param Security               $security
     * @param EntityManagerInterface $em
     */
    public function __construct(Security $security, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->em       = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var UserInterface $user */
        $user = $this->security->getUser();
        /** @var AccountRepository $accountRepo */
        $accountRepo = $this->em->getRepository(Account::class);

        $builder
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
            ->add('amount')
            ->add('description')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Operation::class,
        ]);
    }
}
