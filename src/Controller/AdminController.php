<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Stock;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(ProductRepository $productRepository, Request $request, EntityManagerInterface $em): Response
    {
        $product = new Product();

        // Pré-remplir le stock par taille (XS à XL)
        foreach (['XS', 'S', 'M', 'L', 'XL'] as $size) {
            $stock = new Stock();
            $stock->setSize($size);
            $stock->setQuantity(0);
            $stock->setProduct($product);
            $product->addStock($stock);
        }

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product->setCreatedAt(new \DateTimeImmutable());

            
            foreach ($product->getStocks() as $stock) {
                $stock->setProduct($product);
            }

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $products = $productRepository->findAll();

        return $this->render('admin/index.html.twig', [
            'form' => $form->createView(),
            'products' => $products
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_product_delete', methods: ['GET'])]
public function delete(int $id, EntityManagerInterface $em): Response
{
    $product = $em->getRepository(\App\Entity\Product::class)->find($id);

    if (!$product) {
        throw $this->createNotFoundException('Produit introuvable.');
    }

    $em->remove($product);
    $em->flush();

    $this->addFlash('success', 'Produit supprimé.');
    return $this->redirectToRoute('admin_dashboard');
}

    #[Route('/product/edit/{id}', name: 'admin_product_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $product = $em->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($product->getStocks() as $stock) {
                $stock->setProduct($product);
            }

            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès.');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product
        ]);
    }
}