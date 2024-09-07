<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, Request, JsonResponse};
use Symfony\Component\Routing\Attribute\Route;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;

use App\Entity\Membre;
use App\Util;

header('Access-Control-Allow-Origin: *');

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

}
