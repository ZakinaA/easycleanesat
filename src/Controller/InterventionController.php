<?php

namespace App\Controller;

use App\Entity\Actions;
use App\Entity\Intervention;
use App\Entity\JourDeLaSemaine;
use App\Entity\Plage;
use App\Entity\SupportClient;
use App\Entity\SuppInter;
use App\Form\InterventionType;
use App\Repository\ActionsRepository;
use App\Repository\InterventionRepository;
use App\Repository\JourDeLaSemaineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/intervention')]
final class InterventionController extends AbstractController
{
    #[Route(name: 'app_intervention_index', methods: ['GET'])]
    public function index(InterventionRepository $interventionRepository): Response
    {
        return $this->render('intervention/index.html.twig', [
            'interventions' => $interventionRepository->findAll(),
        ]);
    }

    /**
     * Crée une nouvelle intervention.
     *
     * Supporte 4 modes d'entrée via paramètres URL :
     * 1. ?client_id=X          → Pré-remplit uniquement le client
     * 2. ?sites_client_id=X    → Pré-remplit client + site
     * 3. ?contrat_id=X         → Pré-remplit client + site + contrat
     * 4. ?zones_client_id=X    → Pré-remplit client + site + contrat + zone
     *
     * Sans paramètres : formulaire vierge avec listes déroulantes dynamiques (AJAX/Stimulus)
     */
    #[Route('/new', name: 'app_intervention_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, JourDeLaSemaineRepository $jourRepository, ActionsRepository $actionsRepository): Response
    {
        $intervention = new Intervention();

        // Récupération des paramètres d'URL pour la pré-sélection
        $clientId = $request->query->get('client_id');
        $sitesClientId = $request->query->get('sites_client_id');
        $contratId = $request->query->get('contrat_id');
        $zonesClientId = $request->query->get('zones_client_id');

        // Création du formulaire avec les paramètres
        $form = $this->createForm(InterventionType::class, $intervention, [
            'client_id' => $clientId,
            'sites_client_id' => $sitesClientId,
            'contrat_id' => $contratId,
            'zones_client_id' => $zonesClientId,
            'em' => $entityManager,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($intervention);

            // Traitement des plages horaires
            $plageData = $request->request->all('plage') ?? [];
            foreach ($plageData as $jourId => $periods) {
                $jour = $jourRepository->find($jourId);
                if (!$jour) continue;
                foreach (['matin', 'apmidi'] as $period) {
                    $debut = $periods[$period]['debut'] ?? '';
                    $fin   = $periods[$period]['fin']   ?? '';
                    if ($debut !== '' && $fin !== '') {
                        $plage = new Plage();
                        $plage->setHeureDebut(new \DateTime($debut));
                        $plage->setHeureFin(new \DateTime($fin));
                        $plage->setIntervention($intervention);
                        $plage->setJourDeLaSemaine($jour);
                        $entityManager->persist($plage);
                    }
                }
            }

            $entityManager->flush();

            // Traitement des supports et actions
            $this->persistSuppInterData($request, $intervention, $entityManager);

            return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
        }

        $jours = $jourRepository->findBy([], ['id' => 'ASC']);

        return $this->render('intervention/new.html.twig', [
            'intervention' => $intervention,
            'form'         => $form,
            'jours'        => $jours,
            'plagesMap'    => [],
            'actionsJson'  => $this->buildActionsJson($actionsRepository),
            'suppInterJson' => 'null',
            'initialZoneId' => $zonesClientId,
        ]);
    }

    #[Route('/{id}', name: 'app_intervention_show', methods: ['GET'])]
    public function show(Intervention $intervention): Response
    {
        return $this->render('intervention/show.html.twig', [
            'intervention' => $intervention,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_intervention_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Intervention $intervention, EntityManagerInterface $entityManager, JourDeLaSemaineRepository $jourRepository, ActionsRepository $actionsRepository): Response
    {
        $zonesClient = $intervention->getZonesClient();
        $contrat     = $intervention->getContrat();

        $form = $this->createForm(InterventionType::class, $intervention, [
            'em'              => $entityManager,
            'zones_client_id' => $zonesClient ? $zonesClient->getId() : null,
            'contrat_id'      => $contrat ? $contrat->getId() : null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $intervention->setDateModificaion(new \DateTime());
            $intervention->setNumVersion(($intervention->getNumVersion() ?? 1) + 1);

            // Supprimer les anciennes plages
            foreach ($intervention->getPlages() as $oldPlage) {
                $entityManager->remove($oldPlage);
            }

            // Supprimer les anciens SuppInter
            foreach ($intervention->getSuppInters() as $oldSuppInter) {
                $entityManager->remove($oldSuppInter);
            }

            // Recréer les plages depuis le formulaire
            $plageData = $request->request->all('plage') ?? [];
            foreach ($plageData as $jourId => $periods) {
                $jour = $jourRepository->find($jourId);
                if (!$jour) continue;
                foreach (['matin', 'apmidi'] as $period) {
                    $debut = $periods[$period]['debut'] ?? '';
                    $fin   = $periods[$period]['fin']   ?? '';
                    if ($debut !== '' && $fin !== '') {
                        $plage = new Plage();
                        $plage->setHeureDebut(new \DateTime($debut));
                        $plage->setHeureFin(new \DateTime($fin));
                        $plage->setIntervention($intervention);
                        $plage->setJourDeLaSemaine($jour);
                        $entityManager->persist($plage);
                    }
                }
            }

            $entityManager->flush();

            // Recréer les supports et actions
            $this->persistSuppInterData($request, $intervention, $entityManager);

            return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
        }

        $jours = $jourRepository->findBy([], ['id' => 'ASC']);

        // Construire la map des plages existantes : [jourId][matin|apmidi] => Plage
        $plagesMap = [];
        foreach ($intervention->getPlages() as $plage) {
            $jourId = $plage->getJourDeLaSemaine()->getId();
            $period = ((int)$plage->getHeureDebut()->format('H') < 12) ? 'matin' : 'apmidi';
            $plagesMap[$jourId][$period] = $plage;
        }

        return $this->render('intervention/edit.html.twig', [
            'intervention'  => $intervention,
            'form'          => $form,
            'jours'         => $jours,
            'plagesMap'     => $plagesMap,
            'actionsJson'   => $this->buildActionsJson($actionsRepository),
            'suppInterJson' => $this->buildSuppInterJson($intervention),
            'initialZoneId' => $zonesClient ? $zonesClient->getId() : null,
        ]);
    }

    #[Route('/{id}', name: 'app_intervention_delete', methods: ['POST'])]
    public function delete(Request $request, Intervention $intervention, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$intervention->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($intervention);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
    }

    private function buildActionsJson(ActionsRepository $actionsRepository): string
    {
        $actions = $actionsRepository->findAllActif();
        $data = array_map(fn(Actions $action) => $this->serializeAction($action), $actions);

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private function serializeAction(Actions $action): array
    {
        $tache = null;
        $necessaires = [];

        foreach ($action->getNecessaire() as $nec) {
            $typeId  = $nec->getTypeNecessaire()?->getId();
            $typeNom = strtolower((string) $nec->getTypeNecessaire()?->getNom());
            if ($typeId === 4) {
                $tache = ['nom' => $nec->getNom(), 'code' => $nec->getCode()];
            } else {
                $necessaires[] = ['code' => $nec->getCode(), 'nom' => $nec->getNom(), 'type_nom' => $typeNom];
            }
        }

        $meo = null;
        if ($mp = $action->getMeoProduit()) {
            $meo = [
                'produit_code'      => $mp->getProduit()?->getCode(),
                'produit_couleur'   => $mp->getProduit()?->getCouleur(),
                'contenant_id'      => $mp->getContenant()?->getId(),
                'volume_eau'        => $mp->getContenant()?->getVolumeEau(),
                'moyen_dosage_id'   => $mp->getMoyenDosage()?->getId(),
                'moyen_dosage_code' => $mp->getMoyenDosage()?->getCode(),
                'volume_produit'    => $mp->getVolumeProduit(),
                'temps_contact_id'  => $mp->getTempsContact()?->getId(),
            ];
        }

        return [
            'id'          => $action->getId(),
            'label'       => $tache ? $tache['nom'] : ('Action #' . $action->getId()),
            'tache'       => $tache,
            'necessaires' => $necessaires,
            'meo'         => $meo,
        ];
    }

    private function buildSuppInterJson(Intervention $intervention): string
    {
        $supports = [];
        $actionsMap = [];

        foreach ($intervention->getSuppInters() as $suppInter) {
            $pos = $suppInter->getOrdre();
            $supports[] = [
                'support_client_id' => $suppInter->getSupportClient()->getId(),
                'order_position'    => $pos,
                'nom'               => $suppInter->getSupportClient()->getTypeSupport()->getNom(),
            ];

            foreach ($suppInter->getActions() as $action) {
                $aId = $action->getId();
                if (!isset($actionsMap[$aId])) {
                    $serialized = $this->serializeAction($action);
                    $actionsMap[$aId] = array_merge($serialized, ['actionsId' => $aId, 'suppInterPositions' => []]);
                }
                $actionsMap[$aId]['suppInterPositions'][] = $pos;
            }
        }

        // Sort supports by position
        usort($supports, fn($a, $b) => $a['order_position'] <=> $b['order_position']);

        return json_encode([
            'supports' => $supports,
            'actions'  => array_values($actionsMap),
        ], JSON_UNESCAPED_UNICODE);
    }

    private function persistSuppInterData(Request $request, Intervention $intervention, EntityManagerInterface $em): void
    {
        $raw = $request->request->get('supp_inter_data', '');
        if (empty($raw)) {
            return;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['supports'])) {
            return;
        }

        // Map position → SuppInter entity
        $suppInterMap = [];
        foreach ($data['supports'] as $suppData) {
            $suppInter = new SuppInter();
            $suppInter->setIntervention($intervention);
            $suppInter->setSupportClient($em->getReference(SupportClient::class, (int)$suppData['support_client_id']));
            $suppInter->setOrdre((int)$suppData['order_position']);
            $em->persist($suppInter);
            $suppInterMap[(int)$suppData['order_position']] = $suppInter;
        }
        $em->flush();

        // Lier les actions aux SuppInter
        foreach ($data['actions'] ?? [] as $actionData) {
            $action = $em->getReference(Actions::class, (int)$actionData['actions_id']);
            foreach ($actionData['supp_inter_positions'] as $position) {
                if (isset($suppInterMap[(int)$position])) {
                    $suppInterMap[(int)$position]->addAction($action);
                }
            }
        }
        $em->flush();
    }
}
