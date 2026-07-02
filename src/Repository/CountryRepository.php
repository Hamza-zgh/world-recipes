<?php

namespace App\Repository;

use App\Entity\Country;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Country>
 */
class CountryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Country::class);
    }

    public function findBySearchTerm(string $searchTerm): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.name LIKE :searchTerm')
            ->orWhere('c.region LIKE :searchTerm')
            ->orWhere('c.description LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByRegion(string $region): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.region = :region')
            ->setParameter('region', $region)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllWithRecipeCount(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.recipes', 'r')
            ->addSelect('COUNT(r.id) as recipeCount')
            ->groupBy('c.id')
            ->orderBy('recipeCount', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getDistinctRegions(): array
    {
        return $this->createQueryBuilder('c')
            ->select('DISTINCT c.region')
            ->where('c.region IS NOT NULL')
            ->orderBy('c.region', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findByPopularity(int $limit = 12): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.recipes', 'r')
            ->addSelect('COUNT(r.id) as recipeCount')
            ->groupBy('c.id')
            ->orderBy('recipeCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getStatsByRegion(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.region, COUNT(c.id) as countryCount')
            ->where('c.region IS NOT NULL')
            ->groupBy('c.region')
            ->orderBy('countryCount', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function countByRegion(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.region, COUNT(c.id) as count')
            ->groupBy('c.region')
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return Country[] Returns an array of Country objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Country
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
