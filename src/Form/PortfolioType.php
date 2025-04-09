<?php

namespace App\Form;

use App\Entity\Portfolio;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PortfolioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('image', FileType::class, [
            'label' => 'Image (PNG, JPG or JPEG file)',
            'mapped' => false, // added this line
            'required' => true,
            'constraints' => [
                new Image([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'image/png',
                        'image/jpeg',
                        'image/jpg',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid PNG, JPG or JPEG image file',
                ]),
            ],
        ])
            ->add('titre', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez renseigner votre titre']),
                    new Length(['min' => 2, 'max' => 50]),
                    new Callback(function ($value, ExecutionContextInterface $context) {
                        if (!ctype_upper($value[0])) {
                            $context->buildViolation('Le premier caractère doit être une majuscule')
                                ->atPath('titre')
                                ->addViolation();
                        }
                    }),
                ]
            ])
            ->add('description', TextType::class, [
                'constraints' => [
                    new Callback(function ($value, ExecutionContextInterface $context) {
                        if (count(explode(' ', $value)) < 4) {
                            $context->buildViolation('La description doit contenir au moins 4 mots')
                                ->atPath('description')
                                ->addViolation();
                        }
                    }),
                ]
            ])
            ->add('instagram', TextType::class, [
                'constraints' => [
                    new Regex(['pattern' => '/^(https:\/\/www.instagram.com\/)([a-zA-Z0-9._]+)(\/)?$/', 'message' => 'L\'URL Instagram est invalide']),
                ],
                'label' => 'Instagram',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Portfolio::class,
        ]);
    }
}
