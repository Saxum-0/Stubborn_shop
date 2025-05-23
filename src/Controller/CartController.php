<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\CartItem;
use App\Repository\ProductRepository;
use App\Repository\CartItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Service\StripeService;

#[Route('/cart')]
class CartController extends AbstractController
{
    #[Route('/', name: 'cart_index')]
    public function index(CartItemRepository $cartItemRepository): Response
    {
        $items = $cartItemRepository->findAll();

        $total = 0;
        foreach ($items as $item) {
            $total += $item->getProduct()->getPrice() * $item->getQuantity();
        }

        return $this->render('cart/index.html.twig', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    #[Route('/add/{id}/{size}', name: 'cart_add', requirements: ['id' => '\d+'])]
public function add(
    int $id,
    string $size,
    ProductRepository $productRepository,
    EntityManagerInterface $em
): Response {
    $product = $productRepository->find($id);

    if (!$product) {
        throw $this->createNotFoundException('Produit introuvable');
    }

    $item = new CartItem();
    $item->setProduct($product);
    $item->setSize($size);
    $item->setQuantity(1);

    $em->persist($item);
    $em->flush();

    return $this->redirectToRoute('cart_index');
}



    #[Route('/remove/{id}', name: 'cart_remove', requirements: ['id' => '\d+'])]
public function remove(
    int $id,
    CartItemRepository $cartItemRepository,
    EntityManagerInterface $em
): Response {
    $item = $cartItemRepository->find($id);

    if (!$item) {
        throw $this->createNotFoundException('Article introuvable');
    }

    $em->remove($item);
    $em->flush();

    $this->addFlash('success', 'Article supprimé du panier.');
    return $this->redirectToRoute('cart_index');
}
#[Route('/checkout', name: 'cart_checkout')]
public function checkout(
    CartItemRepository $cartItemRepository,
    StripeService $stripeService
): Response {
    $items = $cartItemRepository->findAll();

    $lineItems = [];

    foreach ($items as $item) {
        $lineItems[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $item->getProduct()->getName() . ' (' . $item->getSize() . ')',
                ],
                'unit_amount' => $item->getProduct()->getPrice() * 100,
            ],
            'quantity' => $item->getQuantity(),
        ];
    }

    $session = $stripeService->createCheckoutSession(
        $lineItems,
        'http://localhost:8000/cart/success',
        'http://localhost:8000/cart/cancel'
    );

    return $this->redirect($session->url, 303);
}
#[Route('/success', name: 'cart_success')]
public function success(CartItemRepository $cartItemRepository, EntityManagerInterface $em): Response
{
    $items = $cartItemRepository->findAll();
    foreach ($items as $item) {
        $em->remove($item);
    }
    $em->flush();

    return $this->render('cart/success.html.twig');
}

#[Route('/cancel', name: 'cart_cancel')]
public function cancel(): Response
{
    $this->addFlash('danger', 'Le paiement a été annulé.');
    return $this->redirectToRoute('cart_index');
}
}
