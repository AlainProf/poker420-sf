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

use App\Util;

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
		
		$jwt= $req->request->get('jwt');
		util::logmsg("in création partie, JWT= $jwt");
		
		if (util::JWTokenEstValide($jwt))
		{
			util::logmsg("le jwt est valide");
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
				util::logmsg("ln 53");
				$em = $doctrine->getManager();
				$p = new Partie();
				
				$p->setDebut($debut);
				$p->setFin(null);
				$p->setIdGagnant(null);
				
				$repoJoueurs = $em->getRepository(Joueur::class);
				$nbJoueurs=0;
				$tabMains =array();
				util::logmsg("ln 64");
					
				for($i=0; $i<10; $i++)
				{
     				util::logmsg("ln 68 $i");

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
				util::logmsg("ln 86 ");
				$em->persist($p);
				$em->flush();
				util::logmsg("ln 89 ");
				
				$partieInfo = $this->PreparerReponse($p);
				util::logmsg("ln 92 ");

				return $this->json($partieInfo);
			}
			else
			{
			util::logmsg("Erreur de jwt");

			   return $this->json("erreur 66");
	
			}
		}
		else
		{
			return $this->json("Erreur de piratage");
		}
    }
	
    //-------------------------------------
	//
    //-------------------------------------
    function PreparerReponse($partie)
	{
		$tabInfo = array();
        $tabInfo['id'] = $partie->getId();
        $tabInfo['debut'] = $partie->getDebut();
        $tabInfo['joueurs'] = array();
		
		$tabJoueurs = $partie->getJoueurs();
		
		//Util::tr("Partie " . $tabInfo['id'] . "\nPre boucle\nNb joueurs:" . count($tabJoueurs));
		for($i=0; $i<count($tabJoueurs); $i++)
		{
			$unJoueur['id'] = $tabJoueurs[$i]->getJoueur()->getId();
			$unJoueur['nom'] = $tabJoueurs[$i]->getJoueur()->getNom();
			$unJoueur['position'] = $tabJoueurs[$i]->getPosition();
			$unJoueur['capital'] = $tabJoueurs[$i]->getCapital();
			$unJoueur['engagement'] = $tabJoueurs[$i]->getEngagement();
            //Util::tr("iter $i");
			$tabInfo['joueurs'][] = $unJoueur;
		}
		return $tabInfo;

	}
	
    //-------------------------------------
	//
    //-------------------------------------
	function infoValides($n, $mdp, $c)
	{
		return true;
	}
	
	//-----------------------------------
	//
	//-----------------------------------
    #[Route('/getPartiesDUnJoueur')]
	public function getPartiesDUnJoueur(Request $req, ManagerRegistry $doctrine, Connection $connexion): JsonResponse
    {
        /* initialisation par le $_POST */
		$joueurID = $req->request->get('idJ');
		//Util::tr("Id du joueur conne: $joueurID");
		
		$repoJoueurs = $doctrine->getManager()->getRepository(Joueur::class);
        $joueur = $repoJoueurs->find($joueurID);

        
		$tabParties = $joueur->getParties();
		
		$reponse = array();
        for($i=0; $i<count($tabParties); $i++)
        {
			$id = $tabParties[$i]->getPartie()->getId();
			$reponse[] = $id * 1;
		}
        return $this->json($reponse);
    }
		
	//-----------------------------------
	//
	//-----------------------------------
    #[Route('/getInfoPartieEnCours')]
	public function getInfoPartieEnCours(Request $req, ManagerRegistry $doctrine, Connection $connexion): JsonResponse
    {
        $idPartie = $req->request->get('idPartie');
		//Util::tr("Id de la partie $idPartie");
		$joueurID = $req->request->get('idJConnecte');
		$repoParties = $doctrine->getManager()->getRepository(Partie::class);
        $partie = $repoParties->find($idPartie);
        //Util::tr("après le find: " . $partie->getId());

        $reponse = $this->PreparerReponse($partie,$joueurID);
		//Util::tr($reponse);
        return $this->json($reponse);
    }	
	
}
