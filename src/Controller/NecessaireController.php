<?php

namespace App\Controller;

use App\Entity\Necessaire;
use App\Form\NecessaireType;
use App\Repository\NecessaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/necessaire')]
final class NecessaireController extends AbstractController
{
    #[Route(name: 'app_necessaire_index', methods: ['GET'])]
    public function index(NecessaireRepository $necessaireRepository): Response
    {
        return $this->render('necessaire/index.html.twig', [
            'necessaires' => $necessaireRepository->findAllActif(),
        ]);
    }

    #[Route('/inactif', name: 'app_necessaire_inactif', methods: ['GET'])]
    public function indexInactif(NecessaireRepository $necessaireRepository): Response
    {
        return $this->render('necessaire/index_inactif.html.twig', [
            'necessaires' => $necessaireRepository->findAllInactif(),
        ]);
    }

    #[Route('/new', name: 'app_necessaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $necessaire = new Necessaire();
        $form = $this->createForm(NecessaireType::class, $necessaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($necessaire);
            $entityManager->flush();

            return $this->redirectToRoute('app_necessaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('necessaire/new.html.twig', [
            'necessaire' => $necessaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_necessaire_show', methods: ['GET'])]
    public function show(Necessaire $necessaire): Response
    {
        return $this->render('necessaire/show.html.twig', [
            'necessaire' => $necessaire,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_necessaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Necessaire $necessaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NecessaireType::class, $necessaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_necessaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('necessaire/edit.html.twig', [
            'necessaire' => $necessaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_necessaire_delete', methods: ['POST'])]
    public function delete(Request $request, Necessaire $necessaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$necessaire->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($necessaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_necessaire_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/toggle-actif', name: 'app_necessaire_toggle_actif', methods: ['POST'])]
    public function toggleActif(Request $request, Necessaire $necessaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle_actif'.$necessaire->getId(), $request->getPayload()->getString('_token'))) {
            $necessaire->setActif(!$necessaire->isActif());
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_necessaire_show', ['id' => $necessaire->getId()], Response::HTTP_SEE_OTHER);
    }
}
