<?php

namespace App\Controller\Admin\Category;

use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{

    
    #[Route('/admin/category/list', name: 'admin.category.index', methods:['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('pages/admin/category/index.html.twig', [
            "categories" => $categories
        ]);
    }


    #[Route('/admin/category/create', name: 'admin.category.create', methods:['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
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
            // Préparons la requête d'insertion de la nouvelle catégorie dans la table "catégory".
            $em->persist($category);

            // Exécutons la requête.
            $em->flush();

            // Générer le message flash de succès
            $this->addFlash("success", "La catégorie '" . $category->getName() . "' a été créée.");

            // Effectuons une redirection vers la route menant à la page d'accueil de la section des catégories
            return $this->redirectToRoute('admin.category.index');
        }

        // Passons la partie visible de ce formulaire à la vue afin de l'afficher 
        return $this->render("pages/admin/category/create.html.twig", [
            "form" => $form->createView()
        ]);
    }


    #[Route('/admin/category/{id}/edit', name: 'admin.category.edit', methods:['GET', 'POST'])]
    public function edit(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CategoryFormType::class, $category);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) 
        {
            $em->persist($category);

            $em->flush();

            // Générer le message flash de succès
            $this->addFlash("success", "La catégorie '" . $category->getName() . "' a été modifiée.");

            // Effectuons une redirection vers la route menant à la page d'accueil de la section des catégories
            return $this->redirectToRoute('admin.category.index');
        }

        return $this->render("pages/admin/category/edit.html.twig", [
            "form" => $form->createView()
        ]);
    }

    #[Route('/admin/category/{id}/delete', name: 'admin.category.delete', methods:['POST'])]
    public function delete(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        if ( $this->isCsrfTokenValid('delete-category'.$category->getId(), $request->request->get('csrf_token')) ) 
        {
            $em->remove($category);

            $em->flush();

            $this->addFlash("success", "La catégorie '" . $category->getName() . "' a été supprimée.");

        }
        
        return $this->redirectToRoute('admin.category.index');
    }

}
