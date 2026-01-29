<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Mechanic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MechanicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mechanic::class);
    }
}
