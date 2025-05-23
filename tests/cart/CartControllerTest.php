<?php

namespace App\Tests\Cart;

use App\Entity\Product;
use App\Entity\CartItem;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class CartControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testCartAccess(): void
    {
        $this->client->request('GET', '/cart/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Votre panier');
    }

    public function testAddProductToCart(): void
    {
        $product = $this->em->getRepository(Product::class)->findOneBy([]);
        $this->assertNotNull($product, 'Produit manquant en BDD pour le test.');

        $size = 'M'; // ⚠️ taille existante pour ce produit en base

        $this->client->request('GET', "/cart/add/{$product->getId()}/$size");
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', $product->getName());
        $this->assertSelectorTextContains('body', $size);
    }

    public function testCartTotalIsCalculated(): void
    {
        $crawler = $this->client->request('GET', '/cart/');
        $this->assertResponseIsSuccessful();

        $content = $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Total', $content);
    }

    public function testRemoveFromCart(): void
{
    $product = $this->em->getRepository(Product::class)->findOneBy([]);
    $this->assertNotNull($product, 'Produit manquant pour test suppression.');

    // Ajouter une entrée au panier (si nécessaire)
    $this->client->request('GET', '/cart/add/' . $product->getId() . '/M');
    $this->client->followRedirect();

    // Supprimer tous les CartItems liés à ce produit
    $items = $this->em->getRepository(CartItem::class)->findBy(['product' => $product]);
    foreach ($items as $item) {
        $this->client->request('GET', '/cart/remove/' . $item->getId());
        $this->client->followRedirect();
    }

    // Recharger le panier
    $crawler = $this->client->request('GET', '/cart/');
    $content = $this->client->getResponse()->getContent();

    $this->assertStringNotContainsString(
        $product->getName(),
        $content,
        'Le produit supprimé est encore affiché dans le panier.'
    );
}


    }

