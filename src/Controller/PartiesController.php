<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\Persistence\ManagerRegistry;  // Pour l'accès à l'entity manager de Doctrine
use Doctrine\DBAL\Connection;              // Pour avoir accès à l'engin de query

use App\Entity\Partie;
use App\Entity\JoueurPartie;
use App\Entity\Joueur;

ini_set('date.timezone', 'America/New_York');

header('Access-Control-Allow-Origin: *');

class PartiesController extends AbstractController
{
    //-------------------------------------
	//
    //-------------------------------------
    #[Route('/creationPartie')]
    public function creationPartie(Request $req, ManagerRegistry $doctrine): JsonResponse
    {
		// initialisation par le POST
		$tabJ[]= $req->request->get('idJ0');
		$tabJ[]= $req->request->get('idJ1');
		$tabJ[]= $req->request->get('idJ2');
		$tabJ[]= $req->request->get('idJ3');
		$tabJ[]= $req->request->get('idJ4');
		$tabJ[]= $req->request->get('idJ5');
		$tabJ[]= $req->request->get('idJ6');
		$tabJ[]= $req->request->get('idJ7');
		$tabJ[]= $req->request->get('idJ8');
		$tabJ[]= $req->request->get('idJ9');
		
		$debut = new \DateTime();
     	if ($req->getMethod() == 'POST')
		{
			$em = $doctrine->getManager();
			$p = new Partie();
			
			$p->setDebut($debut);
			$p->setFin(null);
			$p->setIdGagnant(null);
			
			$repoJoueurs = $em->getRepository(Joueur::class);
			$nbJoueurs=0;
			$tabMains =array();
			
			for($i=0; $i<10; $i++)
			{
				if (!empty($tabJ[$i]))
				{
					$joueurAInserer = $repoJoueurs->find($tabJ[$i]);
					
					$jp = new JoueurPartie();
					$jp->setJoueur($joueurAInserer);
					$jp->setPosition($i);
					$jp->setCapital(100);
					$jp->setEngagement(0);
					
					$em->persist($jp);
					$p->addJoueur($jp);
					$nbJoueurs++;
				}
					
			}
			$em->persist($p);
			$em->flush();
			return $this->json("OK $nbJoueurs joueurs");
		}
		else
		{
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
}
