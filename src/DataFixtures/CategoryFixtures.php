<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryFixtures extends Fixture
{

    public function __construct(private SluggerInterface $slugger)
    {
        // pas obligatoire d'utiliser le $this avec PHP 8, il faut préciser
        // la visibilité de la classe dans les paramètres de la fonction
    }

    public function load(ObjectManager $manager): void
    {
        $parent = $this->createCategory('Informatique', null, $manager);

        $this->createCategory('Ecran', $parent, $manager);
        $this->createCategory('Ordinateur', $parent, $manager);

        $parent = $this->createCategory('Mode', null, $manager);

        $this->createCategory('Homme', $parent, $manager);
        $this->createCategory('Femme', $parent, $manager);
        $this->createCategory('Enfant', $parent, $manager);

        $manager->flush();

    }

    public function createCategory(string $name, Category $parent = null, ObjectManager $manager) {

        $category = new Category();
        $category->setName($name);
        $category->setSlug($this->slugger->slug($category->getName())->lower());
        $category->setParent($parent);
        $manager->persist($category);

        return $category;
    }
}

