<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ParticipantProfileFormType extends AbstractType
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

        $builder->add('photoProfil', FileType::class, [
            'label' => 'Votre image de profil (Fichiers images uniquement)',

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

        $builder->add('submit', SubmitType::class, [
            'label' => 'Modifier',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
/*
     *
    public function validateCurrentPassword($value, ExecutionContextInterface $context)
    {
        $user = $context->getObject()->getUser();
        if (!$this->passwordEncoder->isPasswordValid($user, $value)) {
            $context->buildViolation('Le mot de passe actuel est incorrect.')->addViolation();
        }
    }

    public function validateNewPassword($value, ExecutionContextInterface $context)
    {
        $passwordNew = $context->getObject()->get('nouveauPassword')->getData();
        if ($value !== $passwordNew) {
            $context->buildViolation('Les mots de passe ne sont pas identiques.')->addViolation();
        }
    }
    */