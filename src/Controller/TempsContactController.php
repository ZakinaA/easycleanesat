<?php

namespace App\Controller;

use App\Entity\TempsContact;
use App\Form\TempsContactType;
use App\Repository\TempsContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/temps/contact')]
final class TempsContactController extends AbstractController
{
    #[Route(name: 'app_temps_contact_index', methods: ['GET'])]
    public function index(TempsContactRepository $tempsContactRepository): Response
    {
        return $this->render('temps_contact/index.html.twig', [
            'temps_contacts' => $tempsContactRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_temps_contact_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tempsContact = new TempsContact();
        $form = $this->createForm(TempsContactType::class, $tempsContact, [
            'picto_upload_mode' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedPicto = $form->get('pictoFile')->getData();

            $entityManager->persist($tempsContact);
            $entityManager->flush();

            if ($uploadedPicto) {
                $targetDirectory = $this->getParameter('kernel.project_dir').'/public/PictoTempContactPNG';

                if (!is_dir($targetDirectory)) {
                    mkdir($targetDirectory, 0775, true);
                }

                $pictoFileName = $tempsContact->getId().'.png';
                $uploadedPicto->move($targetDirectory, $pictoFileName);

                $tempsContact->setPicto($pictoFileName);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_temps_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('temps_contact/new.html.twig', [
            'temps_contact' => $tempsContact,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_temps_contact_show', methods: ['GET'])]
    public function show(TempsContact $tempsContact): Response
    {
        return $this->render('temps_contact/show.html.twig', [
            'temps_contact' => $tempsContact,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_temps_contact_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TempsContact $tempsContact, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TempsContactType::class, $tempsContact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_temps_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('temps_contact/edit.html.twig', [
            'temps_contact' => $tempsContact,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_temps_contact_delete', methods: ['POST'])]
    public function delete(Request $request, TempsContact $tempsContact, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tempsContact->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($tempsContact);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_temps_contact_index', [], Response::HTTP_SEE_OTHER);
    }
}
