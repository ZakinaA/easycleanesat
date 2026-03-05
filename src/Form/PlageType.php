<?php

namespace App\Form;

use App\Entity\Intervention;
use App\Entity\JourDeLaSemaine;
use App\Entity\Plage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('heureDebut')
            ->add('heureFin')
            ->add('intervention', EntityType::class, [
                'class' => Intervention::class,
                'choice_label' => 'id',
            ])
            ->add('jourDeLaSemaine', EntityType::class, [
                'class' => JourDeLaSemaine::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Plage::class,
        ]);
    }
}
