<?php
// src/DataFixtures/AppFixtures.php

namespace App\DataFixtures;

use App\Entity\Country;
use App\Entity\Ingredient;
use App\Entity\User;
use App\Entity\NutritionProfile;
use App\Entity\Recipe;
use App\Entity\RecipeIngredient;
use App\Entity\Menu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;
    private $countries = [];
    private $ingredients = [];
    private $users = [];
    private $recipes = [];

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->createCountries($manager);
        $this->createIngredients($manager);
        $this->createUsers($manager);

        // Flush to get IDs
        $manager->flush();

        // Now create recipes (need existing ingredients and countries)
        $this->createRecipes($manager);

        // Now create menus (need existing recipes and users)
        $this->createMenus($manager);

        $manager->flush();
    }

    private function createCountries(ObjectManager $manager): void
    {
        // North Africa
        $countries = [
            ['name' => 'Morocco', 'flag' => '🇲🇦', 'region' => 'North Africa', 'description' => 'Rich with spices, tagines, and couscous', 'cuisineImage' => 'morocco-cuisine.jpg'],
            ['name' => 'Tunisia', 'flag' => '🇹🇳', 'region' => 'North Africa', 'description' => 'Known for harissa, brik, and seafood dishes', 'cuisineImage' => 'tunisia-cuisine.jpg'],
            ['name' => 'Egypt', 'flag' => '🇪🇬', 'region' => 'North Africa', 'description' => 'Ancient cuisine with ful medames, koshari, and mezze', 'cuisineImage' => 'egypt-cuisine.jpg'],
            ['name' => 'Algeria', 'flag' => '🇩🇿', 'region' => 'North Africa', 'description' => 'Blend of Berber, Arabic, and French influences', 'cuisineImage' => 'algeria-cuisine.jpg'],

            // Europe
            ['name' => 'Italy', 'flag' => '🇮🇹', 'region' => 'Europe', 'description' => 'Home of pasta, pizza, risotto, and regional specialties', 'cuisineImage' => 'italy-cuisine.jpg'],
            ['name' => 'France', 'flag' => '🇫🇷', 'region' => 'Europe', 'description' => 'Haute cuisine, pastries, and wine culture', 'cuisineImage' => 'france-cuisine.jpg'],
            ['name' => 'Spain', 'flag' => '🇪🇸', 'region' => 'Europe', 'description' => 'Tapas, paella, and Mediterranean flavors', 'cuisineImage' => 'spain-cuisine.jpg'],
            ['name' => 'Greece', 'flag' => '🇬🇷', 'region' => 'Europe', 'description' => 'Mediterranean diet with moussaka, souvlaki, and feta', 'cuisineImage' => 'greece-cuisine.jpg'],
            ['name' => 'Turkey', 'flag' => '🇹🇷', 'region' => 'Europe/Asia', 'description' => 'Bridge between Europe and Asia with kebabs and mezze', 'cuisineImage' => 'turkey-cuisine.jpg'],

            // Asia
            ['name' => 'Japan', 'flag' => '🇯🇵', 'region' => 'Asia', 'description' => 'Delicate sushi, ramen, tempura, and kaiseki', 'cuisineImage' => 'japan-cuisine.jpg'],
            ['name' => 'China', 'flag' => '🇨🇳', 'region' => 'Asia', 'description' => 'Diverse regional cuisines from Sichuan to Cantonese', 'cuisineImage' => 'china-cuisine.jpg'],
            ['name' => 'India', 'flag' => '🇮🇳', 'region' => 'Asia', 'description' => 'Spice-rich curries, biryanis, and diverse vegetarian dishes', 'cuisineImage' => 'india-cuisine.jpg'],
            ['name' => 'Thailand', 'flag' => '🇹🇭', 'region' => 'Asia', 'description' => 'Balance of sweet, sour, salty, and spicy flavors', 'cuisineImage' => 'thailand-cuisine.jpg'],
            ['name' => 'Vietnam', 'flag' => '🇻🇳', 'region' => 'Asia', 'description' => 'Fresh herbs, pho, banh mi, and light broths', 'cuisineImage' => 'vietnam-cuisine.jpg'],
            ['name' => 'Lebanon', 'flag' => '🇱🇧', 'region' => 'Middle East', 'description' => 'Mezze, grilled meats, and aromatic spices', 'cuisineImage' => 'lebanon-cuisine.jpg'],

            // Americas
            ['name' => 'Mexico', 'flag' => '🇲🇽', 'region' => 'Americas', 'description' => 'Bold chili flavors, corn, beans, and vibrant salsas', 'cuisineImage' => 'mexico-cuisine.jpg'],
            ['name' => 'United States', 'flag' => '🇺🇸', 'region' => 'Americas', 'description' => 'Melting pot of immigrant influences and regional specialties', 'cuisineImage' => 'usa-cuisine.jpg'],
            ['name' => 'Brazil', 'flag' => '🇧🇷', 'region' => 'Americas', 'description' => 'Feijoada, churrasco, and tropical ingredients', 'cuisineImage' => 'brazil-cuisine.jpg'],
            ['name' => 'Peru', 'flag' => '🇵🇪', 'region' => 'Americas', 'description' => 'Ceviche, potatoes, and fusion cuisine', 'cuisineImage' => 'peru-cuisine.jpg'],

            // Additional
            ['name' => 'South Korea', 'flag' => '🇰🇷', 'region' => 'Asia', 'description' => 'Kimchi, bibimbap, and fermented flavors', 'cuisineImage' => 'korea-cuisine.jpg'],
            ['name' => 'Portugal', 'flag' => '🇵🇹', 'region' => 'Europe', 'description' => 'Seafood, bacalhau, and pastéis de nata', 'cuisineImage' => 'portugal-cuisine.jpg'],
            ['name' => 'Ethiopia', 'flag' => '🇪🇹', 'region' => 'Africa', 'description' => 'Injera bread, wats, and communal eating', 'cuisineImage' => 'ethiopia-cuisine.jpg'],
        ];

        foreach ($countries as $countryData) {
            $country = new Country();
            $country->setName($countryData['name']);
            $country->setFlag($countryData['flag']);
            $country->setRegion($countryData['region']);
            $country->setDescription($countryData['description']);
            $country->setCuisineImage($countryData['cuisineImage']); // NEW LINE
            $manager->persist($country);

            // Store in array for later use
            $this->countries[$countryData['name']] = $country;
        }
    }

    private function createIngredients(ObjectManager $manager): void
    {
        $ingredients = [
            // Proteins
            ['name' => 'Chicken Breast', 'calories' => 165, 'unit' => 'g', 'description' => 'Lean white meat'],
            ['name' => 'Ground Beef', 'calories' => 250, 'unit' => 'g', 'description' => 'Minced beef'],
            ['name' => 'Salmon Fillet', 'calories' => 208, 'unit' => 'g', 'description' => 'Fatty fish rich in omega-3'],
            ['name' => 'Tofu', 'calories' => 76, 'unit' => 'g', 'description' => 'Soybean curd'],
            ['name' => 'Chickpeas', 'calories' => 364, 'unit' => 'g', 'description' => 'Legume for hummus and stews'],
            ['name' => 'Lentils', 'calories' => 116, 'unit' => 'g', 'description' => 'Small legumes'],
            ['name' => 'Eggs', 'calories' => 155, 'unit' => 'piece', 'description' => 'Large chicken eggs'],

            // Carbs
            ['name' => 'Rice', 'calories' => 130, 'unit' => 'g', 'description' => 'White rice'],
            ['name' => 'Pasta', 'calories' => 131, 'unit' => 'g', 'description' => 'Durum wheat pasta'],
            ['name' => 'Potatoes', 'calories' => 77, 'unit' => 'g', 'description' => 'Starchy vegetable'],
            ['name' => 'Bread', 'calories' => 265, 'unit' => 'slice', 'description' => 'White bread'],
            ['name' => 'Couscous', 'calories' => 112, 'unit' => 'g', 'description' => 'North African semolina'],
            ['name' => 'Tortilla', 'calories' => 160, 'unit' => 'piece', 'description' => 'Corn tortilla'],

            // Vegetables
            ['name' => 'Tomatoes', 'calories' => 18, 'unit' => 'g', 'description' => 'Fresh tomatoes'],
            ['name' => 'Onions', 'calories' => 40, 'unit' => 'g', 'description' => 'Yellow onions'],
            ['name' => 'Garlic', 'calories' => 149, 'unit' => 'g', 'description' => 'Garlic cloves'],
            ['name' => 'Bell Peppers', 'calories' => 31, 'unit' => 'g', 'description' => 'Sweet peppers'],
            ['name' => 'Carrots', 'calories' => 41, 'unit' => 'g', 'description' => 'Orange carrots'],
            ['name' => 'Spinach', 'calories' => 23, 'unit' => 'g', 'description' => 'Fresh spinach'],
            ['name' => 'Eggplant', 'calories' => 25, 'unit' => 'g', 'description' => 'Aubergine'],
            ['name' => 'Zucchini', 'calories' => 17, 'unit' => 'g', 'description' => 'Courgette'],

            // Fruits
            ['name' => 'Lemons', 'calories' => 29, 'unit' => 'g', 'description' => 'Citrus fruit'],
            ['name' => 'Olives', 'calories' => 115, 'unit' => 'g', 'description' => 'Cured olives'],
            ['name' => 'Dates', 'calories' => 282, 'unit' => 'g', 'description' => 'Sweet dried fruit'],

            // Dairy
            ['name' => 'Feta Cheese', 'calories' => 264, 'unit' => 'g', 'description' => 'Greek cheese'],
            ['name' => 'Parmesan', 'calories' => 431, 'unit' => 'g', 'description' => 'Hard Italian cheese'],
            ['name' => 'Yogurt', 'calories' => 61, 'unit' => 'g', 'description' => 'Plain yogurt'],
            ['name' => 'Butter', 'calories' => 717, 'unit' => 'g', 'description' => 'Unsalted butter'],
            ['name' => 'Milk', 'calories' => 42, 'unit' => 'ml', 'description' => 'Whole milk'],

            // Fats & Oils
            ['name' => 'Olive Oil', 'calories' => 884, 'unit' => 'ml', 'description' => 'Extra virgin'],
            ['name' => 'Sesame Oil', 'calories' => 884, 'unit' => 'ml', 'description' => 'Asian cooking oil'],
            ['name' => 'Tahini', 'calories' => 595, 'unit' => 'g', 'description' => 'Sesame paste'],

            // Spices & Herbs
            ['name' => 'Cumin', 'calories' => 375, 'unit' => 'g', 'description' => 'Ground cumin'],
            ['name' => 'Paprika', 'calories' => 282, 'unit' => 'g', 'description' => 'Ground paprika'],
            ['name' => 'Turmeric', 'calories' => 354, 'unit' => 'g', 'description' => 'Ground turmeric'],
            ['name' => 'Cinnamon', 'calories' => 247, 'unit' => 'g', 'description' => 'Ground cinnamon'],
            ['name' => 'Parsley', 'calories' => 36, 'unit' => 'g', 'description' => 'Fresh parsley'],
            ['name' => 'Cilantro', 'calories' => 23, 'unit' => 'g', 'description' => 'Fresh coriander'],
            ['name' => 'Mint', 'calories' => 44, 'unit' => 'g', 'description' => 'Fresh mint'],
            ['name' => 'Basil', 'calories' => 22, 'unit' => 'g', 'description' => 'Fresh basil'],

            // Other
            ['name' => 'Flour', 'calories' => 364, 'unit' => 'g', 'description' => 'All-purpose flour'],
            ['name' => 'Sugar', 'calories' => 387, 'unit' => 'g', 'description' => 'White sugar'],
            ['name' => 'Salt', 'calories' => 0, 'unit' => 'g', 'description' => 'Table salt'],
            ['name' => 'Black Pepper', 'calories' => 251, 'unit' => 'g', 'description' => 'Ground pepper'],
            ['name' => 'Soy Sauce', 'calories' => 53, 'unit' => 'ml', 'description' => 'Japanese soy sauce'],
            ['name' => 'Fish Sauce', 'calories' => 35, 'unit' => 'ml', 'description' => 'Southeast Asian sauce'],
            ['name' => 'Tomato Paste', 'calories' => 82, 'unit' => 'g', 'description' => 'Concentrated tomatoes'],
        ];

        foreach ($ingredients as $ingredientData) {
            $ingredient = new Ingredient();
            $ingredient->setName($ingredientData['name']);
            $ingredient->setCaloriesPerUnit($ingredientData['calories']);
            $ingredient->setUnit($ingredientData['unit']);
            $ingredient->setDescription($ingredientData['description']);
            $manager->persist($ingredient);

            $this->ingredients[$ingredientData['name']] = $ingredient;
        }
    }

    private function createUsers(ObjectManager $manager): void
    {
        // Create Admin User
        $admin = new User();
        $admin->setEmail('admin@worldrecipes.com');
        $admin->setFullName('System Administrator');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin123!'));
        $admin->setRole('ROLE_ADMIN');
        $manager->persist($admin);
        $this->users['admin'] = $admin;

        // Create Regular User 1
        $user1 = new User();
        $user1->setEmail('john@worldrecipes.com');
        $user1->setFullName('John Doe');
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'User123!'));
        $manager->persist($user1);
        $this->users['john'] = $user1;

        // Create Regular User 2
        $user2 = new User();
        $user2->setEmail('sarah@worldrecipes.com');
        $user2->setFullName('Sarah Smith');
        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'User123!'));
        $manager->persist($user2);
        $this->users['sarah'] = $user2;

        // Create Nutrition Profile for user1
        $nutritionProfile1 = new NutritionProfile();
        $nutritionProfile1->setDailyCalorieGoal(2200);
        $nutritionProfile1->setActivityLevel('moderate');
        $nutritionProfile1->setDietaryGoal('maintain_weight');
        $nutritionProfile1->setUser($user1);
        $manager->persist($nutritionProfile1);

        // Create Nutrition Profile for user2
        $nutritionProfile2 = new NutritionProfile();
        $nutritionProfile2->setDailyCalorieGoal(1800);
        $nutritionProfile2->setActivityLevel('light');
        $nutritionProfile2->setDietaryGoal('weight_loss');
        $nutritionProfile2->setUser($user2);
        $manager->persist($nutritionProfile2);
    }

    private function createRecipes(ObjectManager $manager): void
    {
        $recipes = [
            // North African Recipes
            [
                'title' => 'Moroccan Chicken Tagine',
                'country' => 'Morocco',
                'user' => 'john',
                'description' => 'Slow-cooked chicken with preserved lemons, olives, and Moroccan spices',
                'instructions' => '1. Brown chicken pieces in olive oil. 2. Add onions, garlic, and spices. 3. Add preserved lemons and olives. 4. Simmer for 1.5 hours until tender. 5. Serve with couscous.',
                'prep_time' => 30,
                'cook_time' => 90,
                'servings' => 4,
                'difficulty' => 'medium',
                'image' => 'moroccan-tagine.jpg',
                'ingredients' => [
                    ['Chicken Breast', 500, 'g'],
                    ['Onions', 200, 'g'],
                    ['Garlic', 20, 'g'],
                    ['Olive Oil', 30, 'ml'],
                    ['Cumin', 5, 'g'],
                    ['Cinnamon', 2, 'g'],
                    ['Lemons', 100, 'g'],
                    ['Olives', 50, 'g'],
                ]
            ],
            [
                'title' => 'Tunisian Brik',
                'country' => 'Tunisia',
                'user' => 'sarah',
                'description' => 'Crispy pastry filled with egg, tuna, and harissa',
                'instructions' => '1. Place brik pastry in pan. 2. Add tuna and harissa. 3. Crack egg in center. 4. Fold and fry until golden. 5. Serve immediately.',
                'prep_time' => 15,
                'cook_time' => 10,
                'servings' => 2,
                'difficulty' => 'easy',
                'image' => 'tunisian-brik.jpg',
                'ingredients' => [
                    ['Eggs', 2, 'piece'],
                    ['Tuna', 150, 'g'],
                    ['Parsley', 10, 'g'],
                    ['Olive Oil', 50, 'ml'],
                ]
            ],
            [
                'title' => 'Egyptian Koshari',
                'country' => 'Egypt',
                'user' => 'john',
                'description' => 'Comfort food with rice, lentils, pasta, and spicy tomato sauce',
                'instructions' => '1. Cook rice, lentils, and pasta separately. 2. Make tomato sauce with spices. 3. Layer ingredients. 4. Top with fried onions.',
                'prep_time' => 20,
                'cook_time' => 40,
                'servings' => 6,
                'difficulty' => 'medium',
                'image' => 'egyptian-koshari.jpg',
                'ingredients' => [
                    ['Rice', 300, 'g'],
                    ['Lentils', 200, 'g'],
                    ['Pasta', 200, 'g'],
                    ['Tomatoes', 400, 'g'],
                    ['Onions', 300, 'g'],
                    ['Garlic', 30, 'g'],
                    ['Cumin', 10, 'g'],
                ]
            ],

            // European Recipes
            [
                'title' => 'Italian Margherita Pizza',
                'country' => 'Italy',
                'user' => 'john',
                'description' => 'Classic Neapolitan pizza with tomato, mozzarella, and basil',
                'instructions' => '1. Stretch pizza dough. 2. Spread tomato sauce. 3. Add fresh mozzarella. 4. Bake at 250°C for 90 seconds. 5. Add fresh basil.',
                'prep_time' => 60,
                'cook_time' => 2,
                'servings' => 2,
                'difficulty' => 'medium',
                'image' => 'italian-pizza.jpg',
                'ingredients' => [
                    ['Flour', 300, 'g'],
                    ['Tomatoes', 200, 'g'],
                    ['Mozzarella', 200, 'g'],
                    ['Basil', 10, 'g'],
                    ['Olive Oil', 20, 'ml'],
                    ['Salt', 5, 'g'],
                ]
            ],
            [
                'title' => 'French Coq au Vin',
                'country' => 'France',
                'user' => 'sarah',
                'description' => 'Chicken braised in red wine with mushrooms and pearl onions',
                'instructions' => '1. Brown chicken. 2. Sauté bacon, mushrooms, onions. 3. Deglaze with wine. 4. Braise for 1 hour. 5. Thicken sauce.',
                'prep_time' => 30,
                'cook_time' => 90,
                'servings' => 4,
                'difficulty' => 'hard',
                'image' => 'french-coq-au-vin.jpg',
                'ingredients' => [
                    ['Chicken Breast', 800, 'g'],
                    ['Bacon', 100, 'g'],
                    ['Mushrooms', 200, 'g'],
                    ['Onions', 150, 'g'],
                    ['Garlic', 20, 'g'],
                    ['Red Wine', 500, 'ml'],
                ]
            ],
            [
                'title' => 'Greek Moussaka',
                'country' => 'Greece',
                'user' => 'john',
                'description' => 'Layered eggplant dish with spiced meat and béchamel sauce',
                'instructions' => '1. Fry eggplant slices. 2. Cook spiced meat sauce. 3. Make béchamel. 4. Layer and bake for 45 minutes.',
                'prep_time' => 45,
                'cook_time' => 60,
                'servings' => 8,
                'difficulty' => 'hard',
                'image' => 'greek-moussaka.jpg',
                'ingredients' => [
                    ['Eggplant', 1000, 'g'],
                    ['Ground Beef', 500, 'g'],
                    ['Tomatoes', 400, 'g'],
                    ['Onions', 200, 'g'],
                    ['Feta Cheese', 200, 'g'],
                    ['Milk', 500, 'ml'],
                    ['Flour', 50, 'g'],
                    ['Butter', 50, 'g'],
                ]
            ],

            // Asian Recipes
            [
                'title' => 'Japanese Sushi Rolls',
                'country' => 'Japan',
                'user' => 'sarah',
                'description' => 'Fresh sushi rolls with salmon, avocado, and cucumber',
                'instructions' => '1. Cook sushi rice. 2. Prepare fillings. 3. Roll with nori. 4. Slice and serve with soy sauce.',
                'prep_time' => 60,
                'cook_time' => 20,
                'servings' => 4,
                'difficulty' => 'hard',
                'image' => 'japanese-sushi.jpg',
                'ingredients' => [
                    ['Rice', 400, 'g'],
                    ['Salmon Fillet', 300, 'g'],
                    ['Avocado', 200, 'g'],
                    ['Cucumber', 100, 'g'],
                    ['Soy Sauce', 50, 'ml'],
                    ['Rice Vinegar', 30, 'ml'],
                ]
            ],
            [
                'title' => 'Indian Butter Chicken',
                'country' => 'India',
                'user' => 'john',
                'description' => 'Creamy tomato-based curry with tender chicken',
                'instructions' => '1. Marinate chicken. 2. Make tomato gravy. 3. Add cream and butter. 4. Simmer chicken in sauce.',
                'prep_time' => 30,
                'cook_time' => 40,
                'servings' => 4,
                'difficulty' => 'medium',
                'image' => 'indian-butter-chicken.jpg',
                'ingredients' => [
                    ['Chicken Breast', 600, 'g'],
                    ['Tomatoes', 800, 'g'],
                    ['Onions', 300, 'g'],
                    ['Garlic', 40, 'g'],
                    ['Ginger', 30, 'g'],
                    ['Cream', 200, 'ml'],
                    ['Butter', 50, 'g'],
                    ['Turmeric', 5, 'g'],
                    ['Cumin', 5, 'g'],
                ]
            ],
            [
                'title' => 'Thai Green Curry',
                'country' => 'Thailand',
                'user' => 'sarah',
                'description' => 'Spicy coconut curry with chicken and vegetables',
                'instructions' => '1. Fry curry paste. 2. Add coconut milk. 3. Add chicken and vegetables. 4. Simmer until cooked.',
                'prep_time' => 20,
                'cook_time' => 25,
                'servings' => 4,
                'difficulty' => 'medium',
                'image' => 'thai-green-curry.jpg',
                'ingredients' => [
                    ['Chicken Breast', 500, 'g'],
                    ['Coconut Milk', 400, 'ml'],
                    ['Bell Peppers', 200, 'g'],
                    ['Thai Eggplant', 150, 'g'],
                    ['Basil', 20, 'g'],
                    ['Fish Sauce', 30, 'ml'],
                ]
            ],

            // Middle Eastern Recipes
            [
                'title' => 'Lebanese Hummus',
                'country' => 'Lebanon',
                'user' => 'john',
                'description' => 'Creamy chickpea dip with tahini and lemon',
                'instructions' => '1. Blend chickpeas. 2. Add tahini, lemon, garlic. 3. Adjust seasoning. 4. Serve with olive oil.',
                'prep_time' => 15,
                'cook_time' => 0,
                'servings' => 6,
                'difficulty' => 'easy',
                'image' => 'lebanese-hummus.jpg',
                'ingredients' => [
                    ['Chickpeas', 400, 'g'],
                    ['Tahini', 100, 'g'],
                    ['Lemons', 2, 'piece'],
                    ['Garlic', 10, 'g'],
                    ['Olive Oil', 30, 'ml'],
                ]
            ],
            [
                'title' => 'Turkish Kebabs',
                'country' => 'Turkey',
                'user' => 'sarah',
                'description' => 'Grilled meat skewers with yogurt sauce',
                'instructions' => '1. Marinate meat. 2. Thread onto skewers. 3. Grill until charred. 4. Serve with rice and salad.',
                'prep_time' => 30,
                'cook_time' => 15,
                'servings' => 4,
                'difficulty' => 'medium',
                'image' => 'turkish-kebabs.jpg',
                'ingredients' => [
                    ['Lamb', 800, 'g'],
                    ['Onions', 200, 'g'],
                    ['Yogurt', 200, 'g'],
                    ['Garlic', 20, 'g'],
                    ['Cumin', 10, 'g'],
                    ['Paprika', 5, 'g'],
                ]
            ],

            // American Recipes
            [
                'title' => 'Mexican Tacos al Pastor',
                'country' => 'Mexico',
                'user' => 'john',
                'description' => 'Marinated pork tacos with pineapple and cilantro',
                'instructions' => '1. Marinate pork. 2. Grill on vertical spit. 3. Slice thinly. 4. Serve on tortillas with toppings.',
                'prep_time' => 120,
                'cook_time' => 30,
                'servings' => 6,
                'difficulty' => 'medium',
                'image' => 'mexican-tacos.jpg',
                'ingredients' => [
                    ['Pork Shoulder', 1000, 'g'],
                    ['Pineapple', 300, 'g'],
                    ['Tortillas', 12, 'piece'],
                    ['Onions', 150, 'g'],
                    ['Cilantro', 50, 'g'],
                    ['Chili Powder', 15, 'g'],
                ]
            ],
            [
                'title' => 'American BBQ Ribs',
                'country' => 'United States',
                'user' => 'sarah',
                'description' => 'Slow-cooked ribs with smoky barbecue sauce',
                'instructions' => '1. Season ribs. 2. Smoke for 4 hours. 3. Baste with sauce. 4. Grill until caramelized.',
                'prep_time' => 30,
                'cook_time' => 240,
                'servings' => 4,
                'difficulty' => 'hard',
                'image' => 'american-bbq-ribs.jpg',
                'ingredients' => [
                    ['Pork Ribs', 1500, 'g'],
                    ['BBQ Sauce', 300, 'ml'],
                    ['Brown Sugar', 100, 'g'],
                    ['Paprika', 20, 'g'],
                    ['Garlic Powder', 10, 'g'],
                ]
            ],
        ];

        foreach ($recipes as $recipeData) {
            $recipe = new Recipe();
            $recipe->setTitle($recipeData['title']);
            $recipe->setCountry($this->countries[$recipeData['country']]);
            $recipe->setUser($this->users[$recipeData['user']]);
            $recipe->setDescription($recipeData['description']);
            $recipe->setInstructions($recipeData['instructions']);
            $recipe->setPreparationTime($recipeData['prep_time']);
            $recipe->setCookingTime($recipeData['cook_time']);
            $recipe->setServings($recipeData['servings']);
            $recipe->setDifficulty($recipeData['difficulty']);
            $recipe->setImage($recipeData['image']);

            // Calculate total calories from ingredients
            $totalCalories = 0;
            foreach ($recipeData['ingredients'] as $ingData) {
                $ingredientName = $ingData[0];
                if (!isset($this->ingredients[$ingredientName])) {
                    continue; // Skip if ingredient not found
                }

                $ingredient = $this->ingredients[$ingredientName];
                $quantity = $ingData[1];

                $recipeIngredient = new RecipeIngredient();
                $recipeIngredient->setIngredient($ingredient);
                $recipeIngredient->setQuantity($quantity);
                $recipeIngredient->setRecipe($recipe);

                // Calculate calories for this ingredient
                $caloriesPerUnit = $ingredient->getCaloriesPerUnit();
                $unit = $ingredient->getUnit();

                // Simple calculation - adjust based on your needs
                if ($unit === 'g' || $unit === 'ml') {
                    $totalCalories += ($quantity * $caloriesPerUnit) / 100;
                } else {
                    $totalCalories += $quantity * $caloriesPerUnit;
                }

                $manager->persist($recipeIngredient);
                $recipe->addRecipeIngredient($recipeIngredient);
            }

            $recipe->setTotalCalories(round($totalCalories));
            $manager->persist($recipe);

            // Store reference for menus
            $this->recipes[$recipeData['title']] = $recipe;
        }
    }

    private function createMenus(ObjectManager $manager): void
    {
        $menus = [
            [
                'name' => 'Mediterranean Week',
                'user' => 'john',
                'description' => 'A week of healthy Mediterranean dishes',
                'recipes' => [
                    'Moroccan Chicken Tagine',
                    'Greek Moussaka',
                    'Lebanese Hummus',
                    'Italian Margherita Pizza',
                ]
            ],
            [
                'name' => 'Asian Favorites',
                'user' => 'sarah',
                'description' => 'Popular dishes from across Asia',
                'recipes' => [
                    'Japanese Sushi Rolls',
                    'Indian Butter Chicken',
                    'Thai Green Curry',
                    'Egyptian Koshari',
                ]
            ],
            [
                'name' => 'World Street Food',
                'user' => 'john',
                'description' => 'Iconic street foods from around the world',
                'recipes' => [
                    'Tunisian Brik',
                    'Mexican Tacos al Pastor',
                    'Turkish Kebabs',
                ]
            ],
            [
                'name' => 'Comfort Food Classics',
                'user' => 'sarah',
                'description' => 'Hearty dishes for cozy nights',
                'recipes' => [
                    'French Coq au Vin',
                    'American BBQ Ribs',
                    'Egyptian Koshari',
                ]
            ],
            [
                'name' => 'Vegetarian Journey',
                'user' => 'john',
                'description' => 'Meat-free dishes from different cultures',
                'recipes' => [
                    'Lebanese Hummus',
                    'Egyptian Koshari',
                    'Greek Moussaka',
                ]
            ],
        ];

        foreach ($menus as $menuData) {
            $menu = new Menu();
            $menu->setName($menuData['name']);
            $menu->setUser($this->users[$menuData['user']]);
            $menu->setDescription($menuData['description']);

            foreach ($menuData['recipes'] as $recipeTitle) {
                if (isset($this->recipes[$recipeTitle])) {
                    $recipe = $this->recipes[$recipeTitle];
                    $menu->addRecipe($recipe);
                }
            }

            $manager->persist($menu);
        }
    }
}
