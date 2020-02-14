<?php

namespace App\Form;

use App\Entity\Fund;
use App\Entity\Person;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class FundType extends AbstractType
{
    /** @var Security */
    private $security;

    /** @var EntityManagerInterface */
    private $em;

    /**
     * FundType constructor.
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var UserInterface $user */
        $user = $this->security->getUser();

        /** @var PersonRepository $personRepo */
        $personRepo = $this->em->getRepository(Person::class);

        $builder
            ->add('name')
            ->add('description')
            ->add('person', EntityType::class, [
                'class'       => Person::class,
                'choices'     => $personRepo->findByUser($user),
                'empty_data'  => null,
                'placeholder' => '---',
                'required'    => true,
            ])
            ->add('icon')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Fund::class,
        ]);
    }
}
