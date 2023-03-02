<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

class LieuFormType extends AbstractType
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

        $builder->add('rue', TextType::class, [
            'trim' => true,
            'label' => 'Rue: ',
            'required' => true,
            'constraints' => [
                new Regex([
                    'pattern' => '/^[a-zA-Z0-9\s\-]+$/',
                    'message' => 'Le champ rue ne doit contenir que des lettres, des chiffres, des espaces ou des tirets.'
                ])
            ]
        ]);


        $builder->add('longitude', IntegerType::class, [
            'trim' => true,
            'label' => 'Longitude: ',
            'required' => true,
            'constraints' => [
                new Range([
                    'min' => -180,
                    'max' => 180,
                    'notInRangeMessage' => 'La longitude doit être comprise entre -180 et 180 degrés.',
                ])
            ]
        ]);

        $builder->add('latitude', NumberType::class, [
            'trim' => true,
            'label' => 'Latitude: ',
            'required' => true,
            'scale' => 6, // précision de 6 décimales après la virgule
            'constraints' => [
                new Range([
                    'min' => -90,
                    'max' => 90,
                    'notInRangeMessage' => 'La latitude doit être comprise entre -90 et 90 degrés.',
                ])
            ]
        ]);


        $builder->add('ville', EntityType::class, [
            'trim' => true,
            'choice_label'=>'nom',
            'label' => 'Ville: ',
            'attr' => ['class' => 'form-control'],
            'class'=> Ville::class,
            'required' => true,
            'invalid_message' => 'La ville est invalide.',
        ]);

        $builder->add('lieuUploadPicture', FileType::class, [
            'label' => 'Ajoutez des photos du lieu :',
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

        $builder->add('Ajouter', SubmitType::class, [
            'attr' => ['class' => 'w-25 btn btn-primary btn-lg']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
