<?php

namespace App\Form;

use App\Entity\Contenant;
use App\Entity\MeoProduit;
use App\Entity\MoyenDosage;
use App\Entity\Produit;
use App\Entity\TempsContact;
use App\Entity\UniteVolume;
use App\Repository\ProduitRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeoProduitType extends AbstractType
{
    public function __construct(private ProduitRepository $produitRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('volumeProduit', TextType::class, [
                'attr' => [
                    'maxlength' => 4,
                ],
            ])
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => function(Produit $produit) {
                    return sprintf('%s - %s', $produit->getCode(), $produit->getNom());
                },
                'choice_attr' => function (Produit $produit): array {
                    return [
                        'data-code' => $produit->getCode() ?? '',
                        'data-color' => $produit->getCouleur() ?? '',
                    ];
                },
                'query_builder' => function () {
                    return $this->produitRepository->createQueryBuilder('p')
                        ->andWhere('p.actif = :actif')
                        ->setParameter('actif', true)
                        ->orderBy('p.nom', 'ASC');
                },
            ])
            ->add('contenant', EntityType::class, [
                'class' => Contenant::class,
                'choice_label' => function(Contenant $contenant) {
                    return sprintf('%s - %s', $contenant->getNom(), $contenant->getVolumeEau());
                },
                'choice_attr' => function (Contenant $contenant): array {
                    return [
                        'data-id' => (string) ($contenant->getId() ?? ''),
                        'data-volume' => $contenant->getVolumeEau() !== null ? (string) $contenant->getVolumeEau() : '',
                        'data-unit' => $contenant->getUniteVolume()?->getNom() ?? '',
                    ];
                },
            ])
            ->add('uniteVolume', EntityType::class, [
                'class' => UniteVolume::class,
                'choice_label' => 'nom',
                'choice_attr' => function (UniteVolume $uniteVolume): array {
                    return [
                        'data-unit' => $uniteVolume->getNom() ?? '',
                    ];
                },
            ])
            ->add('moyenDosage', EntityType::class, [
                'class' => MoyenDosage::class,
                'choice_label' => 'nom',
                'choice_attr' => function (MoyenDosage $moyenDosage): array {
                    return [
                        'data-id' => (string) ($moyenDosage->getId() ?? ''),
                        'data-code' => $moyenDosage->getCode() ?? '',
                    ];
                },
            ])
            ->add('tempsContact', EntityType::class, [
                'class' => TempsContact::class,
                'choice_attr' => function (TempsContact $tempsContact): array {
                    return [
                        'data-id' => (string) ($tempsContact->getId() ?? ''),
                    ];
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MeoProduit::class,
        ]);
    }
}
