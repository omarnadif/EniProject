<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
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

        $builder->add('pseudo', TextType::class, [
            'trim' => true,
            'label' => 'Pseudo: ',
            'required' => true,
        ]);

        $builder->add('site', EntityType::class, [
            'class' => Site::class,
            'trim' => true,
            'label' => 'Site: ',
            'required' => true,
            'query_builder' => function(EntityRepository $entityRepository) {
                return $entityRepository->createQueryBuilder('participant')->orderBy('participant.nom', 'ASC');
            },
            'choice_label' => 'nom',
            'attr' => ['class' => 'form-control']
        ]);


        $builder->add('roles',CollectionType::class, [
        'label' => "Role de l'utilisateur : ",
        'entry_type' => ChoiceType::class,
        'entry_options' => [
            'choices'  => [
                'Utilisateur' => 'ROLE_USER',
                'Administrateur' => 'ROLE_ADMIN',
            ],
        ],
            /*'multiple' => false,
            'expanded' => true,*/
        ]);

        $builder->add('actif', CheckboxType::class, [
            'label' => 'Actif',
            'required' => false
        ]);


        $builder->add('plainPassword', RepeatedType::class, [
            'mapped' => false,
            'type' => PasswordType::class,
            'invalid_message' => 'Les deux champs de mot de passe doivent correspondre !',
            'first_options' => [
                'label' => 'Mot de passe: ',
                'attr' => ['autocomplete' => 'new-password'],
            ],
            'second_options' => [
                'label' => 'Confirmation du mot de passe: ',
                'attr' => ['autocomplete' => 'new-password'],
            ],
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
                /*
                 * new EqualTo([
                    'propertyPath' => 'plainPassword',
                    'message' => 'Les deux champs de mot de passe doivent correspondre !',
                ]),*/
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
