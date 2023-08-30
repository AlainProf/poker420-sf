<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\Persistence\ManagerRegistry;  // Pour l'accès à l'entity manager de Doctrine
use Doctrine\DBAL\Connection;              // Pour avoir accès à l'engin de query

use App\Entity\Joueur;

ini_set('date.timezone', 'America/New_York');

header('Access-Control-Allow-Origin: *');



class ConnexionController extends AbstractController
{
    //-------------------------------------
	//
    //-------------------------------------
    #[Route('/getJoueurs')]
    public function getJoueurs(Connection $connexion): JsonResponse
    {
		$joueurs = $connexion->FetchAllAssociative('select * from joueur');
        return $this->json($joueurs);
    }

    //-------------------------------------
	//
    //-------------------------------------
    #[Route('/creationJoueur')]
    public function creationCompte(Request $req, ManagerRegistry $doctrine): JsonResponse
    {
		// initialisation par le POST
		$nom = $req->request->get('nom');
		$mdp = $req->request->get('mdp');
		$courriel = $req->request->get('courriel');
		
		if ($this->infoValides($nom,$mdp, $courriel))
		{
			$creation = new \DateTime();
			
			if ($req->getMethod() == 'POST')
			{
				$em = $doctrine->getManager();
				$j = new Joueur();
				$j->setNom($nom);
				$j->setCourriel($courriel);
				$j->setMotDePasse($mdp);
				$j->setCreation($creation);
				$j->setDernierLogin($creation);
				$j->setNbLogin(1);
				
				$em->persist($j);
				$em->flush();
				
				$retJoueur['id'] = $j->getId();
				$retJoueur['nom'] = $j->getNom();
				$retJoueur['courriel'] = $j->getCourriel();
				
				return $this->json($retJoueur);
			}
			else
			{
				return $this->json("erreur 62");
			}
		}
		else{
		   return $this->json("erreur 66");

		}
		
    }
	
    //-------------------------------------
	//
    //-------------------------------------
	function infoValides($n, $mdp, $c)
	{
		return true;
	}


    //-------------------------------------
	//
    //-------------------------------------
    #[Route('/connexion', name: 'app_connexion')]
    public function connexion(Request $req, ManagerRegistry $doctrine, Connection  $connexion): JsonResponse
    {
		// initialisation par le POST
		$nom = $req->request->get('nom');
		$mdp = $req->request->get('mdp');
		
		
		$joueur = $connexion->FetchAllAssociative("select * from joueur where nom = '$nom'");
		
		if (isset($joueur[0]))
		{
			if ($joueur[0]['motDePasse'] === $mdp)
			{
      			$retJoueur['id'] = $joueur[0]['id'];
				$retJoueur['nom'] = $joueur[0]['nom'];
				$retJoueur['courriel'] = $joueur[0]['courriel'];
				return $this->json($retJoueur);
			}
			else{
				return $this->json("erreur 112");
			}
		}
		else
		{
			return $this->json("erreur 117");
		}
    }
}
