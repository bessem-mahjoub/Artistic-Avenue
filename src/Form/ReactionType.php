<?php

namespace App\Form;

use App\Entity\Reaction;
use App\Entity\Portfolio;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ReactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Love' => 'love',
                    'Haha' => 'haha',
                    'Grr' => 'grr',
                    'Sad' => 'sad',
                ],
            ])
            ->add('idPortfolio', HiddenType::class, [
                'data' => $options['idPortfolio'] ? $options['idPortfolio'] : null,
                'data_class' => Portfolio::class,
                'property_path' => 'idPortfolio',
            ])
        ;
    }
        
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reaction::class,
            'idPortfolio' => null,
        ]);
    }
}
