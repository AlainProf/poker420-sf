<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, Request};
use Symfony\Component\Routing\Attribute\Route;

use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Membre;

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
        
        $membre = new Membre();
        $membre->setNom($req->request->get('nom'));
        $membre->setCourriel($req->request->get('courriel'));
        $membre->setMotDePasse($req->request->get('motDePasse'));

        $em = $doctrine->getManager();
        $em->persist($membre);
        $em->flush();


        return new Response($membre->getId());
    }


}
