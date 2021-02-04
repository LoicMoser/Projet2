<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Utilisateur;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'Informations',
        ]);
    }
    /**
     * @Route("/createUser", name="createUser")
     */
    public function createUser(Request $request, EntityManagerInterface $manager): Response
    {
        $User = new Utilisateur();
        $User->setNom($request->request->get('nom'));
        $User->setPrenom($request->request->get('prenom'));
        $User->setCode($request->request->get('code'));
        $User->setSalt($request->request->get('salt'));

        $manager->persist($User);
        $manager->flush();

        return $this->render('user/index.html.twig', [
            'controller_name' => 'Un utilisateur a été ajouté',
        ]);
    }
}
