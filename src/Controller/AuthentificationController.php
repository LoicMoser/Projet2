<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
//la commande suivante permet d'appeler l'entité Utilisateur depuis la BDD
use App\Entity\Utilisateur;
use App\Entity\Acces;
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
            return $this->redirectToRoute('home');    
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
    	//les commandes suivantes permet le contrôle de la connexion ou non à un compte utilisateur existant
        $sess = $request->getSession();
        if($sess->get("idUtilisateur")){
            $listeDocuments = $manager->getRepository(Acces::class)->findByUtilisateurId($sess->get("idUtilisateur"));
            $nbDocument = 0;
            foreach ($listeDocuments as $val){
                $nbDocument ++;
            }
            return $this->render('authentification/dashboard.html.twig', [
                'controller_name' => 'Espace client',
                'nbDocument' => $nbDocument,
            ]);
        }else{
            return $this->redirectToRoute('authentification');
        }
    }

    /**
     * @Route("/home", name="home")
     */
    public function home(Request $request, EntityManagerInterface $manager): Response
    {
        $sess = $request->getSession();
        if($sess->get("idUtilisateur")){
            return $this->render('authentification/home.html.twig', [
                'controller_name' => 'Bienvenu dans le lolo drive',
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
        //on récupère les données de la session puis on supprime l'information idUtilisateur et on clear la session.
        $sess = $request->getSession();
        $sess->remove("idUtilisateur");
        $sess->invalidate();
        $sess->clear();
        $sess=$request->getSession()->clear();
        return $this->redirectToRoute('authentification');
    }
}
