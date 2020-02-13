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
    /** @var KopecksToRublesTransformer */
    private $transformer;

    /**
     * CategoryType constructor.
     *
     * @param KopecksToRublesTransformer $transformer
     */
    public function __construct(KopecksToRublesTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('icon')
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
