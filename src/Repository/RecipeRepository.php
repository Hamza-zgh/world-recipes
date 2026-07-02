<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    public function findBySearchTerm(string $searchTerm): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.country', 'c')
            ->where('r.title LIKE :searchTerm')
            ->orWhere('r.description LIKE :searchTerm')
            ->orWhere('c.name LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCountry(int $countryId): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.country', 'c')
            ->where('c.id = :countryId')
            ->setParameter('countryId', $countryId)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByDifficulty(string $difficulty): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.difficulty = :difficulty')
            ->setParameter('difficulty', $difficulty)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCalorieRange(int $min, int $max): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.totalCalories BETWEEN :min AND :max')
            ->setParameter('min', $min)
            ->setParameter('max', $max)
            ->orderBy('r.totalCalories', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByPreparationTime(int $maxTime): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.preparationTime <= :maxTime')
            ->setParameter('maxTime', $maxTime)
            ->orderBy('r.preparationTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getDistinctDifficulties(): array
    {
        return $this->createQueryBuilder('r')
            ->select('DISTINCT r.difficulty')
            ->where('r.difficulty IS NOT NULL')
            ->orderBy('r.difficulty', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function getStatsByDifficulty(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.difficulty, COUNT(r.id) as recipeCount')
            ->where('r.difficulty IS NOT NULL')
            ->groupBy('r.difficulty')
            ->orderBy('recipeCount', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPopularRecipes(int $limit = 12): array
    {
        // For now, return newest recipes. Later you can add view count or likes.
        return $this->createQueryBuilder('r')
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findQuickRecipes(int $maxTime = 30): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.preparationTime <= :maxTime')
            ->setParameter('maxTime', $maxTime)
            ->orderBy('r.preparationTime', 'ASC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();
    }

    public function findLowCalorieRecipes(int $maxCalories = 400): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.totalCalories <= :maxCalories')
            ->setParameter('maxCalories', $maxCalories)
            ->orderBy('r.totalCalories', 'ASC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();
    }
    public function countByDifficulty(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.difficulty, COUNT(r.id) as count')
            ->where('r.difficulty IS NOT NULL')
            ->groupBy('r.difficulty')
            ->getQuery()
            ->getResult();
    }

    public function findRecent(int $days = 7): array
    {
        $date = new \DateTimeImmutable("-{$days} days");

        return $this->createQueryBuilder('r')
            ->leftJoin('r.country', 'c')
            ->addSelect('c')
            ->where('r.createdAt >= :date')
            ->setParameter('date', $date)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }
}
