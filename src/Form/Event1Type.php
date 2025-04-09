<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class Event1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_org')
            ->add('date_debut')
            ->add('date_fin')
            ->add('affiche')
            ->add('adresse')
            ->add('nbr_place')
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'concert' => 'concert',
                    'theatre' => 'theatre',
                    'master_class' => 'master_class',
                    'cinema' => 'cinema',
                    // Add other options as needed
                ],
                'placeholder' => 'Select a type', // Optional: Add a placeholder label
                // Add other configuration options for the combo box field here
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
