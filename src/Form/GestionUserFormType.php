<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class GestionUserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'constraints' => [
                new NotBlank(),
                new Email(),
            ],
        ]);

        $builder->add('pseudo', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 2, 'max' => 20]),
                ],
        ]);

        $builder->add('nom', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 2, 'max' => 50]),
                ],
        ]);

        $builder->add('prenom', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 2, 'max' => 50]),
                ],
        ]);

        $builder->add('telephone', TelType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 10, 'max' => 20]),
                ],
        ]);

        $builder->add('roles',CollectionType::class, [
            'label' => "Role de l'utilisateur :",
            'required' => true,
            'entry_type' => ChoiceType::class,
            'entry_options' => [
                'label' => false,
                'choices'  => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
            ],
        ]);
        $builder->add('Modifier', SubmitType::class, [
            'attr' => ['class' => 'w-25 btn btn-warning btn-lg']
        ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
