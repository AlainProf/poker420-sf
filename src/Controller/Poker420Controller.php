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
        //dd($membre);
        //$membre = $doctrine->getrepository(Membre:class)->find()

        Util::logmsg($membre[0]['mot_de_passe']);
        if (isset($membre[0]['mot_de_passe']))
        {
            if ($membre[0]['mot_de_passe'] == $motDePasse)
            {
                $retMembre['id'] =  $membre[0]['id'];
                $retMembre['nom'] =  $membre[0]['nom'];
                $retMembre['courriel'] =  $membre[0]['courriel'];
                $retMembre['mot_de_passe'] =  "hahaha";
                $retMembre['choisi'] = false;

                $retMembre['jwt'] = Util::genererJWToken($membre[0]['id']);
         

                return $this->json($retMembre);
            }
        }
        return $this->json("erreur 77");
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
        $jwt = $req->request->get('jwt');

        Util::logmsg($jwt);
        if (Util::JWTokenEstValide($jwt))
        {  

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
      else
      {
        dd("Token invalide");
      }
    }

    
    #[Route('/getInfoPartie')]
    public function getInfoPartie(ManagerRegistry $doctrine, Request $req, Connection $connexion): JsonResponse
    {
        $idP = $req->request->get('idP');
       
        $partie = $doctrine->getRepository(Partie::class)->find($idP);
        $infoP = $this->preparerReponse($partie);
        return $this->json( $infoP);
    }

    
    //-----------------------------------
	//
	//-----------------------------------
    #[Route('/televerseAvatar')]
    public function televerseAvatar(Request $req, ManagerRegistry $doctrine): Response
    {
		//$this->setTrace(); 
		Util::logmsg("route televerseAvatar()");
		$result=array();
		/*$token = $req->request->get('acces');
		if (!Util::JWTokenEstValide($token))
		{
			$result["status"]=0;
            $result["message"]="erreur: le token n et pas valide";
			Util::logmsg("On quitte televerseAvatar");
		    return new Response(json_encode($result));
		}*/
		
        if ($req->getMethod() === 'POST')
        {
		   Util::logmsg("c'est un post");
		   if ($this->estUnPng())
		   {
			  Util::logmsg("png on traiteImage()");
              $this->traiterImage($req);
		   }
		   else
		   {
			 Util::logmsg("mauvaise extension");
             $result["status"]=0;
             $result["message"]="erreur: on accepte seulement les png";
		   }
        }
        else
        {
		   Util::logmsg("Erreur ce n'est pas un png");
           $result["status"]=0;
           $result["message"]="erreur: requête n'est pas un post";
        }
		Util::logmsg("On quitte televerseAvatar");
		return new Response(json_encode($result));
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
	Util::logmsg("debut de traiterImage()");
	
	$joueurId = $req->request->get('joueurId');
	Util::logmsg("joueurId : $joueurId");
   	
	if(!empty($_FILES['file']['name']))
	{
		Util::logmsg("nom du fichier : " . $_FILES['file']['name']);
		
		if($_FILES['file']['error'] > 0)
		{
			if($_FILES['file']['error'] == 2)
			{
                $result["status"]=0;
                $result["message"]="erreur téléversement trop volumineux";				
			}
			else
			{
				Util::logmsg("Erreur : Téléchargement " . 
				          $_FILES['file']['error'] . 
						  " fichier tmp : " .
						  $FILES['file']['tmp_name'] );	
                $result["status"]=0;
                $result["message"]="erreur " . $_FILES['file']['error'] ;				
			}
		}
		else
		{
			Util::logmsg("Tout est beaux");
			
			$chemin = "./images/joueurs/";
			$nomFichier = "joueur$joueurId.png";
			$dest = $chemin . $nomFichier;
			
			if(is_uploaded_file($_FILES['file']['tmp_name']))
			{
				Util::logmsg("is_uploaded_file est vrai");
				if(move_uploaded_file($_FILES['file']['tmp_name'], $dest))
				{
					Util::logmsg("move_uploaded_file est vrai");
					Util::logmsg("Déplacer le fichier téléversé vers: $dest");
                    $result["status"]=1;
                    $result["message"]="téléversement réussi.";				
				}
     			else
	     		{
		    	   Util::logmsg("Problème de transfert");
                   $result["status"]=0;
                   $result["message"]="erreur du téléversement";				
			    }
			}
			else
			{
				Util::logmsg("is_uploaded_file est faux");
			}
		}
	}
	else
	{
       $result["status"]=0;
       $result["message"]="erreur fichier vide";				
	}
	Util::logmsg("Quitte traiterImage");
  }


}
