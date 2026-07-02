<?php

namespace App\Controller;

use App\Repository\CountryRepository;
use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExploreController extends AbstractController
{
    #[Route('/explore', name: 'app_explore')]
    public function index(
        Request $request,
        CountryRepository $countryRepository,
        RecipeRepository $recipeRepository
    ): Response
    {
        // Get query parameters
        $searchTerm = $request->query->get('search', '');
        $region = $request->query->get('region', 'all');
        $sort = $request->query->get('sort', 'popular');

        // Get all countries initially
        if ($searchTerm) {
            $countries = $countryRepository->findBySearchTerm($searchTerm);
        } elseif ($region !== 'all') {
            $countries = $countryRepository->findByRegion($region);
        } else {
            $countries = $countryRepository->findAll();
        }

        // Sort countries
        $countries = $this->sortCountries($countries, $sort);

        // Get regions for filter
        $regions = $countryRepository->getDistinctRegions();

        // Get region stats
        $regionStats = $countryRepository->getStatsByRegion();

        // Get total recipe count
        $totalRecipeCount = $recipeRepository->count([]);

        // Calculate stats for each country
        $countryData = [];
        foreach ($countries as $country) {
            $countryRecipes = $country->getRecipes();
            $recipeCount = $countryRecipes->count();

            // Calculate average calories and time
            $totalCalories = 0;
            $totalTime = 0;
            $recipeTitles = [];

            if ($recipeCount > 0) {
                foreach ($countryRecipes as $recipe) {
                    $totalCalories += $recipe->getTotalCalories() ?? 0;
                    $totalTime += ($recipe->getPreparationTime() ?? 0) + ($recipe->getCookingTime() ?? 0);
                    $recipeTitles[] = $recipe->getTitle();
                }

                $avgCalories = $totalCalories / $recipeCount;
                $avgTime = $totalTime / $recipeCount;
            } else {
                $avgCalories = 0;
                $avgTime = 0;
            }

            // Get 3 recipe titles for display
            $displayRecipes = array_slice($recipeTitles, 0, 3);

            $countryData[] = [
                'country' => $country,
                'recipeCount' => $recipeCount,
                'avgCalories' => round($avgCalories),
                'avgTime' => round($avgTime),
                'recipeTitles' => $displayRecipes,
            ];
        }

        return $this->render('explore/index.html.twig', [
            'countryData' => $countryData,
            'regions' => $regions,
            'regionStats' => $regionStats,
            'totalRecipeCount' => $totalRecipeCount,
            'searchTerm' => $searchTerm,
            'selectedRegion' => $region,
            'selectedSort' => $sort,
        ]);
    }

    private function sortCountries(array $countries, string $sortMethod): array
    {
        // Convert to array to sort
        $countriesArray = [];
        foreach ($countries as $country) {
            $countriesArray[] = $country;
        }

        usort($countriesArray, function($a, $b) use ($sortMethod) {
            switch ($sortMethod) {
                case 'name_asc':
                    return strcmp($a->getName(), $b->getName());
                case 'name_desc':
                    return strcmp($b->getName(), $a->getName());
                case 'recipes_asc':
                    return $a->getRecipes()->count() <=> $b->getRecipes()->count();
                case 'recipes_desc':
                case 'popular':
                default:
                    return $b->getRecipes()->count() <=> $a->getRecipes()->count();
            }
        });

        return $countriesArray;
    }
}
