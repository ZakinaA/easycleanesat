<?php

namespace App\Form;

use App\Entity\Actions;
use App\Entity\MeoProduit;
use App\Entity\Necessaire;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('meo_produit', EntityType::class, [
                'class' => MeoProduit::class,
                'choice_label' => function (MeoProduit $meoProduit) {
                    $label = 'ID: ' . $meoProduit->getId();
                    if ($meoProduit->getProduit()) {
                        $label .= ' - ' . $meoProduit->getProduit()->getNom() . ' (' . $meoProduit->getProduit()->getCode() . ')';
                    }
                    if ($meoProduit->getContenant()) {
                        $label .= ' - Contenant: ' . $meoProduit->getContenant()->getId();
                    }
                    return $label;
                },
                'label' => 'Produit (MEO)',
            ])
            ->add('necessaire', EntityType::class, [
                'class' => Necessaire::class,
                'choice_label' => function (Necessaire $necessaire) {
                    return $necessaire->getCode() . ' - ' . $necessaire->getNom();
                },
                'multiple' => true,
                'expanded' => true,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Actions::class,
        ]);
    }
}
