<?php

namespace App\Controller;

use App\Entity\Actions;
use App\Form\ActionsType;
use App\Repository\ActionsRepository;
use App\Repository\TypeNecessaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/actions')]
final class ActionsController extends AbstractController
{
    #[Route(name: 'app_actions_index', methods: ['GET'])]
    public function index(ActionsRepository $actionsRepository): Response
    {
        return $this->render('actions/index.html.twig', [
            'actions' => $actionsRepository->findAllActif(),
        ]);
    }

    #[Route('/inactif', name: 'app_actions_inactif', methods: ['GET'])]
    public function indexInactif(ActionsRepository $actionsRepository): Response
    {
        return $this->render('actions/index_inactif.html.twig', [
            'actions' => $actionsRepository->findAllInactif(),
        ]);
    }

    #[Route('/new', name: 'app_actions_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TypeNecessaireRepository $typeNecessaireRepository): Response
    {
        $action = new Actions();
        $form = $this->createForm(ActionsType::class, $action);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($action);
            $entityManager->flush();

            return $this->redirectToRoute('app_actions_index', [], Response::HTTP_SEE_OTHER);
        }

        $typesNecessaires = $typeNecessaireRepository->findAll();

        return $this->render('actions/new.html.twig', [
            'action' => $action,
            'form' => $form,
            'typesNecessaires' => $typesNecessaires,
        ]);
    }

    #[Route('/{id}', name: 'app_actions_show', methods: ['GET'])]
    public function show(Actions $action): Response
    {
        return $this->render('actions/show.html.twig', [
            'action' => $action,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_actions_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Actions $action, EntityManagerInterface $entityManager, TypeNecessaireRepository $typeNecessaireRepository): Response
    {
        $form = $this->createForm(ActionsType::class, $action);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_actions_index', [], Response::HTTP_SEE_OTHER);
        }

        $typesNecessaires = $typeNecessaireRepository->findAll();

        return $this->render('actions/edit.html.twig', [
            'action' => $action,
            'form' => $form,
            'typesNecessaires' => $typesNecessaires,
        ]);
    }

    #[Route('/{id}', name: 'app_actions_delete', methods: ['POST'])]
    public function delete(Request $request, Actions $action, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$action->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($action);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_actions_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/toggle-actif', name: 'app_actions_toggle_actif', methods: ['POST'])]
    public function toggleActif(Request $request, Actions $action, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle_actif'.$action->getId(), $request->getPayload()->getString('_token'))) {
            $action->setActif(!$action->isActif());
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_actions_show', ['id' => $action->getId()], Response::HTTP_SEE_OTHER);
    }
}
