<?php

namespace App\Controller;

use App\Entity\SupportClient;
use App\Entity\ZonesClient;
use App\Entity\TypeSupport;
use App\Form\SupportClientType;
use App\Repository\SupportClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/support/client')]
final class SupportClientController extends AbstractController
{
    #[Route(name: 'app_support_client_index', methods: ['GET'])]
    public function index(SupportClientRepository $supportClientRepository): Response
    {
        return $this->render('support_client/index.html.twig', [
            'support_clients' => $supportClientRepository->findAll(),
        ]);
    }

    #[Route('/new/{zone_id}', name: 'app_support_client_new', methods: ['GET', 'POST'], defaults: ['zone_id' => null])]
    public function new(Request $request, EntityManagerInterface $entityManager, ?int $zone_id = null): Response
    {
        $zone = null;
        if ($zone_id) {
            $zone = $entityManager->getRepository(ZonesClient::class)->find($zone_id);
        }

        if ($request->isMethod('POST')) {
            $selectedIds = $request->request->all('selected_supports');
            if ($zone && !empty($selectedIds)) {
                $typeSupportRepo = $entityManager->getRepository(TypeSupport::class);
                foreach ($selectedIds as $typeSupportId) {
                    $typeSupport = $typeSupportRepo->find($typeSupportId);
                    if ($typeSupport) {
                        $supportClient = new SupportClient();
                        $supportClient->setZonesClient($zone);
                        $supportClient->setTypeSupport($typeSupport);
                        $entityManager->persist($supportClient);
                    }
                }
                $entityManager->flush();
                $clientId = $zone->getSitesClient()->getClient()->getId();
                return $this->redirectToRoute('app_client_show', ['id' => $clientId], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('support_client/new.html.twig', [
            'zone' => $zone,
        ]);
    }

    #[Route('/{id}', name: 'app_support_client_show', methods: ['GET'])]
    public function show(SupportClient $supportClient): Response
    {
        return $this->render('support_client/show.html.twig', [
            'support_client' => $supportClient,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_support_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SupportClient $supportClient, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SupportClientType::class, $supportClient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $clientId = $supportClient->getZonesClient()->getSitesClient()->getClient()->getId();
            return $this->redirectToRoute('app_client_show', ['id' => $clientId], Response::HTTP_SEE_OTHER);
        }

        return $this->render('support_client/edit.html.twig', [
            'support_client' => $supportClient,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_support_client_delete', methods: ['POST'])]
    public function delete(Request $request, SupportClient $supportClient, EntityManagerInterface $entityManager): Response
    {
        $clientId = $supportClient->getZonesClient()->getSitesClient()->getClient()->getId();
        if ($this->isCsrfTokenValid('delete' . $supportClient->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($supportClient);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_client_show', ['id' => $clientId], Response::HTTP_SEE_OTHER);
    }
}
