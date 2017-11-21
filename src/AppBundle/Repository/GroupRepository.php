<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;

/**
 * GroupRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class GroupRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllWithMembers()
    {
        $query = $this->createQueryBuilder('g')
            ->leftJoin('g.administrators', 'administrators')
                ->addSelect('administrators')
            ->leftJoin('g.members', 'members')
                ->addSelect('members')
            ->orderBy('g.name', 'ASC')
            ->getQuery();

        return $query->getResult();
    }

    public function findOneWithMembers($param)
    {
        $query = $this->createQueryBuilder('g')
            ->leftJoin('g.administrators', 'administrators')
                ->addSelect('administrators')
                    ->orderBy('administrators.firstName', 'ASC')
                    ->addOrderBy('administrators.lastName', 'ASC')
            ->leftJoin('g.members', 'members')
                ->addSelect('members')
                    ->orderBy('members.firstName', 'ASC')
                    ->addOrderBy('members.lastName', 'ASC')
            ->where('g.slug = :slug')
                ->setParameter('slug', $param['slug'])
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findAllForUser(User $user)
    {
        $query = $this->createQueryBuilder('g')
            ->leftJoin('g.members', 'members')
            ->leftJoin('g.administrators', 'administrators')
                ->addSelect('administrators')
            ->where('members = :user')
                ->setParameter('user', $user)
            ->orderBy('g.name', 'ASC')
            ->getQuery();

        return $query->getResult();
    }
}