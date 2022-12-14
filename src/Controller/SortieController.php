<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Sortie;
use App\Form\AnnulationSortieType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\InscriptionRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    // Créer une sortie

    #[Route('/sortie/creer', name: 'sortie_creer')]
    public function creer(
        Request $request,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository,
        LieuRepository  $lieuRepository,
        SiteRepository  $siteRepository,
    ): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $user = $this->getUser();
        $sortie = new Sortie();
        $etatCreee = $etatRepository->findOneBy(array('id'=> 1));
        $etatOuvert = $etatRepository->findOneBy(array('id'=> 2));
        $site = $siteRepository->findOneBy(array('id'=>$user->getSite()));

        $sortie->setOrganisateur($user);
        $sortie->setSite($site);

        $inscription = new Inscription();
        $inscription -> setSortie($sortie);
        $inscription->setParticipant($user);
        $inscription->setDateInscription(new \dateTime());
        $sortie->setNombreParticipants(1);

        $lieu = $lieuRepository->findOneBy(array('id'=>$sortie->getLieux()));
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //if ($request->get("sortie")["selectionner"]){
            if ($request->get("enregistrer")){
                $sortie->setEtats($etatCreee);
            } else {
                $sortie->setEtats($etatOuvert);
            }

            $entityManager->persist($inscription);
            $entityManager->persist($sortie);
            $entityManager->flush();
            return $this->redirectToRoute('accueil_index');
        }
        return $this->render('sortie/creer_sortie.html.twig',[
            'form'=>$form->createView(),
            'lieu'=>$lieu
        ]);
    }

    #[Route('/sortie/modifier/{id}', name: 'sortie_modifier', requirements: ['id' => '\d+'])]
    public function modifier(
        Request $request,
        EntityManagerInterface $entityManager,
        SortieRepository $sortieRepository,
        EtatRepository $etatRepository,
        LieuRepository  $lieuRepository,
        Sortie $id
    ): Response
    {
        if(!$this->getUser()){
            $this->redirectToRoute('app_login');
        }
        $user = $this->getUser()->getId();
        $sortie = $sortieRepository->findOneBy(array('id'=>$id));
        $etatSortie = $sortie->getEtats();
        $organisateur = $sortie->getOrganisateur()->getId();
        $etatCreee = $etatRepository->findOneBy(array('id'=> 1));

        if ($user != $organisateur or $etatSortie != $etatCreee) {
            return $this->redirectToRoute('accueil_index');
        }

        $lieu = $lieuRepository->findOneBy(array('id'=>$sortie->getLieux()));
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$request->get("supprimer")) {
                if ($request->get("enregistrer")) {
                    $sortie->setEtats($etatCreee);
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    return $this->redirectToRoute('accueil_index');
                } else {
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    return $this->render('sortie/publier_sortie.html.twig',
                        compact('sortie')
                    );
                }
            }
            return $this->render('sortie/supprimer_sortie.html.twig',
                compact('sortie')
            );
        }
        return $this->render('sortie/modifier_sortie.html.twig',[
            'form'=>$form->createView(),
            'lieu'=>$lieu
        ]);
    }

    #[Route('/sortie/publier/{id}', name: 'sortie_publier', requirements: ['id' => '\d+'])]
    public function publier(
        EntityManagerInterface $entityManager,
        SortieRepository $sortieRepository,
        EtatRepository $etatRepository,
        Sortie $id
    ): Response
    {
        if(!$this->getUser()){
            $this->redirectToRoute('app_login');
        }
        $sortie = $sortieRepository->findOneBy(array('id'=>$id));
        $etatOuvert = $etatRepository->findOneBy(array('id'=> 2));
        $etat = $sortie->setEtats($etatOuvert);
        $this->addFlash('success', 'Publication de la sortie réalisée avec succès!');
        $entityManager->persist($sortie);
        $entityManager->flush();

        return $this->redirectToRoute('accueil_index');
    }

    #[Route('/sortie/supprimer/{id}', name: 'sortie_supprimer', requirements: ['id' => '\d+'])]
    public function supprimer(
        EntityManagerInterface $entityManager,
        SortieRepository $sortieRepository,
        Sortie $id
    ): Response
    {
        if(!$this->getUser()){
            $this->redirectToRoute('app_login');
        }
        $sortie = $sortieRepository->findOneBy(array('id'=>$id));
        $this->addFlash('success', 'Suppression de la sortie réalisée avec succès!');
        $entityManager->remove($sortie);
        $entityManager->flush();

        return $this->redirectToRoute('accueil_index');
    }

    #[Route('/sortie/detail/{id}', name: 'sortie_detail', requirements: ['id' => '\d+'])]
    public function detail(Sortie $id,
                            ParticipantRepository $participantRepository,
                            InscriptionRepository $inscriptionRepository
    ): Response
    {
        if(!$this->getUser()){
            $this->redirectToRoute('app_login');
        }
        $Participant = $participantRepository->findAll();
        $inscription = $inscriptionRepository->findAll();
        return $this->render('sortie/detail.html.twig', [
            "sortie" => $id,
            "participant" => $Participant,
            "inscription" => $inscription
        ]);
    }

    // Annuler une sortie

    #[Route('/sortie/annulee/{id}', name: 'sortie_annulee', requirements: ['id'=>'\d+'])]
    public function SortieAnnulee(
        Sortie $id,
        SortieRepository $SortieRepository,
        EtatRepository $etatRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response
    {
        if(!$this->getUser()){
            $this->redirectToRoute('app_login');
        }
        $user = $this->getUser();
        $sortie = $SortieRepository->find($id);
        $etat = $etatRepository->findOneBy(array('id'=> 6));

        $formMotifAnnulation = $this->createForm(AnnulationSortieType::class,$sortie);
        $formMotifAnnulation->handleRequest($request);

        if ($sortie->getOrganisateur() === $user)
            if ($formMotifAnnulation->isSubmitted() && $formMotifAnnulation->isValid()) {
                $sortie->setEtats($etat);
                $entityManager->persist($sortie);
                $entityManager->flush();
                return $this->redirectToRoute('accueil_index');
            }
        return $this->renderForm('sortie/annulation.html.twig',
            compact('formMotifAnnulation', 'sortie')
        );
    }

    // Afficher sortie annulée

    #[Route('/sortie/annulee/detail/{id}', name: 'sortie_annulee_Detail', requirements: ['id'=>'\d+'])]
    public function SortieAnnuleeDetail(
        Sortie $id,
    ): Response
    {
        if(!$this->getUser()){
            $this->redirectToRoute('app_login');
        }
        return $this->render('sortie/annulee_detail.html.twig', [
            "sortie" => $id,
        ]);
    }
}
