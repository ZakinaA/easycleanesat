<?php

namespace App\Controller;

use App\Repository\TypeZoneRepository;
use App\Repository\SitesClientRepository;
use App\Repository\ContratRepository;
use App\Repository\ZonesClientRepository;
use App\Repository\TypeSupportRepository;
use App\Repository\SupportClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/api')]
class ApiController extends AbstractController
{
    #[Route('/type-zone/search', name: 'api_type_zone_search', methods: ['GET'])]
    public function searchTypeZone(Request $request, TypeZoneRepository $typeZoneRepository): JsonResponse
    {
        $query = trim($request->query->get('q', ''));

        if ($query === '') {
            $results = [];
        } else {
            $results = $typeZoneRepository->searchByNomOrDescription($query);
        }

        return $this->json($results);
    }

    #[Route('/type-support/search', name: 'api_type_support_search', methods: ['GET'])]
    public function searchTypeSupport(Request $request, TypeSupportRepository $typeSupportRepository): JsonResponse
    {
        $query = trim($request->query->get('q', ''));
        if ($query === '') {
            $results = [];
        } else {
            $results = $typeSupportRepository->searchByNomOrDescription($query);
        }
        return $this->json($results);
    }

    #[Route('/sites-by-client/{clientId}', name: 'api_sites_by_client', methods: ['GET'])]
    public function getSitesByClient(int $clientId, SitesClientRepository $sitesClientRepository): JsonResponse
    {
        $sites = $sitesClientRepository->createQueryBuilder('s')
            ->where('s.client = :clientId')
            ->setParameter('clientId', $clientId)
            ->orderBy('s.nom', 'ASC')
            ->getQuery()
            ->getResult();

        $data = array_map(function ($site) {
            return ['id' => $site->getId(), 'nom' => $site->getNom()];
        }, $sites);

        return $this->json($data);
    }

    #[Route('/contrats-by-site/{siteId}', name: 'api_contrats_by_site', methods: ['GET'])]
    public function getContratsBySite(int $siteId, ContratRepository $contratRepository): JsonResponse
    {
        $contrats = $contratRepository->createQueryBuilder('c')
            ->where('c.sitesClient = :siteId')
            ->setParameter('siteId', $siteId)
            ->orderBy('c.numero', 'ASC')
            ->getQuery()
            ->getResult();

        $data = array_map(function ($contrat) {
            return ['id' => $contrat->getId(), 'numero' => $contrat->getNumero()];
        }, $contrats);

        return $this->json($data);
    }

    #[Route('/zones-by-site/{siteId}', name: 'api_zones_by_site', methods: ['GET'])]
    public function getZonesBySite(int $siteId, ZonesClientRepository $zonesClientRepository): JsonResponse
    {
        $zones = $zonesClientRepository->createQueryBuilder('z')
            ->where('z.sitesClient = :siteId')
            ->setParameter('siteId', $siteId)
            ->orderBy('z.nom', 'ASC')
            ->getQuery()
            ->getResult();

        $data = array_map(function ($zone) {
            return ['id' => $zone->getId(), 'nom' => $zone->getNom()];
        }, $zones);

        return $this->json($data);
    }

    #[Route('/supports-by-zone/{zoneId}', name: 'api_supports_by_zone', methods: ['GET'])]
    public function getSupportsByZone(int $zoneId, SupportClientRepository $supportClientRepository): JsonResponse
    {
        $supports = $supportClientRepository->createQueryBuilder('s')
            ->join('s.typeSupport', 'ts')
            ->where('s.zonesClient = :zoneId')
            ->setParameter('zoneId', $zoneId)
            ->orderBy('ts.nom', 'ASC')
            ->getQuery()
            ->getResult();

        $data = array_map(function ($support) {
            return [
                'id' => $support->getId(),
                'nom' => $support->getTypeSupport()->getNom(),
                'type_support_id' => $support->getTypeSupport()->getId(),
            ];
        }, $supports);

        return $this->json($data);
    }
}
