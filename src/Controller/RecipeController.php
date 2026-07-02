<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Country;
use App\Repository\RecipeRepository;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RecipeController extends AbstractController
{
    #[Route('/recipes', name: 'app_recipes')]
    public function index(
        Request $request,
        RecipeRepository $recipeRepository,
        CountryRepository $countryRepository
    ): Response
    {
        // Get query parameters
        $searchTerm = $request->query->get('search', '');
        $countryId = $request->query->get('country', 'all');
        $difficulty = $request->query->get('difficulty', 'all');
        $sort = $request->query->get('sort', 'newest');

        // Get all recipes initially
        if ($searchTerm) {
            $recipes = $recipeRepository->findBySearchTerm($searchTerm);
        } elseif ($countryId !== 'all' && is_numeric($countryId)) {
            $recipes = $recipeRepository->findByCountry((int)$countryId);
        } elseif ($difficulty !== 'all') {
            $recipes = $recipeRepository->findByDifficulty($difficulty);
        } else {
            $recipes = $recipeRepository->findAll();
        }

        // Sort recipes
        $recipes = $this->sortRecipes($recipes, $sort);

        // Get filters data
        $difficulties = $recipeRepository->getDistinctDifficulties();
        $countries = $countryRepository->findAll();

        // Get quick stats
        $totalRecipes = $recipeRepository->count([]);
        $quickRecipes = $recipeRepository->findQuickRecipes(30);
        $lowCalorieRecipes = $recipeRepository->findLowCalorieRecipes(400);

        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipes,
            'difficulties' => $difficulties,
            'countries' => $countries,
            'totalRecipes' => $totalRecipes,
            'quickRecipes' => $quickRecipes,
            'lowCalorieRecipes' => $lowCalorieRecipes,
            'searchTerm' => $searchTerm,
            'selectedCountry' => $countryId,
            'selectedDifficulty' => $difficulty,
            'selectedSort' => $sort,
        ]);
    }

    #[Route('/recipes/{id}', name: 'app_recipe_show')]
    public function show(Recipe $recipe, EntityManagerInterface $entityManager): Response
    {
        // Calculate total calories from ingredients
        $totalCalories = 0;
        $ingredientDetails = [];

        foreach ($recipe->getRecipeIngredients() as $recipeIngredient) {
            $ingredient = $recipeIngredient->getIngredient();

            // Check if ingredient exists
            if ($ingredient) {
                $quantity = $recipeIngredient->getQuantity();
                $calories = $ingredient->getCaloriesPerUnit();

                // FIX: Remove the division by 100 - caloriesPerUnit should already be per unit
                $ingredientCalories = $quantity * $calories;
                $totalCalories += $ingredientCalories;

                $ingredientDetails[] = [
                    'name' => $ingredient->getName(),
                    'quantity' => $quantity,
                    'unit' => $ingredient->getUnit(),
                    'calories' => round($ingredientCalories),
                    'notes' => $recipeIngredient->getNotes(),
                ];
            }
        }

        // If recipe doesn't have total calories calculated, update it
        // But only if we have ingredients
        if (($recipe->getTotalCalories() == 0 || !$recipe->getTotalCalories()) && count($ingredientDetails) > 0) {
            $recipe->setTotalCalories(round($totalCalories));
            $entityManager->flush();
        }

        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe,
            'ingredientDetails' => $ingredientDetails,
            'totalCalories' => round($totalCalories),
        ]);
    }

    private function sortRecipes(array $recipes, string $sortMethod): array
    {
        usort($recipes, function($a, $b) use ($sortMethod) {
            switch ($sortMethod) {
                case 'name_asc':
                    return strcmp($a->getTitle(), $b->getTitle());
                case 'name_desc':
                    return strcmp($b->getTitle(), $a->getTitle());
                case 'calories_asc':
                    return ($a->getTotalCalories() ?? 0) <=> ($b->getTotalCalories() ?? 0);
                case 'calories_desc':
                    return ($b->getTotalCalories() ?? 0) <=> ($a->getTotalCalories() ?? 0);
                case 'time_asc':
                    return ($a->getPreparationTime() ?? 0) <=> ($b->getPreparationTime() ?? 0);
                case 'time_desc':
                    return ($b->getPreparationTime() ?? 0) <=> ($a->getPreparationTime() ?? 0);
                case 'newest':
                default:
                    return $b->getCreatedAt() <=> $a->getCreatedAt();
            }
        });

        return $recipes;
    }
}
