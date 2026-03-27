<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Form\InterventionType;
use App\Repository\InterventionRepository;
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
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $intervention = new Intervention();
        
        // Récupération des paramètres d'URL pour la pré-sélection
        $clientId = $request->query->get('client_id');
        $sitesClientId = $request->query->get('sites_client_id');
        $contratId = $request->query->get('contrat_id');
        $zonesClientId = $request->query->get('zones_client_id');
        
        // Création du formulaire avec les paramètres
        // Ces options sont passées à InterventionType::buildForm()
        $form = $this->createForm(InterventionType::class, $intervention, [
            'client_id' => $clientId,
            'sites_client_id' => $sitesClientId,
            'contrat_id' => $contratId,
            'zones_client_id' => $zonesClientId,
            'em' => $entityManager,  // Nécessaire pour charger les entités dans le FormType
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($intervention);
            $entityManager->flush();

            return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intervention/new.html.twig', [
            'intervention' => $intervention,
            'form' => $form,
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
    public function edit(Request $request, Intervention $intervention, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InterventionType::class, $intervention);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intervention/edit.html.twig', [
            'intervention' => $intervention,
            'form' => $form,
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
}
