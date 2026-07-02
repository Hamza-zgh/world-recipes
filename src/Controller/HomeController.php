<?php

namespace App\Controller;

use App\Repository\CountryRepository;
use App\Repository\RecipeRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        CountryRepository $countryRepository,
        RecipeRepository $recipeRepository,
        UserRepository $userRepository
    ): Response
    {
        // Get stats for hero section
        $countryCount = $countryRepository->count([]);
        $recipeCount = $recipeRepository->count([]);
        $userCount = $userRepository->count([]);

        // Get countries for Global Culinary Adventures section
        $countries = $countryRepository->findAll();

        // Get additional stats for each country
        $countryData = [];
        foreach ($countries as $country) {
            $countryRecipes = $country->getRecipes();
            $recipeCountByCountry = $countryRecipes->count();

            // Calculate average calories and time
            $totalCalories = 0;
            $totalTime = 0;
            $recipeTitles = [];

            if ($recipeCountByCountry > 0) {
                foreach ($countryRecipes as $recipe) {
                    $totalCalories += $recipe->getTotalCalories() ?? 0;
                    $totalTime += ($recipe->getPreparationTime() ?? 0) + ($recipe->getCookingTime() ?? 0);
                    $recipeTitles[] = $recipe->getTitle();
                }

                $avgCalories = $totalCalories / $recipeCountByCountry;
                $avgTime = $totalTime / $recipeCountByCountry;
            } else {
                $avgCalories = 0;
                $avgTime = 0;
            }

            // Limit recipe titles to 3 for display
            $displayRecipes = array_slice($recipeTitles, 0, 3);

            $countryData[] = [
                'country' => $country,
                'recipeCount' => $recipeCountByCountry,
                'avgCalories' => round($avgCalories),
                'avgTime' => round($avgTime),
                'recipeTitles' => $displayRecipes,
            ];
        }

        // Sort countries by recipe count (descending) and take top 6
        usort($countryData, function($a, $b) {
            return $b['recipeCount'] <=> $a['recipeCount'];
        });

        $featuredCountries = array_slice($countryData, 0, 6);

        return $this->render('home/index.html.twig', [
            'countryCount' => $countryCount,
            'recipeCount' => $recipeCount,
            'userCount' => $userCount,
            'featuredCountries' => $featuredCountries,
            'allCountries' => $countryData,
        ]);
    }
}
