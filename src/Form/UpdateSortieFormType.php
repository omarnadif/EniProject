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
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateSortieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->add('nom', TextType::class, [
            'trim' => true,
            'label' => 'Nom: ',
            'required' => true,
            'attr' => ['class' => 'form-control']
        ]);

        $builder->add('dateHeureDebut', DateTimeType::class, [
            'widget'=>'single_text',
            'trim' => true,
           'required' => true,
            'attr' => ['class' => 'form-control'],
        ]);

        $builder->add('dateLimiteInscription', DateTimeType::class, [
            'widget'=>'single_text',
            'trim' => true,
            'required' => true,
            'attr' => ['class' => 'form-control'],
        ]);

        $builder->add('duree', IntegerType::class, [
            'trim' => true,
            'required' => true,
            'attr' => ['class' => 'form-control']
        ]);

        $builder->add('nbInscriptionsMax', IntegerType::class, [
            'trim' => true,
            'required' => false,
            'attr' => ['class' => 'form-control']
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



//            ->add('sites')
//            ->add('etatRelation')
//            ->add('ParticipantOrganise')
//            ->add('ParticipantInscrit')



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