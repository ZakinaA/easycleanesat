<?php

namespace App\Controller;

use App\Entity\TypeSupport;
use App\Form\TypeSupportType;
use App\Repository\TypeSupportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/type/support')]
final class TypeSupportController extends AbstractController
{
    #[Route(name: 'app_type_support_index', methods: ['GET'])]
    public function index(TypeSupportRepository $typeSupportRepository): Response
    {
        return $this->render('type_support/index.html.twig', [
            'type_supports' => $typeSupportRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_type_support_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $typeSupport = new TypeSupport();
        $form = $this->createForm(TypeSupportType::class, $typeSupport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($typeSupport);
            $entityManager->flush();

            $uploadedPicto = $form->get('pictoFile')->getData();
            if ($uploadedPicto) {
                $targetDirectory = $this->getParameter('kernel.project_dir') . '/public/PictoTypeSupportPNG';
                if (!is_dir($targetDirectory)) {
                    mkdir($targetDirectory, 0775, true);
                }
                $pictoFileName = $typeSupport->getId() . '.png';
                $uploadedPicto->move($targetDirectory, $pictoFileName);
                $typeSupport->setPicto($pictoFileName);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_type_support_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_support/new.html.twig', [
            'type_support' => $typeSupport,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_support_show', methods: ['GET'])]
    public function show(TypeSupport $typeSupport): Response
    {
        return $this->render('type_support/show.html.twig', [
            'type_support' => $typeSupport,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_support_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeSupport $typeSupport, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TypeSupportType::class, $typeSupport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedPicto = $form->get('pictoFile')->getData();
            if ($uploadedPicto) {
                $targetDirectory = $this->getParameter('kernel.project_dir') . '/public/PictoTypeSupportPNG';
                if (!is_dir($targetDirectory)) {
                    mkdir($targetDirectory, 0775, true);
                }
                $pictoFileName = $typeSupport->getId() . '.png';
                $uploadedPicto->move($targetDirectory, $pictoFileName);
                $typeSupport->setPicto($pictoFileName);
            }
            $entityManager->flush();
            return $this->redirectToRoute('app_type_support_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_support/edit.html.twig', [
            'type_support' => $typeSupport,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_support_delete', methods: ['POST'])]
    public function delete(Request $request, TypeSupport $typeSupport, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $typeSupport->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($typeSupport);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_type_support_index', [], Response::HTTP_SEE_OTHER);
    }
}
