<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Genre;
use Symfony\Component\HttpFoundation\Session\Session;

class GenreController extends AbstractController
{
    /**
     * @Route("/insertGenre", name="insertGenre")
     */
    public function insertGenre(Request $request, EntityManagerInterface $manager): Response
    {
        $Genre = new Genre();
        $Genre->setType($request->request->get('genre'));


        $manager->persist($Genre);
        $manager->flush();

        return $this->render('genre/index.html.twig', [
            'controller_name' => 'Votre document a été envoyé',
        ]);
    }
    /**
     * @Route("/formGenre", name="formGenre")
     */
    public function formGenre(Request $request, EntityManagerInterface $manager): Response
    {
        
        return $this->render('genre/index.html.twig', [
            'controller_name' => 'Formulaire',
        ]);
    }
    /**
     * @Route("/listeGenre", name="listeGenre")
     */
    public function listeGenre(Request $request, EntityManagerInterface $manager): Response
    {
        //Requête pour récupérer toute la table genre
        $listeGenre = $manager->getRepository(Genre::class)->findAll();
        return $this->render('genre/listeGenre.html.twig', [
            'controller_name' => 'Liste des genres',
            'listeGenre' => $listeGenre,
        ]);
    }
    /**
     * @Route("/deleteGenre/{id}", name="deleteGenre")
     */
    public function deleteGenre(Request $request, EntityManagerInterface $manager, Genre $id): Response
    {
        //Suppression de l'objet qui a l'id passé en paramètre
        $manager->remove($id);
        $manager->flush();
        return $this->redirectToRoute('listeGenre');
    }

    /**
     * @Route("/updateGenre/{id}", name="updateGenre")
     */
    public function updateGenre(Request $request, EntityManagerInterface $manager, Genre $id): Response
    {
    
        $sess = $request->getSession();
        //Création de variable de session
        $sess->set("idGenreModif", $id->getId());
        return $this->render('genre/updateGenre.html.twig', [
            'controller_name' => 'Mise à jour d un genre',
            'genre'=>$id
        ]);
    }

    /**
     * @Route("/updateGenreBdd", name="updateGenreBdd")
     */
    public function updateGenreBdd(Request $request, EntityManagerInterface $manager): Response
    {

        $sess = $request->getSession();

        $id=$sess->get("idGenreModif");
        $genre=$manager->getRepository(Genre::class)->findOneById($id);
        $genre->setType($request->request->get('nom'));
        $manager->persist($genre);
        $manager->flush();
        return $this->redirectToRoute('listeGenre');
    }
}