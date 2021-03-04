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
    	$listeGenre = $manager->getRepository(Genre::class)->findAll();
        return $this->render('ged/uploadGed.html.twig', [
            'controller_name' => 'Upload d un document',
            'listeGenre' => $listeGenre
        ]);
    }

    /**
     * @Route("/insertGed", name="insertGed")
     */
    public function insertGed(Request $request, EntityManagerInterface $manager): Response
    {
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
    }
}
