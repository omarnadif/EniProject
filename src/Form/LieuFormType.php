<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class LieuFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->add('nom', TextType::class, [
            'trim' => true,
            'label' => 'Nom: ',
            'required' => true,
        ]);

        $builder->add('rue', TextType::class, [
            'trim' => true,
            'label' => 'Rue: ',
            'required' => true,
        ]);

        $builder->add('longitude', IntegerType::class, [
            'trim' => true,
            'label' => 'Longitude: ',
            'required' => true,
        ]);

        $builder->add('latitude', IntegerType::class, [
            'trim' => true,
            'label' => 'Latitude: ',
            'required' => true,
        ]);

        $builder->add('ville', EntityType::class, [
            'trim' => true,
            'choice_label'=>'nom',
            'label' => 'Ville: ',
            'attr' => ['class' => 'form-control'],
            'class'=> Ville::class,
            'required' => true,
        ]);

        $builder->add('lieuUploadPicture', FileType::class, [
            'label' => 'Ajoutez des photos du lieu :',

            // unmapped means that this field is not associated to any entity property
            'mapped' => false,
            'required' => false,

            // unmapped fields can't define their validation using annotations
            // in the associated entity, so you can use the PHP constraint classes
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
