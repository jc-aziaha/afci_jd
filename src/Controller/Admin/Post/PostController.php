<?php

namespace App\Controller\Admin\Post;

use App\Entity\Post;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{

    #[Route('/admin/post/list', name: 'admin.post.index', methods:['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();

        return $this->render('pages/admin/post/index.html.twig', [
            "posts" => $posts
        ]);
    }


    #[Route('/admin/post/create', name: 'admin.post.create', methods:['GET','POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        // 1- Créons le nouvel article.
        $post = new Post();

        // 2- Créons le formulaire d'ajout du nouvel article.
        $form = $this->createForm(PostFormType::class, $post);

        // 4- Associons les données de la requête aux données du formulaire.
        $form->handleRequest($request);

        // 5- Si le formulaire est soumis et qu'il est envoyé, 
        if ( $form->isSubmitted() && $form->isValid() )
        {

            // 6- Initialisons le slug, la date de création et de modification
                // Ce traitement se fait dans l'entité Post grâce au paquet "StofDoctrine".

            // 7- Renseignons l'utilisateur dont le rôle est admin qui a rédigé l'article.
            $userAuthenticated = $this->getUser();
            $post->setUser($userAuthenticated);

            // 8- Demandons au gestionnaire des entités de préparer la requête 
            // d'insertion des données du nouvel article dans la table "post" de la base de données.
            $em->persist($post);

            // 9- Demandons-lui d'exécuter la requête.
            $em->flush();

            // 10- Générer le message flash de succès.
            $this->addFlash("success", "L'article a été créé et sauvégardé.");

            // 11- Effectuer une redirection vers la page d'accueil(index) de la section des articles.
            // Puis arrêtons l'exécution du script.
            return $this->redirectToRoute("admin.post.index");
        }

        // 3- Passons la partie visible du formulaire à la page(vue).
        return $this->render("pages/admin/post/create.html.twig", [
            "form" => $form->createView()
        ]);
    }
}
