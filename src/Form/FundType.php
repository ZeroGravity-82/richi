<?php

namespace App\Form;

use App\Entity\Fund;
use App\Entity\Person;
use App\Form\DataTransformer\KopecksToRublesTransformer;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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

    /** @var KopecksToRublesTransformer */
    private $transformer;

    /**
     * FundType constructor.
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
            ->add('initialBalance', NumberType::class, [
                'scale' => 2,
                'html5' => true,
                'attr' => [
                    'min'  => 0.01,
                    'step' => 0.01,
                ],
            ])
        ;

        $builder->get('initialBalance')
            ->addModelTransformer($this->transformer);
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
