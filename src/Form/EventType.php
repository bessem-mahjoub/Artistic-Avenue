<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\Form\Extension\Core\Type\DateType;


// ...

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_org')
            ->add('date_debut', DateType::class, [
                'widget' => 'single_text', // Utiliser un widget de type "single_text" pour le champ de date
            ])
            ->add('date_fin', DateType::class, [
                'widget' => 'single_text', // Utiliser un widget de type "single_text" pour le champ de date
            ])
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
