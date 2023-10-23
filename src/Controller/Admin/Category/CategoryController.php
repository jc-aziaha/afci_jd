<?php

namespace App\Controller\Admin\Category;

use App\Entity\Category;
use App\Form\CategoryFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    #[Route('/admin/category/list', name: 'admin.category.index', methods:['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/category/index.html.twig');
    }


    #[Route('/admin/category/create', name: 'admin.category.create', methods:['GET', 'POST'])]
    public function create(Request $request): Response
    {
        // Créons la nouvelle instance de la catégorie a insérer dans la base de données.
        $category = new Category();
        
        // Créons le formulaire permettant de récupérer le nom de la catégorie.
        $form = $this->createForm(CategoryFormType::class, $category);

        // Associons les données de la requête au formulaire.
        $form->handleRequest($request);

        // Si le formulaire est soumis et qu'il est valide,
        if ( $form->isSubmitted() && $form->isValid() ) 
        {
            
        }

        // Passons la partie visible de ce formulaire à la vue afin de l'afficher 
        return $this->render("pages/admin/category/create.html.twig", [
            "form" => $form->createView()
        ]);
    }
}
