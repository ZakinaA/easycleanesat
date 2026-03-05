<?php

namespace App\Controller;

use App\Entity\TypeZone;
use App\Form\TypeZoneType;
use App\Repository\TypeZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/type/zone')]
final class TypeZoneController extends AbstractController
{
    #[Route(name: 'app_type_zone_index', methods: ['GET'])]
    public function index(TypeZoneRepository $typeZoneRepository): Response
    {
        return $this->render('type_zone/index.html.twig', [
            'type_zones' => $typeZoneRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_type_zone_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $typeZone = new TypeZone();
        $form = $this->createForm(TypeZoneType::class, $typeZone);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($typeZone);
            $entityManager->flush();

            $uploadedPicto = $form->get('pictoFile')->getData();
            if ($uploadedPicto) {
                $targetDirectory = $this->getParameter('kernel.project_dir') . '/public/PictoTypeZonePNG';
                if (!is_dir($targetDirectory)) {
                    mkdir($targetDirectory, 0775, true);
                }
                $pictoFileName = $typeZone->getId() . '.png';
                $uploadedPicto->move($targetDirectory, $pictoFileName);
                $typeZone->setPicto($pictoFileName);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_type_zone_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_zone/new.html.twig', [
            'type_zone' => $typeZone,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_zone_show', methods: ['GET'])]
    public function show(TypeZone $typeZone): Response
    {
        return $this->render('type_zone/show.html.twig', [
            'type_zone' => $typeZone,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_zone_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeZone $typeZone, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TypeZoneType::class, $typeZone);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedPicto = $form->get('pictoFile')->getData();
            if ($uploadedPicto) {
                $targetDirectory = $this->getParameter('kernel.project_dir') . '/public/PictoTypeZonePNG';
                if (!is_dir($targetDirectory)) {
                    mkdir($targetDirectory, 0775, true);
                }
                $pictoFileName = $typeZone->getId() . '.png';
                $uploadedPicto->move($targetDirectory, $pictoFileName);
                $typeZone->setPicto($pictoFileName);
            }

            $entityManager->flush();
            return $this->redirectToRoute('app_type_zone_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_zone/edit.html.twig', [
            'type_zone' => $typeZone,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_zone_delete', methods: ['POST'])]
    public function delete(Request $request, TypeZone $typeZone, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $typeZone->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($typeZone);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_type_zone_index', [], Response::HTTP_SEE_OTHER);
    }
}
