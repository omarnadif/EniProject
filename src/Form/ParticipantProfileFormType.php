<?php

namespace App\Form;



use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ParticipantProfileFormType extends AbstractType
{
    /*
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    */

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

        $builder->add('site', Entity::class, [
            'class' => Site::class,
            'trim' => true,
            'label' => 'Choississez votre campus: ',
            'required' => true,
            'query_builder' => function(EntityRepository $entityRepository) {
            return $entityRepository->createQueryBuilder('c')->orderBy('c.siteNom', 'ASC');
            }
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

        $builder->add('submit', SubmitType::class, [
            'label' => 'Modifier',
        ]);

        /*
        // Ajouter un champ "password Actuel"
        $builder->add('password', PasswordType::class, [
            'mapped' => false,
            'required' => true,
            'label' => 'Mot de passe actuel: ',
            'constraints' => [
                new NotBlank(),
                new Callback([$this, 'validateCurrentPassword']),
            ],
        ]);

        // Ajout un champ "nouveau password"
        $builder->add('nouveauPassword', PasswordType::class, [
            'mapped' => false,
            'required' => true,
            'label' => 'Nouveau mot de passe',
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        // Ajout un champ "confirmation password"
        $builder->add('confirmationPassword', PasswordType::class, [
            'mapped' => false,
            'required' => true,
            'label' => 'Confirmation du nouveau mot de passe',
            'constraints' => [
                new NotBlank(),
                new Callback([$this, 'validateNewPassword']),
            ],
        ]);

        */
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
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
}