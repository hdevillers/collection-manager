<?php

namespace AppBundle\Repository;
use AppBundle\Entity\User;

/**
 * TeamRequestRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TeamRequestRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAdministredOrModerated(User $user)
    {
        $query = $this->createQueryBuilder('request')
            ->leftJoin('request.team', 'team')
            ->leftJoin('team.administrators', 'administrators')
            ->leftJoin('team.moderators', 'moderators')
            ->where('administrators = :user')
            ->orWhere('moderators = :user')
            ->andWhere('request.answer is NULL')
            ->setParameter('user', $user)
            ->orderBy('team.name', 'ASC')
            ->addOrderBy('request.requestDate', 'ASC')
            ->getQuery();

        return $query->getResult();
    }
}
