<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\Persistence\ManagerRegistry;  // Pour l'accès à l'entity manager de Doctrine
use Doctrine\DBAL\Connection;              // Pour avoir accès à l'engin de query

use App\Entity\Joueur;
use App\util;

ini_set('date.timezone', 'America/New_York');
header('Access-Control-Allow-Origin: *');



class ConnexionController extends AbstractController
{
    //-------------------------------------
	//
    //-------------------------------------
    #[Route('/getJoueurs')]
    public function getJoueurs(Request $req, Connection $connexion): JsonResponse
    {
		// initialisation par le GET
		$idJ = $req->query->get('idJ');
		$joueurs = $connexion->FetchAllAssociative("select * from joueur where id != $idJ");
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
                $jwt = util::genereJWToken($retJoueur['id']);
				$retJoueur['jwt'] = $jwt;				
				
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
		
		util::logmsg("in connexion");
				
		
		$joueur = $connexion->FetchAllAssociative("select * from joueur where nom = '$nom'");
		
		if (isset($joueur[0]))
		{
			if ($joueur[0]['motDePasse'] === $mdp)
			{
      			$retJoueur['id'] = $joueur[0]['id'];
				$retJoueur['nom'] = $joueur[0]['nom'];
				$retJoueur['courriel'] = $joueur[0]['courriel'];
				
				$jwt = util::genereJWToken($joueur[0]['id']);
				util::logmsg("JWT: " . $jwt);
				
				$retJoueur['jwt'] = $jwt;
				
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


	//-----------------------------------
	//
	//-----------------------------------
    #[Route('/televerseAvatar')]
    public function televerseAvatar(Request $req, ManagerRegistry $doctrine): JsonResponse
    {
		//$this->setTrace(); 
		//Util::logmsg("route televerseAvatar()");
		$result=array();
		$token = $req->request->get('acces');
		/*if (!Util::JWTokenEstValide($token))
		{
			$result["status"]=0;
            $result["message"]="erreur: le token n et pas valide";
			Util::logmsg("On quitte televerseAvatar");
		    return new Response(json_encode($result));
		}*/
		
        if ($req->getMethod() === 'POST')
        {
		   //Util::logmsg("c'est un post");
		   if ($this->estUnPng())
		   {
			  //Util::logmsg("png on traiteImage()");
              $this->traiterImage($req);
		   }
		   else
		   {
			 //Util::logmsg("mauvaise extension");
             $result["status"]=0;
             $result["message"]="erreur: on accepte seulement les png";
		   }
        }
        else
        {
		   //Util::logmsg("Erreur ce n'est pas un png");
           $result["status"]=0;
           $result["message"]="erreur: requête n'est pas un post";
        }
		//Util::logmsg("On quitte televerseAvatar");
		return $this->json($result);
	}
   //------------------------------------------------------------------
   //
   //------------------------------------------------------------------
   function estUnPng()
   {
	   $nom = $_FILES['file']['name'];
	   
	   $pattern = '/\.png$/i';
	   if (preg_match($pattern, $nom) )
		   return true;
	   return false;
   }
   
   
   //------------------------------------------------------------------
   //
   //------------------------------------------------------------------
   function traiterImage($req)
   {
	global $result;
	global $maBD;
	//Util::logmsg("debut de traiterImage()");
	
	$joueurId = $req->request->get('joueurId');
	//Util::logmsg("joueurId : $joueurId");
   	
	if(!empty($_FILES['file']['name']))
	{
		//Util::logmsg("nom du fichier : " . $_FILES['file']['name']);
		
		if($_FILES['file']['error'] > 0)
		{
			if($_FILES['file']['error'] == 2)
			{
                $result["status"]=0;
                $result["message"]="erreur téléversement trop volumineux";				
			}
			else
			{
				/*Util::logmsg("Erreur : Téléchargement " . 
				          $_FILES['file']['error'] . 
						  " fichier tmp : " .
						  $FILES['file']['tmp_name'] );	*/
                $result["status"]=0;
                $result["message"]="erreur " . $_FILES['file']['error'] ;				
			}
		}
		else
		{
			//Util::logmsg("Tout est beaux");
			
			$chemin = "./images/joueurs/";
			$nomFichier = "$joueurId.png";
			$dest = $chemin . $nomFichier;
			
			if(is_uploaded_file($_FILES['file']['tmp_name']))
			{
				//Util::logmsg("is_uploaded_file est vrai");
				if(move_uploaded_file($_FILES['file']['tmp_name'], $dest))
				{
					/*Util::logmsg("move_uploaded_file est vrai");
					Util::logmsg("Déplacer le fichier téléversé vers: $dest");*/
                    $result["status"]=1;
                    $result["message"]="téléversement réussi.";				
				}
     			else
	     		{
		    	   //Util::logmsg("Problème de transfert");
                   $result["status"]=0;
                   $result["message"]="erreur du téléversement";				
			    }
			}
			else
			{
				//Util::logmsg("is_uploaded_file est faux");
			}
		}
	}
	else
	{
       $result["status"]=0;
       $result["message"]="erreur fichier vide";				
	}
	//Util::logmsg("Quitte traiterImage");
  }
  
    //-----------------------------------------------
    //
    //-----------------------------------------------
    private function setTrace()
    {
		$this->traceOn = false;	
		$TraceOn = $this->getParameter('TRACE');
		
		if ($TraceOn === '1')
            $this->traceOn = true;	
    }





}
