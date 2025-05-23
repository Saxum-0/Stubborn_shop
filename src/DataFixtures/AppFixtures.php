<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Stock;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $productsData = [
            ['Blackbelt', 29.90, true],
            ['BlueBelt', 29.90, false],
            ['Street', 34.50, false],
            ['Pokeball', 45.00, true],
            ['PinkLady', 29.90, false],
            ['Snow', 28.50, false],
            ['Greyback', 45.00, false],
            ['BlueCloud', 32.00, false],
            ['BornInUsa', 59.90, true],
            ['GreenSchool', 42.20, false],
        ];

        $sizes = ['XS', 'S', 'M', 'L', 'XL'];

        foreach ($productsData as [$name, $price, $highlighted]) {
            $product = new Product();
            $product->setName($name);
            $product->setPrice($price);
            $product->setHighlighted($highlighted);
            $product->setImage('uploads/products/' . strtolower($name) . '.png');
            $product->setCreatedAt(new \DateTimeImmutable());
            $product->setSlug($this->slugger->slug($name)->lower());

            foreach ($sizes as $size) {
                $stock = new Stock();
                $stock->setSize($size);
                $stock->setQuantity(random_int(2, 10));
                $stock->setProduct($product);
                $manager->persist($stock);
            }

            $manager->persist($product);
        }

        $manager->flush();
    }
}
