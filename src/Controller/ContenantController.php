<?php

namespace App\Controller;

use App\Entity\Contenant;
use App\Form\ContenantType;
use App\Repository\ContenantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/contenant')]
final class ContenantController extends AbstractController
{
    #[Route(name: 'app_contenant_index', methods: ['GET'])]
    public function index(ContenantRepository $contenantRepository): Response
    {
        return $this->render('contenant/index.html.twig', [
            'contenants' => $contenantRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_contenant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contenant = new Contenant();
        $form = $this->createForm(ContenantType::class, $contenant, [
            'picto_upload_mode' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedPicto = $form->get('pictoFile')->getData();

            $entityManager->persist($contenant);
            $entityManager->flush();

            if ($uploadedPicto) {
                $targetDirectory = $this->getParameter('kernel.project_dir').'/public/PictoContenantPNG';

                if (!is_dir($targetDirectory)) {
                    mkdir($targetDirectory, 0775, true);
                }

                $pictoFileName = $contenant->getId().'.png';
                $uploadedPicto->move($targetDirectory, $pictoFileName);

                $contenant->setPicto($pictoFileName);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_contenant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contenant/new.html.twig', [
            'contenant' => $contenant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contenant_show', methods: ['GET'])]
    public function show(Contenant $contenant): Response
    {
        return $this->render('contenant/show.html.twig', [
            'contenant' => $contenant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contenant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contenant $contenant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContenantType::class, $contenant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_contenant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contenant/edit.html.twig', [
            'contenant' => $contenant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contenant_delete', methods: ['POST'])]
    public function delete(Request $request, Contenant $contenant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contenant->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($contenant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_contenant_index', [], Response::HTTP_SEE_OTHER);
    }
}
