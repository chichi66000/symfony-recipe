<?php

namespace App\DataFixtures;

use Faker\Factory;
use Faker\Generator;
use App\Entity\User;
use App\Entity\Recipe;
use App\Entity\Ingredient;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;


/**
 * Add fake info to database
 */
class AppFixtures extends Fixture
{
    /***
     * @var Generator Faker
     */
    private Generator $faker;

    public function __construct() {
        $this->faker = Factory::create('fr-FR');
    }
    public function load(ObjectManager $manager): void
    {
        // Ingredients
        $ingredients = [];
        for ($i = 1; $i <= 50; $i++) {
            $ingredient = new Ingredient();
            $ingredient
                ->setName($this->faker->word())
                ->setPrice(mt_rand(0, 100));
            $ingredients[] = $ingredient;
            $manager->persist($ingredient);
        }
        
        // Recipe
        for($j = 0; $j < 25; $j++) {
            $recipe = new Recipe();
            $recipe
                ->setName($this->faker->word())
                ->setTime(mt_rand(0,1) == 1 ? mt_rand(1,1440) : null)
                ->setNbPeople(mt_rand(0,1) == 1 ? mt_rand(1,50) : null)
                ->setDifficulty(mt_rand(0,1) == 1 ? mt_rand(1,5) : null)
                ->setDescription($this->faker->text(300))
                ->setPrice(mt_rand(0,1) == 1 ? mt_rand(1,1000) : null)
                ->setIsFavorite(mt_rand(0,1) == 1 ? true: false);
            
            // add an random ingredient in the array of ingredient
            for ($k = 0; $k < mt_rand(5, 15); $k++) {
                $recipe->addIngredient($ingredients[mt_rand(0, count($ingredients) - 1)]);
            }
            $manager->persist($recipe);
        }

        // add fake user
        for ($k = 0; $k < 10; $k++) {
            $user = new User();
            $user
                ->setFullName($this->faker->name())
                ->setPseudo(mt_rand(0, 1) == 1 ? $this->faker->firstName() : null)
                ->setEmail($this->faker->email())
                ->setRoles(['ROLE_USER'])
                ->setPlainPassword('password');

            
            $manager->persist($user);
        }
        $manager->flush();
    }
}
