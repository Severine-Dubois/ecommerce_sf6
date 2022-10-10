<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/panier', name: 'cart_')]
class CartController extends AbstractController 
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('cart/index.html.twig');
    }

    #[Route('/ajouter/{id}', name: 'add')]
    public function add(Product $product, SessionInterface $session): Response
    {
        // On récupère le panier actuel
        $panier = $session->get("panier", []);
        $id = $product->getId();

        if($id) {
            // si l'article existe déjà dans le panier
            if(!empty($panier[$id])) {
                // alors j'ajoute un à la quantité
                $panier[$id]++;
            } else {
                // sinon je le "créé" avec une quantité de 1
                $panier[$id] = 1;
            }
            // On sauvegarde dans la session
            $session->set("panier", $panier);
    
            return $this->redirectToRoute("cart_index");
        }

    }
}