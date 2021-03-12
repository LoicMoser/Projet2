<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Document;
use App\Entity\Genre;
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
        	$listeGenre = $manager->getRepository(Genre::class)->findAll();
            return $this->render('ged/uploadGed.html.twig', [
                'controller_name' => 'Upload d un document',
                'listeGenre' => $listeGenre
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
        		$newFilename = uniqid('', true) . "." .
        		$brochureFile->getClientOriginalExtension();
        		$brochureFile->move($this->getParameter('upload'), $newFilename);
        		if($request->request->get('choix')=="on"){
        			$actif=1;
        		}else{
        			$actif=-1;
        		}
       			$Document->setActif($actif);
           		$Document->setTypeId($manager->getRepository(Genre::class)->findOneById($request->request->get('genre')));
            	$Document->setCreatedAt(new \Datetime);
            	$Document->setChemin($newFilename);
            	$Document->setNom($request->request->get('nom'));

            	$manager->persist($Document);
            	$manager->flush();
            }

       
        	$listeGenre = $manager->getRepository(Genre::class)->findAll();
            return $this->render('ged/uploadGed.html.twig', [
                'controller_name' => 'Upload d un document',
                'listeGenre' => $listeGenre
            ]);
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
            //Requête pour récupérer toute la table Document
            $listeGed = $manager->getRepository(Document::class)->findAll();
            return $this->render('ged/listeGed.html.twig', [
                'controller_name' => 'Liste des documents',
                'listeGed' => $listeGed,
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
            //Suppression de l'objet qui a l'id passé en paramètre
            $manager->getRepository(Document::class)->findOneById($id);
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
                $ged->setChemin($request->request->get('chemin'));
                rename(getChemin(), get('chemin'));
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
}
