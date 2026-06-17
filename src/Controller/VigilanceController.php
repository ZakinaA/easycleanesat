<?php

namespace App\Controller;

use App\Entity\Vigilance;
use App\Form\VigilanceType;
use App\Repository\VigilanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/vigilance')]
final class VigilanceController extends AbstractController
{
    #[Route(name: 'app_vigilance_index', methods: ['GET'])]
    public function index(VigilanceRepository $vigilanceRepository): Response
    {
        return $this->render('vigilance/index.html.twig', [
            'vigilances' => $vigilanceRepository->findAllActif(),
        ]);
    }

    #[Route('/inactif', name: 'app_vigilance_inactif', methods: ['GET'])]
    public function indexInactif(VigilanceRepository $vigilanceRepository): Response
    {
        return $this->render('vigilance/index_inactif.html.twig', [
            'vigilances' => $vigilanceRepository->findAllInactif(),
        ]);
    }

    #[Route('/new', name: 'app_vigilance_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vigilance = new Vigilance();
        $form = $this->createForm(VigilanceType::class, $vigilance, [
            'picto_upload_mode' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedPicto = $form->get('pictoFile')->getData();

            $entityManager->persist($vigilance);
            $entityManager->flush();

            if ($uploadedPicto) {
                $targetDirectory = $this->getParameter('kernel.project_dir').'/public/PictoVigilancePNG';

                if (!is_dir($targetDirectory)) {
                    mkdir($targetDirectory, 0775, true);
                }

                $pictoFileName = $vigilance->getId().'.png';
                $uploadedPicto->move($targetDirectory, $pictoFileName);

                $vigilance->setPicto($pictoFileName);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_vigilance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vigilance/new.html.twig', [
            'vigilance' => $vigilance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vigilance_show', methods: ['GET'])]
    public function show(Vigilance $vigilance): Response
    {
        return $this->render('vigilance/show.html.twig', [
            'vigilance' => $vigilance,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vigilance_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vigilance $vigilance, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VigilanceType::class, $vigilance, [
            'picto_upload_mode' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedPicto = $form->get('pictoFile')->getData();

            if ($uploadedPicto) {
                $targetDirectory = $this->getParameter('kernel.project_dir').'/public/PictoVigilancePNG';

                if (!is_dir($targetDirectory)) {
                    mkdir($targetDirectory, 0775, true);
                }

                $pictoFileName = $vigilance->getId().'.png';
                $targetPath = $targetDirectory.'/'.$pictoFileName;

                if (file_exists($targetPath)) {
                    unlink($targetPath);
                }

                $uploadedPicto->move($targetDirectory, $pictoFileName);
                $vigilance->setPicto($pictoFileName);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_vigilance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vigilance/edit.html.twig', [
            'vigilance' => $vigilance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/toggle-actif', name: 'app_vigilance_toggle_actif', methods: ['POST'])]
    public function toggleActif(Request $request, Vigilance $vigilance, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle_actif'.$vigilance->getId(), $request->getPayload()->getString('_token'))) {
            $vigilance->setActif(!$vigilance->isActif());
            
            // if needed, cascade disabling related vigilance_interventions could be added here
            
            $entityManager->flush();
            $this->addFlash('success', 'Le statut a été mis à jour.');
        }

        return $this->redirectToRoute('app_vigilance_show', ['id' => $vigilance->getId()], Response::HTTP_SEE_OTHER);
    }
}
