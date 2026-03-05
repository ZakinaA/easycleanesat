<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\SitesClient;
use App\Entity\Contrat;
use App\Entity\ElementSecurite;
use App\Entity\Intervention;
use App\Entity\Redacteur;
use App\Entity\ZonesClient;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterventionType extends AbstractType
{
    /**
     * Construction du formulaire d'intervention avec gestion de la pré-sélection en cascade.
     * 
     * LOGIQUE DE CASCADE :
     * Si zones_client_id fourni → Déduit site, client, contrat
     * Sinon si contrat_id fourni → Déduit site, client
     * Sinon si sites_client_id fourni → Déduit client
     * Sinon si client_id fourni → Utilise uniquement le client
     * Sinon → Aucune pré-sélection, JavaScript Stimulus gère la dynamique
     * 
     * APPROCHE MIXTE :
     * - Avec pré-sélection : query_builder filtre pour 1 seule option (verrouillé)
     * - Sans pré-sélection : query_builder retourne liste vide, JavaScript la remplit en AJAX
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupération des paramètres passés depuis le contrôleur
        $em = $options['em'];
        $clientId = $options['client_id'];
        $sitesClientId = $options['sites_client_id'];
        $contratId = $options['contrat_id'];
        $zonesClientId = $options['zones_client_id'];
        
        // Cascade de données à partir de zones_client_id
        $zonesClient = null;
        $contrat = null;
        $sitesClient = null;
        $client = null;
        
        // Priorité 1 : Zone client (le plus précis, permet de tout déduire)
        if ($zonesClientId) {
            $zonesClient = $em->getRepository(ZonesClient::class)->find($zonesClientId);
            if ($zonesClient) {
                $sitesClient = $zonesClient->getSitesClient();
                if ($sitesClient) {
                    $client = $sitesClient->getClient();
                    // Récupérer le premier contrat du site si disponible
                    if ($sitesClient->getContrats()->count() > 0) {
                        $contrat = $sitesClient->getContrats()->first();
                    }
                }
            }
        }
        // Priorité 2 : Contrat (permet de déduire site et client)
        elseif ($contratId) {
            $contrat = $em->getRepository(Contrat::class)->find($contratId);
            if ($contrat) {
                $sitesClient = $contrat->getSitesClient();
                if ($sitesClient) {
                    $client = $sitesClient->getClient();
                }
            }
        }
        // Priorité 3 : Site client (permet de déduire client)
        elseif ($sitesClientId) {
            $sitesClient = $em->getRepository(SitesClient::class)->find($sitesClientId);
            if ($sitesClient) {
                $client = $sitesClient->getClient();
            }
        }
        // Priorité 4 : Client uniquement
        elseif ($clientId) {
            $client = $em->getRepository(Client::class)->find($clientId);
        }
        
        $builder
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez un client',
                'mapped' => false, 
                'required' => true,
                'data' => $client,
                'query_builder' => function (EntityRepository $er) use ($client) {
                    $qb = $er->createQueryBuilder('c');
                    if ($client) {
                        $qb->where('c.id = :clientId')
                           ->setParameter('clientId', $client->getId());
                    }
                    return $qb->orderBy('c.nom', 'ASC');
                },
            ])
            ->add('site', EntityType::class, [
                'class' => SitesClient::class,
                'choice_label' => function (SitesClient $site) {
                    return $site->getNom();
                },
                'placeholder' => 'Sélectionnez un site',
                'mapped' => false,  // Pas mappé directement sur Intervention (champ intermédiaire)
                'required' => true,
                'data' => $sitesClient,  // Pré-remplissage si disponible
                'query_builder' => function (EntityRepository $er) use ($sitesClient, $client) {
                    $qb = $er->createQueryBuilder('s');
                    if ($sitesClient) {
                        // CAS 1 : Pré-sélection précise (depuis zone ou contrat)
                        $qb->where('s.id = :siteId')
                           ->setParameter('siteId', $sitesClient->getId());
                    } elseif ($client) {
                        // CAS 2 : Filtrage par client (depuis client_id)
                        $qb->where('s.client = :client')
                           ->setParameter('client', $client);
                    } else {
                        // CAS 3 : Aucune pré-sélection, liste vide (Stimulus gère)
                        $qb->where('1 = 0');
                    }
                    return $qb->orderBy('s.nom', 'ASC');
                },
            ])
            ->add('contrat', EntityType::class, [
                'class' => Contrat::class,
                'choice_label' => function (Contrat $contrat) {
                    return $contrat->getNumero();
                },
                'placeholder' => 'Sélectionnez un contrat',
                'required' => false,
                'data' => $contrat,
                'query_builder' => function (EntityRepository $er) use ($contrat, $sitesClient) {
                    $qb = $er->createQueryBuilder('c');
                    if ($contrat) {
                        // Si un contrat est pré-rempli, afficher uniquement ce contrat
                        $qb->where('c.id = :contratId')
                           ->setParameter('contratId', $contrat->getId());
                    } elseif ($sitesClient) {
                        // Si un site est pré-rempli, filtrer par site
                        $qb->where('c.sitesClient = :site')
                           ->setParameter('site', $sitesClient);
                    } else {
                        // Aucune pré-sélection : liste vide, le JS gèrera
                        $qb->where('1 = 0');
                    }
                    return $qb->orderBy('c.numero', 'ASC');
                },
            ])
            ->add('zonesClient', EntityType::class, [
                'class' => ZonesClient::class,
                'choice_label' => function (ZonesClient $zone) {
                    return $zone->getNom();
                },
                'placeholder' => 'Sélectionnez une zone',
                'required' => true,
                'data' => $zonesClient,
                'query_builder' => function (EntityRepository $er) use ($zonesClient, $sitesClient) {
                    $qb = $er->createQueryBuilder('z');
                    if ($zonesClient) {
                        // Si une zone est pré-remplie, afficher uniquement cette zone
                        $qb->where('z.id = :zoneId')
                           ->setParameter('zoneId', $zonesClient->getId());
                    } elseif ($sitesClient) {
                        // Si un site est pré-rempli, filtrer par site
                        $qb->where('z.sitesClient = :site')
                           ->setParameter('site', $sitesClient);
                    } else {
                        // Aucune pré-sélection : liste vide, le JS gèrera
                        $qb->where('1 = 0');
                    }
                    return $qb->orderBy('z.nom', 'ASC');
                },
            ])
            ->add('redacteur', EntityType::class, [
                'class' => Redacteur::class,
                'choice_label' => 'initial',
                'placeholder' => 'Sélectionnez un rédacteur',
                'required' => false,
            ])
            ->add('dateCreation', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'html5' => false,
                'attr' => ['placeholder' => 'jj/mm/aaaa'],
            ])
            ->add('nbTravailleur', IntegerType::class, [
                'attr' => [
                    'min' => 1,
                    'max' => 6,
                ],
            ])
            ->add('dureeHeure')
            ->add('dureeMinute')
            ->add('elementSecurites', EntityType::class, [
                'class' => ElementSecurite::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
            'client_id' => null,
            'sites_client_id' => null,
            'contrat_id' => null,
            'zones_client_id' => null,
            'em' => null,
        ]);
    }
}
