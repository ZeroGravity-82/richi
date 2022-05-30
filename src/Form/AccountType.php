<?php

namespace App\Form;

use App\Entity\Account;
use App\Form\DataTransformer\KopecksToRublesTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                'html5' => true,
                'attr' => [
                    'min'  => 0.01,
                    'step' => 0.01,
                ],
            ])
            ->add('archived')
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
