<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProductRepository;


class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
public function index(ProductRepository $repo): Response
{
    $highlighted = $repo->findBy(['highlighted' => true]);

    return $this->render('home/index.html.twig', [
        'highlighted' => $highlighted
    ]);
}
}
