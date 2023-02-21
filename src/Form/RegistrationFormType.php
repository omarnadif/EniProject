<?php

namespace App\Form;

use App\Entity\Participant;
use Doctrine\DBAL\Types\IntegerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('nom', TextType::class, [
            'trim' => true,
            'label' => 'Nom: ',
            'required' => true,
        ]);

        $builder->add('prenom', TextType::class, [
            'trim' => true,
            'label' => 'Prénom: ',
            'required' => true,
        ]);

        $builder->add('telephone', TextType::class, [
            'trim' => true,
            'label' => 'Téléphone: ',
            'required' => true,
        ]);

        $builder->add('email', EmailType::class, [
            'trim' => true,
            'label' => 'Email: ',
            'required' => true,
        ]);
        $builder->add('plainPassword', PasswordType::class, [
            'mapped' => false,
            'label' => 'Mot de passe: ',
            'required' => true,
            'attr' => ['autocomplete' => 'new-password'],
            'constraints' => [
                new NotBlank([
                    'message' => 'Le mot de passe est obligatoire !',
                ]),
                new Length([
                    'min' => 8,
                    'max' => 100,
                    'minMessage' => 'Votre mot de passe doit faire au moins {{ limit }} caractères !',
                    'maxMessage' => 'Votre mot de passe doit faire au maximum {{ limit }} caractères !',
                ]),
            ],
        ]);
        $builder->add('agreeTerms', CheckboxType::class, [
            'mapped' => false,
            'label' => 'J\'accepte les CGU',
            'constraints' => [
                new IsTrue([
                    'message' => 'Vous devez accepter les CGU !',
                ]),
            ],
        ]);
        $builder->add('submit', SubmitType::class, [
            'label' => 'S\'inscrire',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
