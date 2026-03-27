<?php

namespace App\Form;

use App\Entity\SitesClient;
use App\Entity\TypeZone;
use App\Entity\ZonesClient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ZonesClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('sitesClient', EntityType::class, [
                'class' => SitesClient::class,
                'choice_label' => 'nom',
            ])
            ->add('typeZone', EntityType::class, [
                'class' => TypeZone::class,
                'choice_label' => 'nom',
                'required' => false,
                'attr' => ['style' => 'display:none'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ZonesClient::class,
        ]);
    }
}
