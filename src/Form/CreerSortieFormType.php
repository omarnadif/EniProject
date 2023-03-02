<?php

namespace App\Form;

use App\Entity\Author;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CreerSortieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->add('nom', TextType::class, [
            'trim' => true,
            'label' => 'Nom: ',
            'required' => true,
            'constraints' => [
                new Length([
                    'min' => 2,
                    'max' => 255,
                    'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                    'maxMessage' => 'Le nom ne peut pas contenir plus de {{ limit }} caractères.'
                ]),
                new Regex([
                    'pattern' => '/^[a-zA-Z0-9 _-]+$/',
                    'message' => 'Le nom ne peut contenir que des lettres, des chiffres, des espaces, des tirets et des underscores.'
                ])
            ]
        ]);

        $builder->add('dateHeureDebut', DateTimeType::class, [
            'widget'=>'single_text',
            'trim' => true,
            'required' => true,
            'attr' => ['class' => 'form-control'],
            'constraints' => [
                new GreaterThanOrEqual([
                    'value' => 'today',
                    'message' => 'La date et l\'heure de début doivent être postérieures ou égales à la date d\'aujourd\'hui.'
                ]),
                new LessThanOrEqual([
                    'propertyPath' => 'parent.all[dateLimiteInscription].data',
                    'message' => 'La date et l\'heure de début ne peuvent pas être postérieures à la date limite d\'inscription.'
                ]),
            ]
        ]);

        $builder->add('dateLimiteInscription', DateTimeType::class, [
            'widget'=>'single_text',
            'trim' => true,
            'required' => true,
            'attr' => ['class' => 'form-control'],
            'constraints' => [
                new GreaterThanOrEqual([
                    'value' => 'today',
                    'message' => 'La date limite d\'inscription doit être postérieure ou égale à la date d\'aujourd\'hui.'
                ]),
                new LessThanOrEqual([
                    'propertyPath' => 'parent.all[dateHeureDebut].data',
                    'message' => 'La date limite d\'inscription ne peut pas être postérieure à la date et l\'heure de début de la sortie.'
                ]),
            ]
        ]);

        $builder->add('duree', IntegerType::class, [
            'trim' => true,
            'required' => true,
            'attr' => ['class' => 'form-control'],
            'constraints' => [
                new NotBlank([
                    'message' => 'La durée ne doit pas être vide.',
                ]),
                new NotNull([
                    'message' => 'La durée ne doit pas être nulle.',
                ]),
                new Positive([
                    'message' => 'La durée doit être positive.',
                ]),
            ],
        ]);

        $builder->add('nbInscriptionsMax', IntegerType::class, [
            'trim' => true,
            'required' => false,
            'attr' => ['class' => 'form-control'],
            'constraints' => [
                new PositiveOrZero([
                    'message' => 'Le nombre maximum d\'inscriptions doit être un nombre positif ou nul.'
                ])
            ]
        ]);

        $builder->add('sortieUploadPicture', FileType::class, [
            'label' => 'Ajoutez une photo de la sortie :',
            'mapped' => false,
            'required' => false,
            'attr' => [
                'data-preview' => '#preview',
            ],
            'constraints' => [
                new File([
                    'maxSize' => '4096k',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/jpg',
                        'image/png',
                        'image/gif',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid image file',
                ])
            ],
        ]);

        $builder->add('infosSortie', TextareaType::class, [
            'trim' => true,
            'label' => 'Nom: ',
            'required' => true,
            'attr' => ['class' => 'form-control']
        ]);

        $builder->add('sites', EntityType::class, [
            'trim' => true,
            'required' => true,
            'class' => Site::class,
            'choice_label'=>'nom',
            'attr' => ['class' => 'form-control']

        ]);

        $builder->add('lieu', EntityType::class, [
            'trim' => true,
            'required' => true,
            'class' => Lieu::class,
            'choice_label'=>'nom',
            'choice_value' => 'id',
            'attr' => ['class' => 'form-control']

        ]);

        $builder->add('etat', EntityType::class, [
            'trim' => true,
            'label' => 'Etat: ',
            'required' => true,
            'class' => Etat::class,
            'choice_label'=>'libelle',
            'choice_value' => 'libelle',
            'attr' => ['class' => 'form-control'],
        ]);

        $builder->add('Enregistrer', SubmitType::class, [
            'attr' => ['class' => 'w-25 btn btn-primary btn-lg']
        ]);

    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}