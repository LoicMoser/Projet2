<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Utilisateur;
use Symfony\Component\HttpFoundation\Session\Session;

class AuthentificationController extends AbstractController
{
    /**
     * @Route("/authentification", name="authentification")
     */
    public function index(): Response
    {
        return $this->render('authentification/index.html.twig', [
            'controller_name' => 'Page de connexion',
        ]);
    }

    /**
     * @Route("/connexion", name="connexion")
     */
	public function connexion(Request $request, EntityManagerInterface $manager): Response
   		{
        //Récupération des données du controleur
        $identifiant = $request->request->get('identifiant');
        $password    = $request->request->get('password');
        
        //connexion avec la BD et récupération du couple id/password
        $aUser = $manager->getRepository(Utilisateur::class)->findBy(["Nom"=>$identifiant, "code"=>$password]);
        
        //test de l'existence d'un tel couple
         if (sizeof($aUser)>0){
         	//Récupération de l'utilisateur
         	$utilisateur = new Utilisateur;
         	$utilisateur = $aUser[0];
         	//démarrage d'un session
         	$sess = $request->getSession();
         	//Création de variable de session
         	$sess->set("idUtilisateur", $utilisateur->getId());
         	$sess->set("nomUtilisateur", $utilisateur->getNom());
         	$sess->set("prenomUtilisateur", $utilisateur->getPrenom());
            return $this->redirectToRoute('dashboard');    
        }else{
             return $this->redirectToRoute('authentification');
        }

        dd($reponse);
        return new response(1);
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboard(Request $request, EntityManagerInterface $manager): Response
    {
    	$sess = $request->getSession();
        if($sess->get("idUtilisateur")){
            return $this->render('authentification/dashboard.html.twig', [
                'controller_name' => 'Espace client',
            ]);
        }else{
            return $this->redirectToRoute('authentification');
        }
    }
    /**
     * @Route("/deconnexion", name="deconnexion")
     */
    public function deconnexion(Request $request, EntityManagerInterface $manager): Response
    {
        $sess = $request->getSession();
        $sess->remove("idUtilisateur");
        $sess->invalidate();
        $sess->clear();
        $sess=$request->getSession()->clear();
        return $this->redirectToRoute('authentification');
    }
}
