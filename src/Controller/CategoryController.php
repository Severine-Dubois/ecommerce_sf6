<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/categories', name: 'categories_')]
class CategoryController extends AbstractController 
{
    #[Route('/{slug}', name: 'list')]
    public function details(Category $category): Response
    {
        // On va chercher la liste des produits par catégorie
        $products = $category->getProducts();

        return $this->render('category/list.html.twig', compact('category', 'products'));
    }
}