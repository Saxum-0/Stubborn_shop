<?php

namespace App\Tests\Checkout;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;

class CheckoutTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testSimulatedPurchaseRedirectsToStripe(): void
    {
        // Récupérer un produit de test
        $product = $this->em->getRepository(Product::class)->findOneBy([]);

        $this->assertNotNull($product, 'Aucun produit trouvé en base.');

        // Simuler un ajout au panier
        $this->client->request('GET', '/cart/add/' . $product->getId() . '/M');
        $this->client->followRedirect();

        // Accéder au panier
        $crawler = $this->client->request('GET', '/cart/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Votre panier');

        // Lancer la commande
        $this->client->request('GET', '/cart/checkout');

        // Vérifier la redirection vers Stripe
        $response = $this->client->getResponse();
        $this->assertTrue($response->isRedirection(), 'Pas de redirection détectée.');
        $this->assertStringContainsString('stripe.com', $response->headers->get('Location'));

        echo "✅ Achat simulé avec succès — redirection Stripe OK\n";
    }
}
