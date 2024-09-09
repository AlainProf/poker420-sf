<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, Request, JsonResponse};
use Symfony\Component\Routing\Attribute\Route;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;

use App\Entity\Membre;
use App\Entity\Partie;
use App\Entity\JoueurPartie;
use App\Util;

header('Access-Control-Allow-Origin: *');
ini_set('date.timezone', 'america/new_york');

class Poker420Controller extends AbstractController
{
    #[Route('/poker420', name: 'app_poker420')]
    public function index(): Response
    {
        return $this->render('poker420/index.html.twig', [
            'controller_name' => 'Poker420Controller',
        ]);
    }

    #[Route('/creationMembre', name: 'creationMembre')]
    public function creationMembre(ManagerRegistry $doctrine, Request $req): Response
    {
        if ($req->request->get('nom') !== null)
        {
           $membre = new Membre();
           $membre->setNom($req->request->get('nom'));
           $membre->setCourriel($req->request->get('courriel'));
           $membre->setMotDePasse($req->request->get('mot_de_passe'));

           $em = $doctrine->getManager();
           $em->persist($membre);
           $em->flush();
           return new Response($membre->getId());
        }
        else
        {
           return new Response(0);  
        }
    }

    #[Route('/connexion', name: 'connexion')]
    public function connexion(ManagerRegistry $doctrine, Request $req, Connection $connexion): JsonResponse
    {
        $nom = $req->request->get('nom');
        $motDePasse = $req->request->get('mot_de_passe');

        Util::logmsg("login info: $nom $motDePasse");
        
        $membre = $connexion->FetchAllAssociative("select * from membre where nom = '$nom'");

        if (isset($membre[0]))
        {
            if ($membre[0]['mot_de_passe'] == $motDePasse)
            {
                $retMembre['id'] =  $membre[0]['id'];
                $retMembre['nom'] =  $membre[0]['nom'];
                $retMembre['courriel'] =  $membre[0]['courriel'];
                $retMembre['mot_de_passe'] =  "hahaha";
                $retMembre['choisi'] = false;
                

                return $this->json($retMembre);
            }
        }
        return $this->json("");
    }

    
    #[Route('/getTousLesMembres')]
    public function getTousLesMembres(ManagerRegistry $doctrine, Request $req, Connection $connexion): JsonResponse
    {
       $membres = $connexion->fetchAllAssociative("select * from membre order by nom");
       //dd($membres);
       return $this->json($membres);
    }

    
    #[Route('/creationPartie')]
    public function creationPartie(ManagerRegistry $doctrine, Request $req, Connection $connexion): JsonResponse
    {
        $tabIdJ[] = $req->request->get('idJ0');
        $tabIdJ[] = $req->request->get('idJ1');
        $tabIdJ[] = $req->request->get('idJ2');
        $tabIdJ[] = $req->request->get('idJ3');
        $tabIdJ[] = $req->request->get('idJ4');
        $tabIdJ[] = $req->request->get('idJ5');
        $tabIdJ[] = $req->request->get('idJ6');
        $tabIdJ[] = $req->request->get('idJ7');
        $tabIdJ[] = $req->request->get('idJ8');
        $tabIdJ[] = $req->request->get('idJ9');

        $debut = new \DateTime();
        if ($req->getMethod() =='POST')
        {
            $em = $doctrine->getManager();
            $p = new Partie;

            $p->setDebut($debut);
            $p->setFin(null);
            $p->setIdGagnant(null);

            $repoMembres = $em->getRepository(Membre::class);
            $nbJoueurs = 0;

            for($i=0; $i < 10; $i++)
            {
                if (!empty($tabIdJ[$i]))
                {
                    $membreAInserer = $repoMembres->find($tabIdJ[$i]);

                    $jp = new JoueurPartie;
                    $jp->setMembre($membreAInserer);
                    $jp->setPosition($nbJoueurs);
                    $jp->setCapital(100);
                    $jp->setEngagement(0);

                    $em->persist($jp);
                    $p->addJoueur($jp);
                    $nbJoueurs++;
                }
            }
            $em->persist($p);
            $em->flush();

            $partieInfo = $this->PreparerReponse($p);
            return $this->json($partieInfo);
        }
        else
        {
            die("Erreur 135");
        }
     
       return $this->json();
    }

    function PreparerReponse($partie)
    {
        $tabInfo = array();
        $tabInfo['id'] = $partie->getId();
        $tabInfo['debut'] = $partie->getDebut();
        $tabInfo['joueurs'] = [];

        $tabJoueurs = $partie->getJoueurs();

        for($i=0; $i< count($tabJoueurs); $i++)
        {
            $unJoueur['id'] = $tabJoueurs[$i]->getMembre()->getId();
            $unJoueur['nom'] = $tabJoueurs[$i]->getMembre()->getNom();
            $unJoueur['position'] = $tabJoueurs[$i]->getPosition();
            $unJoueur['capital'] = $tabJoueurs[$i]->getCapital();
            $unJoueur['engagement'] = $tabJoueurs[$i]->getEngagement();
            $tabInfo['joueurs'][]= $unJoueur;
        }
        return $tabInfo;
    }

    
    #[Route('/getPartiesDUnMembre')]
    public function getPartiesDUnMembre(ManagerRegistry $doctrine, Request $req, Connection $connexion): JsonResponse
    {
        $idJ = $req->request->get('idj');
        $tabIdParties = [];

        $membre = $doctrine->getRepository(Membre::class)->find($idJ);
        $parties = $membre->getParties();

        foreach($parties as $jp)
        {
            $idp = $jp->getPartie()->getId();
            $tabIdParties[] = $idp;
        }
        return $this->json( $tabIdParties);
    }

}
