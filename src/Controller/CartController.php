<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/panier', name: 'cart_')]
class CartController extends AbstractController 
{
    #[Route('/', name: 'index')]
    public function index(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $panier = $session->get("panier", []);

        // On "fabrique" les données
        $dataPanier = [];
        $total = 0;

        foreach($panier as $id => $quantity) {
            $product = $productRepository->find($id);
            $dataPanier[] = [
                "product" => $product,
                "quantity" => $quantity,
            ];

            $total += $product->getPrice() * $quantity;
        }

        return $this->render('cart/index.html.twig', compact("dataPanier", "total"));
    }

    #[Route('/ajouter/{id}', name: 'add')]
    public function add(Product $product, SessionInterface $session): Response
    {
        // On récupère le panier actuel
        $panier = $session->get("panier", []);
        $id = $product->getId();

        // dans la session, la tableau du panier va se présenter sous cette forme :
        //    [
        //        "produit1" => quantité1,
        //        "produit2" => quantité2
        //    ]
        // $panier[1] = quantité1

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

    #[Route('/retirer/{id}', name: 'remove')]
    public function remove(Product $product, SessionInterface $session): Response
    {
        // On récupère le panier actuel
        $panier = $session->get("panier", []);
        $id = $product->getId();

        if($id) {
            // si l'article existe déjà dans le panier
            if(!empty($panier[$id])) {
                // et qu'il y en a + d'un
                if($panier[$id] > 1) {

                    $panier[$id]--;

                } else {
                    unset($panier[$id]);
                }
            } 
            // On sauvegarde dans la session
            $session->set("panier", $panier);
    
            return $this->redirectToRoute("cart_index");
        }
    }

    #[Route('/supprimer/{id}', name: 'delete')]
    public function delete(Product $product, SessionInterface $session): Response
    {
        // On récupère le panier actuel
        $panier = $session->get("panier", []);
        $id = $product->getId();

        // si l'article existe déjà dans le panier
            if($id && !empty($panier[$id])) {
                unset($panier[$id]);
            } 
            // On sauvegarde dans la session
            $session->set("panier", $panier);
    
            return $this->redirectToRoute("cart_index");
        }

        #[Route('/supprimer', name: 'delete_all')]
        public function deleteAll(SessionInterface $session): Response
        {
            // Si on veut supprimer tous les articles du panier
            //! Pas de session clear sinon on supprime tout, dont le session user
            $session->remove("panier");
        
            return $this->redirectToRoute("cart_index");
        }
}