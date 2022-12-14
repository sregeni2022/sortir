<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Sortie;
use App\Repository\InscriptionRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/accueil', name: 'accueil')]
class AccueilController extends AbstractController
{
    #[Route('/', name: '_index')]
    public function index(SortieRepository $sortieRepository,
                          SiteRepository $siteRepository,
                          InscriptionRepository $inscriptionRepository,
                          ParticipantRepository $participantRepository,
                          Request $request
    ): Response
    {
        if(!$this->getUser()){
            $this->redirectToRoute('app_login');
        }

        $sorties = $sortieRepository->findAll();
        $sites = $siteRepository->findAll();
        $inscription = $inscriptionRepository->findAll();
        $participant = $participantRepository->findAll();

        if($request->request) {

            if ($request->request->get('sortiesOrga')) {
                $organisateur = $this->getUser();
            }else{
                $organisateur = null;
            }
            if($request->request->get('site')){
                $site = $request->request->get('site');
            } else {
                $site = null;
            }
            if($nom = $request->request->get('search')){
                $nom = '%'.$request->request->get('search').'%';
            }
            if($dateDebut = $request->request->get('dateDebut')){
                $dateDebut = $request->request->get('dateDebut');
            }
            if($dateFin = $request->request->get('dateFin')){
                $dateFin = $request->request->get('dateFin');
            }
            if($inscrit = $request->request->get('sortiesInscrit')){
                $inscrit = $this->getUser();
            }
            if($nonInscrit = $request->request->get('sortiesNonInscrit')){
                $nonInscrit = $this->getUser();
            }
            if($sortiesPassees = $request->request->get('sortiesPassees')){
                $sortiesPassees = 5;
            }

            $sortie = $sortieRepository->findByFiltre($organisateur, $site, $inscrit, $nonInscrit, $sortiesPassees, $nom, $dateDebut, $dateFin);

            return $this->render('accueil/index.html.twig',[
                'sorties'=>$sortie,
                "sites"=>$sites,
                "inscription"=>$inscription,
                "participant"=>$participant,
            ]);
        }

        return $this->render('accueil/index.html.twig', [
            "sorties"=>$sorties,
            "sites"=>$sites,
            "inscription"=>$inscription,
            "participant"=>$participant,

        ]);
    }


}
