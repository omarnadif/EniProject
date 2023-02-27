<?php

namespace App\Form;

use App\Entity\Ville;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class VilleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('nom', null, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255]),
                    //seulement des lettres et des espaces
                    new Regex('/^[a-zA-Z\s]+$/')
                ]
            ])
            ->add('codePostal', null, [
                'constraints' => [
                    new NotBlank(),
                    //maximum 5 numéros
                    new Regex('/^\d{5}$/')
                ]
            ])
        ;

        $builder->add('Ajouter', SubmitType::class, [
            'attr' => ['class' => 'w-25 btn btn-success btn-lg']
        ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ville::class,
        ]);
    }
}
