<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Document;
use App\Entity\Genre;
use App\Entity\Autorisation;
use App\Entity\Utilisateur;
use App\Entity\Acces;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use DateTime;

class GedController extends AbstractController
{
    /**
     * @Route("/uploadGed", name="uploadGed")
     */
    public function uploadGed(Request $request, EntityManagerInterface $manager): Response
    {
        $sess = $request->getSession();
        if($sess->get("idUtilisateur")){
            return $this->render('ged/uploadGed.html.twig', [
                'controller_name' => 'Upload d un document',
                'listeGenre' => $manager->getRepository(Genre::class)->findAll(),
                'listeAutorisation' => $manager->getRepository(Autorisation::class)->findAll(),
                'listeUsers' => $manager->getRepository(Utilisateur::class)->findAll(),

            ]);
        }else{
            return $this->redirectToRoute('authentification');
        }
    }

    /**
     * @Route("/insertGed", name="insertGed")
     */
    public function insertGed(Request $request, EntityManagerInterface $manager): Response
    {
        $sess = $request->getSession();
        if($sess->get("idUtilisateur")){
        	$Document = new Document();
        	//recupération et transfert du fichier
        	$brochureFile = $request->files->get("fichier");
        	if ($brochureFile){
                //création d'un nom de fichier unique
        		$newFilename = uniqid('', true) . "." .
        		$brochureFile->getClientOriginalExtension();
        		$brochureFile->move($this->getParameter('upload'), $newFilename);
        		if($request->request->get('choix')=="on"){
        			$actif=1;
        		}else{
        			$actif=-1;
        		}
                //On set les propriétés de l'entité selon les informations que l'on rentre 
                $Document->setActif($actif);
                $Document->setNom($request->request->get('nom'));
                $Document->setTypeId($manager->getRepository(Genre::class)->findOneById($request->request->get('genre')));
                $Document->setCreatedAt(new \Datetime); 
                $Document->setChemin($newFilename); 
            
                $manager->persist($Document);
                $manager->flush();
            }
            if($request->request->get('utilisateur') != -1){
                $user = $manager->getRepository(Utilisateur::class)->findOneById($request->request->get('utilisateur'));
                $autorisation = $manager->getRepository(Autorisation::class)->findOneById($request->request->get('autorisation'));
                $acces = new Acces();
                $acces->setUtilisateurId($user);
                $acces->setAutorisationId($autorisation);
                $acces->setDocumentId($Document);
                $manager->persist($acces);
                $manager->flush();  
            }
            //Création d'un accès pour l'uploadeur (propriétaire)
            $user = $manager->getRepository(Utilisateur::class)->findOneById($sess->get("idUtilisateur"));
            $autorisation = $manager->getRepository(Autorisation::class)->findOneById(1);
            $acces = new Acces();
            $acces->setUtilisateurId($user);
            $acces->setAutorisationId($autorisation);
            $acces->setDocumentId($Document);
            $manager->persist($acces);
            $manager->flush();  
        
        return $this->redirectToRoute('listeGed');
        }else{
            return $this->redirectToRoute('authentification');  
        }
    }

    /**
     * @Route("/listeGed", name="listeGed")
     */
    public function listeGed(Request $request, EntityManagerInterface $manager): Response
    {
        $sess = $request->getSession();
        if($sess->get("idUtilisateur")){
            $user = $manager->getRepository(Utilisateur::class)->findOneById($sess->get("idUtilisateur"));
            $listeAcces = $manager->getRepository(Acces::class)->findByUtilisateurId($user);
            $listeUsers = $manager->getRepository(Utilisateur::class)->findAll();
            $listeAutorisations = $manager->getRepository(Autorisation::class)->findAll();
            return $this->render('ged/listeGed.html.twig', [
                'controller_name' => 'Liste des documents',
                'listeAcces' => $listeAcces,
                'listeUsers' => $listeUsers,
                'listeAutorisations' => $listeAutorisations
            ]);
        }else{
            return $this->redirectToRoute('authentification');
        }
    }

