<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Utilisateur;
use App\Entity\Genre;
use App\Entity\Document;
use Symfony\Component\HttpFoundation\Session\Session;

class GenreController extends AbstractController
{
    /**
     * @Route("/insertGenre", name="insertGenre")
     */
    public function insertGenre(Request $request, EntityManagerInterface $manager): Response
    {
        $sess = $request->getSession();
        if($sess->get("idUtilisateur")){
            $Genre = new Genre();
            $Genre->setType($request->request->get('genre'));


            $manager->persist($Genre);
            $manager->flush();

            return $this->render('genre/index.html.twig', [
                'controller_name' => 'Votre document a été envoyé',
            ]);
        }else{
            return $this->redirectToRoute('authentification');
        }
    }
    /**
     * @Route("/formGenre", name="formGenre")
     */
    public function formGenre(Request $request, EntityManagerInterface $manager): Response
    {
        $sess = $request->getSession();
        if($sess->get("idUtilisateur")){
            return $this->render('genre/index.html.twig', [
                'controller_name' => 'Formulaire',
            ]);
        }else{
            return $this->redirectToRoute('authentification');
        }
    }
    /**
     * @Route("/listeGenre", name="listeGenre")
     */
    public function listeGenre(Request $request, EntityManagerInterface $manager): Response
    {
        $sess = $request->getSession();
        if($sess->get("idUtilisateur")){
            //Requête pour récupérer toute la table genre
            $listeGenre = $manager->getRepository(Genre::class)->findAll();
            return $this->render('genre/listeGenre.html.twig', [
                'controller_name' => 'Liste des genres',
                'listeGenre' => $listeGenre,
            ]);
        }else{
            return $this->redirectToRoute('authentification');
        }
    }
    /**
     * @Route("/deleteGenre/{id}", name="deleteGenre")
     */
    public function deleteGenre(Request $request, EntityManagerInterface $manager, Genre $id): Response
    {
        $sess = $request->getSession();
        if($sess->get("idUtilisateur")){
            $testGenre = $manager->getRepository(Document::class)->findByTypeId($id->getId());
            if($testGenre){
                $this->addFlash(
                'notice',
                'Ce genre ne peut pas être supprimé car il est attribué à un ou plusieurs document'
                );
            }else{
                $manager->remove($id);
                $manager->flush();
            }
            return $this->redirectToRoute('listeGenre');
        }else{
            return $this->redirectToRoute('authentification');
        }
    }

    /**
     * @Route("/updateGenre/{id}", name="updateGenre")
     */
    public function updateGenre(Request $request, EntityManagerInterface $manager, Genre $id): Response
    {
        $sess = $request->getSession();
        if($sess->get("idUtilisateur")){
            $sess = $request->getSession();
            //Création de variable de session
            $sess->set("idGenreModif", $id->getId());
            return $this->render('genre/updateGenre.html.twig', [
                'controller_name' => 'Mise à jour d un genre',
                'genre'=>$id
            ]);
        }else{
            return $this->redirectToRoute('authentification');
        }
    }

    /**
     * @Route("/updateGenreBdd", name="updateGenreBdd")
     */
    public function updateGenreBdd(Request $request, EntityManagerInterface $manager): Response
    {
        $sess = $request->getSession();
        if($sess->get("idUtilisateur")){
            $sess = $request->getSession();

            $id=$sess->get("idGenreModif");
            $genre=$manager->getRepository(Genre::class)->findOneById($id);
             if(!empty($request->request->get('nom')))
                $genre->setType($request->request->get('nom'));
            $manager->persist($genre);
            $manager->flush();
            return $this->redirectToRoute('listeGenre');
        }else{
            return $this->redirectToRoute('authentification');     
        }
    }
}