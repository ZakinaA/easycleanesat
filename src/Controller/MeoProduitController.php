<?php

namespace App\Controller;

use App\Entity\MeoProduit;
use App\Form\MeoProduitType;
use App\Repository\MeoProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/meo/produit')]
final class MeoProduitController extends AbstractController
{
    #[Route(name: 'app_meo_produit_index', methods: ['GET'])]
    public function index(MeoProduitRepository $meoProduitRepository): Response
    {
        return $this->render('meo_produit/index.html.twig', [
            'meo_produits' => $meoProduitRepository->findAllActif(),
        ]);
    }

    #[Route('/inactif', name: 'app_meo_produit_inactif', methods: ['GET'])]
    public function indexInactif(MeoProduitRepository $meoProduitRepository): Response
    {
        return $this->render('meo_produit/index_inactif.html.twig', [
            'meo_produits' => $meoProduitRepository->findAllInactif(),
        ]);
    }

    #[Route('/new', name: 'app_meo_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $meoProduit = new MeoProduit();
        $form = $this->createForm(MeoProduitType::class, $meoProduit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($meoProduit);
            $entityManager->flush();

            return $this->redirectToRoute('app_meo_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('meo_produit/new.html.twig', [
            'meo_produit' => $meoProduit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_meo_produit_show', methods: ['GET'])]
    public function show(MeoProduit $meoProduit): Response
    {
        return $this->render('meo_produit/show.html.twig', [
            'meo_produit' => $meoProduit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_meo_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MeoProduit $meoProduit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MeoProduitType::class, $meoProduit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_meo_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('meo_produit/edit.html.twig', [
            'meo_produit' => $meoProduit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_meo_produit_delete', methods: ['POST'])]
    public function delete(Request $request, MeoProduit $meoProduit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$meoProduit->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($meoProduit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_meo_produit_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/toggle-actif', name: 'app_meo_produit_toggle_actif', methods: ['POST'])]
    public function toggleActif(Request $request, MeoProduit $meoProduit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle_actif'.$meoProduit->getId(), $request->getPayload()->getString('_token'))) {
            $meoProduit->setActif(!$meoProduit->isActif());
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_meo_produit_show', ['id' => $meoProduit->getId()], Response::HTTP_SEE_OTHER);
    }
}