    /**
     * @Route("/deleteGed/{id}", name="deleteGed")
     */
    public function deleteGed(Request $request, EntityManagerInterface $manager, Document $id): Response
    {
        $sess = $request->getSession();
        if($sess->get("idUtilisateur")){
            //supprimer le lien avec l'acces
            foreach($manager->getRepository(Acces::class)->findByDocumentId($id) as $doc){
                $manager->remove($doc);
                $manager->flush();
            }
            //suppression physique d'un document
            if(unlink($this->getParameter('upload') . $id->getChemin())){
                //suppression dans la base de donnée 
                $manager->remove($id);
                $manager->flush();
                $this->addFlash(
                    'true',
                    'Le document à été supprimé'
                    );
                return $this->redirectToRoute('listeGed');
            }else{
                $this->addFlash(
                    'false',
                    'Ce document ne peut pas être supprimé'
                    );
                return $this->redirectToRoute('listeGed');
            }
        }else{
            return $this->redirectToRoute('authentification');
        }
    }

    /**
     * @Route("/updateGed/{id}", name="updateGed")
     */
    public function updateGed(Request $request, EntityManagerInterface $manager, Document $id): Response
    {
        $sess = $request->getSession();
        if($sess->get("idUtilisateur")){
            $sess = $request->getSession();
            //Création de variable de session
            $sess->set("idGedModif", $id->getId());
            return $this->render('ged/updateGed.html.twig', [
                'controller_name' => 'Mise à jour d un document',
                'ged'=>$id
            ]);
        }else{
            return $this->redirectToRoute('authentification');
        }
    }

    /**
     * @Route("/updateGedBdd", name="updateGedBdd")
     */
    public function updateGedBdd(Request $request, EntityManagerInterface $manager): Response
    {
        $sess = $request->getSession();
        if($sess->get("idUtilisateur")){
            $sess = $request->getSession();

            $id=$sess->get("idGedModif");
            $ged=$manager->getRepository(Document::class)->findOneById($id);
            if(!empty($request->request->get('chemin')))
                rename($request->request->get('chemin'), setChemin($request->request->get('chemin')));
                $ged->setChemin($request->request->get('chemin'));
             if(!empty($request->request->get('nom')))
                $ged->setNom($request->request->get('nom'));
            if(!empty($request->request->get('actif')))
                $ged->setActif($request->request->get('actif'));
            $manager->persist($ged);
            $manager->flush();
            return $this->redirectToRoute('listeGed');
        }else{
            return $this->redirectToRoute('authentification');
        }
    }

    /**
     * @Route("/downloadGed/{id}", name="downloadGed")
     */
    public function downloadGed(Request $request, EntityManagerInterface $manager, Document $id): Response
    {
        $sess = $request->getSession();
        if($sess->get("idUtilisateur")){
            
            return $this->redirectToRoute('listeGed');
        }else{
            return $this->redirectToRoute('authentification');
        }
    }

    /**
     * @Route("/permission", name="permission")
     */
    public function permission(Request $request, EntityManagerInterface $manager, Document $id): Response
    {
        $sess = $request->getSession();
        if($sess->get("idUtilisateur")){
            $listeGed = $manager->getRepository(Document::class)->findAll();
            $listeUser = $manager->getRepository(Utilisateur::class)->findAll();
            return $this->render('ged/permission.html.twig', [
                'controller_name' => 'Attribution d un permission',
                'listeDocument' => $listeDocument,
                'listeUser' => $listeUser,
            ]);
        }else{
            return $this->redirectToRoute('authentification');
        }
    }

    /**
     * @Route("/partageGed", name="partageGed")
     */
    public function partageGed(Request $request, EntityManagerInterface $manager): Response
    {
        $sess = $request->getSession();
        if($sess->get("idUtilisateur")){
        //Requête le user en focntion du formulaire
            $user = $manager->getRepository(Utilisateur::class)->findOneById($request->request->get('utilisateur'));
            $autorisation = $manager->getRepository(Autorisation::class)->findOneById($request->request->get('autorisation'));
            $document = $manager->getRepository(Document::class)->findOneById($request->request->get('doc'));
            $acces = new Acces();
            $acces->setUtilisateurId($user);
            $acces->setAutorisationId($autorisation);
            $acces->setDocumentId($document);
            $manager->persist($acces);
            $manager->flush();

        return $this->redirectToRoute('listeGed');
        }else{
            return $this->redirectToRoute('authentification');
        }
    }
}


