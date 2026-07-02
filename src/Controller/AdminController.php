<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Country;
use App\Entity\Recipe;
use App\Entity\Menu;
use App\Entity\Ingredient;
use App\Entity\RecipeIngredient;
use App\Repository\UserRepository;
use App\Repository\CountryRepository;
use App\Repository\RecipeRepository;
use App\Repository\MenuRepository;
use App\Repository\IngredientRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function dashboard(
        UserRepository $userRepository,
        CountryRepository $countryRepository,
        RecipeRepository $recipeRepository,
        MenuRepository $menuRepository,
        IngredientRepository $ingredientRepository
    ): Response
    {
        $stats = [
            'users' => [
                'total' => $userRepository->count([]),
                'admins' => $userRepository->count(['role' => 'ROLE_ADMIN']),
                'regular' => $userRepository->count(['role' => 'ROLE_USER']),
                'recent' => $userRepository->findRecentUsers(7),
            ],
            'countries' => [
                'total' => $countryRepository->count([]),
                'by_region' => $countryRepository->countByRegion(),
            ],
            'recipes' => [
                'total' => $recipeRepository->count([]),
                'by_difficulty' => $recipeRepository->countByDifficulty(),
                'recent' => $recipeRepository->findRecent(7),
            ],
            'menus' => [
                'total' => $menuRepository->count([]),
            ],
            'ingredients' => [
                'total' => $ingredientRepository->count([]),
            ],
        ];

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    // ==================== USERS ====================

    #[Route('/users', name: 'app_admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/new', name: 'app_admin_user_new')]
    public function newUser(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();

        if ($request->isMethod('POST')) {
            $user->setFullName($request->request->get('fullName'));
            $user->setEmail($request->request->get('email'));
            $user->setRole($request->request->get('role'));

            // Generate a random password (in production, send email to set password)
            $tempPassword = bin2hex(random_bytes(8));
            $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User created successfully. Temporary password: ' . $tempPassword);
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/users/new.html.twig');
    }

    #[Route('/users/{id}/edit', name: 'app_admin_user_edit')]
    public function editUser(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $user->setFullName($request->request->get('fullName'));
            $user->setEmail($request->request->get('email'));
            $user->setRole($request->request->get('role'));

            $entityManager->flush();

            $this->addFlash('success', 'User updated successfully.');
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/users/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): Response
    {
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'You cannot delete your own account.');
            return $this->redirectToRoute('app_admin_users');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'User deleted successfully.');
        return $this->redirectToRoute('app_admin_users');
    }

    // ==================== COUNTRIES ====================

    #[Route('/countries', name: 'app_admin_countries')]
    public function countries(CountryRepository $countryRepository): Response
    {
        $countries = $countryRepository->findAll();

        return $this->render('admin/countries/index.html.twig', [
            'countries' => $countries,
        ]);
    }

    #[Route('/countries/new', name: 'app_admin_country_new')]
    public function newCountry(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $countryUploader,
        FileUploader $flagUploader
    ): Response
    {
        $country = new Country();

        if ($request->isMethod('POST')) {
            $country->setName($request->request->get('name'));
            $country->setRegion($request->request->get('region'));
            $country->setDescription($request->request->get('description'));

            // Handle cuisine image upload
            /** @var UploadedFile $cuisineImageFile */
            $cuisineImageFile = $request->files->get('cuisineImage');
            if ($cuisineImageFile) {
                $cuisineImageFileName = $countryUploader->upload($cuisineImageFile);
                $country->setCuisineImage($cuisineImageFileName);
            }

            // Handle flag image upload
            /** @var UploadedFile $flagFile */
            $flagFile = $request->files->get('flag');
            if ($flagFile) {
                $flagFileName = $flagUploader->upload($flagFile);
                $country->setFlag($flagFileName);
            }

            $entityManager->persist($country);
            $entityManager->flush();

            $this->addFlash('success', 'Country created successfully.');
            return $this->redirectToRoute('app_admin_countries');
        }

        return $this->render('admin/countries/new.html.twig');
    }

    #[Route('/countries/{id}/edit', name: 'app_admin_country_edit')]
    public function editCountry(
        Country $country,
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $countryUploader,
        FileUploader $flagUploader
    ): Response
    {
        if ($request->isMethod('POST')) {
            $country->setName($request->request->get('name'));
            $country->setRegion($request->request->get('region'));
            $country->setDescription($request->request->get('description'));

            // Handle remove flag checkbox
            if ($request->request->has('remove_flag')) {
                if ($country->getFlag()) {
                    $flagUploader->delete($country->getFlag());
                }
                $country->setFlag(null);
            }

            // Handle flag image upload
            /** @var UploadedFile $flagFile */
            $flagFile = $request->files->get('flag');
            if ($flagFile) {
                // Delete old flag if exists
                if ($country->getFlag()) {
                    $flagUploader->delete($country->getFlag());
                }

                $flagFileName = $flagUploader->upload($flagFile);
                $country->setFlag($flagFileName);
            }

            // Handle remove cuisine image checkbox
            if ($request->request->has('remove_cuisine_image')) {
                if ($country->getCuisineImage()) {
                    $countryUploader->delete($country->getCuisineImage());
                }
                $country->setCuisineImage(null);
            }

            // Handle cuisine image upload
            /** @var UploadedFile $cuisineImageFile */
            $cuisineImageFile = $request->files->get('cuisineImage');
            if ($cuisineImageFile) {
                // Delete old image if exists
                if ($country->getCuisineImage()) {
                    $countryUploader->delete($country->getCuisineImage());
                }

                $cuisineImageFileName = $countryUploader->upload($cuisineImageFile);
                $country->setCuisineImage($cuisineImageFileName);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Country updated successfully.');
            return $this->redirectToRoute('app_admin_countries');
        }

        return $this->render('admin/countries/edit.html.twig', [
            'country' => $country,
        ]);
    }

    #[Route('/countries/{id}/delete', name: 'app_admin_country_delete', methods: ['POST'])]
    public function deleteCountry(
        Country $country,
        EntityManagerInterface $entityManager,
        FileUploader $countryUploader,
        FileUploader $flagUploader
    ): Response
    {
        // Check if country has recipes
        if ($country->getRecipes()->count() > 0) {
            $this->addFlash('error', 'Cannot delete country with existing recipes. Delete the recipes first.');
            return $this->redirectToRoute('app_admin_countries');
        }

        // Delete images if they exist
        if ($country->getCuisineImage()) {
            $countryUploader->delete($country->getCuisineImage());
        }

        if ($country->getFlag()) {
            $flagUploader->delete($country->getFlag());
        }

        $entityManager->remove($country);
        $entityManager->flush();

        $this->addFlash('success', 'Country deleted successfully.');
        return $this->redirectToRoute('app_admin_countries');
    }

    // ==================== RECIPES ====================

    #[Route('/recipes', name: 'app_admin_recipes')]
    public function recipes(RecipeRepository $recipeRepository): Response
    {
        $recipes = $recipeRepository->findAll();

        return $this->render('admin/recipes/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route('/recipes/new', name: 'app_admin_recipe_new')]
    public function newRecipe(
        Request $request,
        EntityManagerInterface $entityManager,
        CountryRepository $countryRepository,
        IngredientRepository $ingredientRepository,
        FileUploader $recipeUploader
    ): Response
    {
        $recipe = new Recipe();

        if ($request->isMethod('POST')) {
            $recipe->setTitle($request->request->get('title'));
            $recipe->setDescription($request->request->get('description'));
            $recipe->setInstructions($request->request->get('instructions'));
            $recipe->setPreparationTime($request->request->get('preparationTime'));
            $recipe->setCookingTime($request->request->get('cookingTime'));
            $recipe->setServings($request->request->get('servings'));
            $recipe->setDifficulty($request->request->get('difficulty'));

            // Handle recipe image upload
            /** @var UploadedFile $imageFile */
            $imageFile = $request->files->get('image');
            if ($imageFile) {
                $imageFileName = $recipeUploader->upload($imageFile);
                $recipe->setImage($imageFileName);
            }

            // Set user (current admin)
            $recipe->setUser($this->getUser());

            // Set country
            $countryId = $request->request->get('country');
            if ($countryId) {
                $country = $countryRepository->find($countryId);
                if ($country) {
                    $recipe->setCountry($country);
                }
            }

            $entityManager->persist($recipe);

            // Handle ingredients
            $ingredientIds = $request->request->all('ingredients') ?? [];
            $quantities = $request->request->all('quantities') ?? [];
            $notes = $request->request->all('notes') ?? [];

            foreach ($ingredientIds as $index => $ingredientId) {
                if (!empty($ingredientId) && !empty($quantities[$index])) {
                    $ingredient = $ingredientRepository->find($ingredientId);
                    if ($ingredient) {
                        $recipeIngredient = new RecipeIngredient();
                        $recipeIngredient->setRecipe($recipe);
                        $recipeIngredient->setIngredient($ingredient);
                        $recipeIngredient->setQuantity((float)$quantities[$index]);
                        $recipeIngredient->setNotes($notes[$index] ?? null);

                        $entityManager->persist($recipeIngredient);
                        $recipe->addRecipeIngredient($recipeIngredient);
                    }
                }
            }

            // Calculate total calories
            $recipe->setTotalCalories($recipe->calculateTotalCalories());

            $entityManager->flush();

            $this->addFlash('success', 'Recipe created successfully.');
            return $this->redirectToRoute('app_admin_recipes');
        }

        $countries = $countryRepository->findAll();
        $ingredients = $ingredientRepository->findAll();

        return $this->render('admin/recipes/new.html.twig', [
            'countries' => $countries,
            'ingredients' => $ingredients,
        ]);
    }

    #[Route('/recipes/{id}/edit', name: 'app_admin_recipe_edit')]
    public function editRecipe(
        Recipe $recipe,
        Request $request,
        EntityManagerInterface $entityManager,
        CountryRepository $countryRepository,
        IngredientRepository $ingredientRepository,
        FileUploader $recipeUploader
    ): Response
    {
        if ($request->isMethod('POST')) {
            $recipe->setTitle($request->request->get('title'));
            $recipe->setDescription($request->request->get('description'));
            $recipe->setInstructions($request->request->get('instructions'));
            $recipe->setPreparationTime($request->request->get('preparationTime'));
            $recipe->setCookingTime($request->request->get('cookingTime'));
            $recipe->setServings($request->request->get('servings'));
            $recipe->setDifficulty($request->request->get('difficulty'));

            // Handle remove image checkbox
            if ($request->request->has('remove_image')) {
                if ($recipe->getImage()) {
                    $recipeUploader->delete($recipe->getImage());
                }
                $recipe->setImage(null);
            }

            // Handle recipe image upload
            /** @var UploadedFile $imageFile */
            $imageFile = $request->files->get('image');
            if ($imageFile) {
                // Delete old image if exists
                if ($recipe->getImage()) {
                    $recipeUploader->delete($recipe->getImage());
                }

                $imageFileName = $recipeUploader->upload($imageFile);
                $recipe->setImage($imageFileName);
            }

            // Set country
            $countryId = $request->request->get('country');
            if ($countryId) {
                $country = $countryRepository->find($countryId);
                if ($country) {
                    $recipe->setCountry($country);
                }
            }

            // Remove existing ingredients
            foreach ($recipe->getRecipeIngredients() as $recipeIngredient) {
                $entityManager->remove($recipeIngredient);
            }

            // Handle new ingredients
            $ingredientIds = $request->request->all('ingredients') ?? [];
            $quantities = $request->request->all('quantities') ?? [];
            $notes = $request->request->all('notes') ?? [];

            foreach ($ingredientIds as $index => $ingredientId) {
                if (!empty($ingredientId) && !empty($quantities[$index])) {
                    $ingredient = $ingredientRepository->find($ingredientId);
                    if ($ingredient) {
                        $recipeIngredient = new RecipeIngredient();
                        $recipeIngredient->setRecipe($recipe);
                        $recipeIngredient->setIngredient($ingredient);
                        $recipeIngredient->setQuantity((float)$quantities[$index]);
                        $recipeIngredient->setNotes($notes[$index] ?? null);

                        $entityManager->persist($recipeIngredient);
                        $recipe->addRecipeIngredient($recipeIngredient);
                    }
                }
            }

            // Recalculate total calories
            $recipe->setTotalCalories($recipe->calculateTotalCalories());

            $entityManager->flush();

            $this->addFlash('success', 'Recipe updated successfully.');
            return $this->redirectToRoute('app_admin_recipes');
        }

        $countries = $countryRepository->findAll();
        $ingredients = $ingredientRepository->findAll();

        return $this->render('admin/recipes/edit.html.twig', [
            'recipe' => $recipe,
            'countries' => $countries,
            'ingredients' => $ingredients,
        ]);
    }

    #[Route('/recipes/{id}/delete', name: 'app_admin_recipe_delete', methods: ['POST'])]
    public function deleteRecipe(
        Recipe $recipe,
        EntityManagerInterface $entityManager,
        FileUploader $recipeUploader
    ): Response
    {
        // Delete image if exists
        if ($recipe->getImage()) {
            $recipeUploader->delete($recipe->getImage());
        }

        $entityManager->remove($recipe);
        $entityManager->flush();

        $this->addFlash('success', 'Recipe deleted successfully.');
        return $this->redirectToRoute('app_admin_recipes');
    }

    // ==================== INGREDIENTS ====================

    #[Route('/ingredients', name: 'app_admin_ingredients')]
    public function ingredients(IngredientRepository $ingredientRepository): Response
    {
        $ingredients = $ingredientRepository->findAll();

        return $this->render('admin/ingredients/index.html.twig', [
            'ingredients' => $ingredients,
        ]);
    }

    #[Route('/ingredients/new', name: 'app_admin_ingredient_new')]
    public function newIngredient(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ingredient = new Ingredient();

        // Create form using FormBuilder
        $form = $this->createFormBuilder($ingredient)
            ->add('name', TextType::class, [
                'label' => 'Ingredient Name *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g., Flour, Sugar, Salt'
                ]
            ])
            ->add('caloriesPerUnit', NumberType::class, [
                'label' => 'Calories per Unit *',
                'attr' => [
                    'class' => 'form-control',
                    'step' => '0.01',
                    'placeholder' => 'e.g., 364.5'
                ]
            ])
            ->add('unit', ChoiceType::class, [
                'label' => 'Unit *',
                'choices' => [
                    'Select unit' => '',
                    'Grams (g)' => 'g',
                    'Milliliters (ml)' => 'ml',
                    'Cup' => 'cup',
                    'Tablespoon' => 'tbsp',
                    'Teaspoon' => 'tsp',
                    'Piece' => 'piece',
                    'Slice' => 'slice',
                    'Pinch' => 'pinch'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => '3',
                    'placeholder' => 'Nutritional information or notes...'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Create Ingredient',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ingredient);
            $entityManager->flush();

            $this->addFlash('success', 'Ingredient created successfully.');
            return $this->redirectToRoute('app_admin_ingredients');
        }

        return $this->render('admin/ingredients/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ingredients/{id}/edit', name: 'app_admin_ingredient_edit')]
    public function editIngredient(Ingredient $ingredient, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Create form using FormBuilder
        $form = $this->createFormBuilder($ingredient)
            ->add('name', TextType::class, [
                'label' => 'Ingredient Name *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g., Flour, Sugar, Salt'
                ]
            ])
            ->add('caloriesPerUnit', NumberType::class, [
                'label' => 'Calories per Unit *',
                'attr' => [
                    'class' => 'form-control',
                    'step' => '0.01',
                    'placeholder' => 'e.g., 364.5'
                ]
            ])
            ->add('unit', ChoiceType::class, [
                'label' => 'Unit *',
                'choices' => [
                    'Select unit' => '',
                    'Grams (g)' => 'g',
                    'Milliliters (ml)' => 'ml',
                    'Cup' => 'cup',
                    'Tablespoon' => 'tbsp',
                    'Teaspoon' => 'tsp',
                    'Piece' => 'piece',
                    'Slice' => 'slice',
                    'Pinch' => 'pinch'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => '3',
                    'placeholder' => 'Nutritional information or notes...'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save Changes',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Ingredient updated successfully.');
            return $this->redirectToRoute('app_admin_ingredients');
        }

        return $this->render('admin/ingredients/edit.html.twig', [
            'ingredient' => $ingredient,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ingredients/{id}/delete', name: 'app_admin_ingredient_delete', methods: ['POST'])]
    public function deleteIngredient(Ingredient $ingredient, EntityManagerInterface $entityManager): Response
    {
        // Check if ingredient is used in recipes
        if ($ingredient->getRecipeIngredients()->count() > 0) {
            $this->addFlash('error', 'Cannot delete ingredient used in recipes. Remove it from recipes first.');
            return $this->redirectToRoute('app_admin_ingredients');
        }

        $entityManager->remove($ingredient);
        $entityManager->flush();

        $this->addFlash('success', 'Ingredient deleted successfully.');
        return $this->redirectToRoute('app_admin_ingredients');
    }

    // ==================== MENUS ====================

    #[Route('/menus', name: 'app_admin_menus')]
    public function menus(MenuRepository $menuRepository): Response
    {
        $menus = $menuRepository->findAll();

        return $this->render('admin/menus/index.html.twig', [
            'menus' => $menus,
        ]);
    }

    #[Route('/menus/new', name: 'app_admin_menu_new')]
    public function newMenu(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        RecipeRepository $recipeRepository
    ): Response
    {
        $menu = new Menu();

        // Get users and recipes for form
        $users = $userRepository->findAll();
        $recipes = $recipeRepository->findAll();

        // Prepare user choices for form
        $userChoices = [];
        foreach ($users as $user) {
            $userChoices[$user->getFullName() . ' (' . $user->getEmail() . ')'] = $user->getId();
        }

        // Prepare recipe choices for form
        $recipeChoices = [];
        foreach ($recipes as $recipe) {
            $recipeChoices[$recipe->getTitle() . ' (' . $recipe->getCountry()->getName() . ')'] = $recipe->getId();
        }

        // Create form using FormBuilder - but without binding to entity for recipes
        $form = $this->createFormBuilder()
            ->add('name', TextType::class, [
                'label' => 'Menu Name *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g., Weekly Dinner Menu, Healthy Lunch Plan'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => '3',
                    'placeholder' => 'Describe this menu...'
                ]
            ])
            ->add('user', ChoiceType::class, [
                'label' => 'User *',
                'choices' => $userChoices,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('recipes', ChoiceType::class, [
                'label' => 'Recipes',
                'choices' => $recipeChoices,
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'size' => '8'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Create Menu',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Set basic properties
            $menu->setName($data['name']);
            $menu->setDescription($data['description']);

            // Handle user relationship
            $userId = $data['user'];
            $user = $userRepository->find($userId);
            if ($user) {
                $menu->setUser($user);
            }

            // Handle recipes relationship
            $selectedRecipeIds = $data['recipes'] ?? [];
            foreach ($selectedRecipeIds as $recipeId) {
                $recipe = $recipeRepository->find($recipeId);
                if ($recipe) {
                    $menu->addRecipe($recipe);
                }
            }

            $entityManager->persist($menu);
            $entityManager->flush();

            $this->addFlash('success', 'Menu created successfully.');
            return $this->redirectToRoute('app_admin_menus');
        }

        return $this->render('admin/menus/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/menus/{id}/edit', name: 'app_admin_menu_edit')]
    public function editMenu(
        Menu $menu,
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        RecipeRepository $recipeRepository
    ): Response
    {
        // Get users and recipes for form
        $users = $userRepository->findAll();
        $recipes = $recipeRepository->findAll();

        // Prepare user choices for form
        $userChoices = [];
        foreach ($users as $user) {
            $userChoices[$user->getFullName() . ' (' . $user->getEmail() . ')'] = $user->getId();
        }

        // Prepare recipe choices for form
        $recipeChoices = [];
        foreach ($recipes as $recipe) {
            $recipeChoices[$recipe->getTitle() . ' (' . $recipe->getCountry()->getName() . ')'] = $recipe->getId();
        }

        // Get current recipe IDs for pre-selection
        $currentRecipeIds = [];
        foreach ($menu->getRecipes() as $recipe) {
            $currentRecipeIds[] = $recipe->getId();
        }

        // Create form using FormBuilder - without binding to entity
        $form = $this->createFormBuilder([
            'name' => $menu->getName(),
            'description' => $menu->getDescription(),
            'user' => $menu->getUser() ? $menu->getUser()->getId() : null,
            'recipes' => $currentRecipeIds,
        ])
            ->add('name', TextType::class, [
                'label' => 'Menu Name *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g., Weekly Dinner Menu, Healthy Lunch Plan'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => '3',
                    'placeholder' => 'Describe this menu...'
                ]
            ])
            ->add('user', ChoiceType::class, [
                'label' => 'User *',
                'choices' => $userChoices,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('recipes', ChoiceType::class, [
                'label' => 'Recipes',
                'choices' => $recipeChoices,
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'size' => '8'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save Changes',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Update basic properties
            $menu->setName($data['name']);
            $menu->setDescription($data['description']);

            // Handle user relationship
            $userId = $data['user'];
            $user = $userRepository->find($userId);
            if ($user) {
                $menu->setUser($user);
            }

            // Clear existing recipes
            foreach ($menu->getRecipes() as $recipe) {
                $menu->removeRecipe($recipe);
            }

            // Add selected recipes
            $selectedRecipeIds = $data['recipes'] ?? [];
            foreach ($selectedRecipeIds as $recipeId) {
                $recipe = $recipeRepository->find($recipeId);
                if ($recipe) {
                    $menu->addRecipe($recipe);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Menu updated successfully.');
            return $this->redirectToRoute('app_admin_menus');
        }

        return $this->render('admin/menus/edit.html.twig', [
            'menu' => $menu,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/menus/{id}/delete', name: 'app_admin_menu_delete', methods: ['POST'])]
    public function deleteMenu(Menu $menu, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($menu);
        $entityManager->flush();

        $this->addFlash('success', 'Menu deleted successfully.');
        return $this->redirectToRoute('app_admin_menus');
    }
}
