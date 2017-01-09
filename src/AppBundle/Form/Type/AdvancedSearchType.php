<?php

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\Constraints\Count;

class AdvancedSearchType extends AbstractType
{
    private $tokenStorage;

    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search', TextType::class, [
                'required' => false,
            ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Gmo' => 'gmo',
                    'Wild' => 'wild',
                    'Plasmid' => 'plasmid',
                ],
                'expanded' => true,
                'multiple' => true,
                'data' => ['gmo', 'wild', 'plasmid'],
                'constraints' => [
                    new Count(['min' => 1, 'minMessage' => 'Select at least one element.']),
                ],
            ])
            ->add('country', CountryType::class, [
                'placeholder' => 'All countries',
                'required' => false,
            ])
            ->add('project', EntityType::class, [
                'class' => 'AppBundle\Entity\Project',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('project')
                        ->leftJoin('project.members', 'members')
                        ->where('members = :user')
                        ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                        ->orderBy('project.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => 'All available projects',
                'required' => false,
            ])
            ->add('type', EntityType::class, [
                'class' => 'AppBundle\Entity\Type',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('type')
                        ->orderBy('type.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => 'All types',
                'required' => false,
            ])
            ->add('author', EntityType::class, [
                'class' => 'AppBundle\Entity\User',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->leftJoin('user.teams', 'teams')
                        ->where('teams = :teams')
                        ->setParameter('teams', $this->tokenStorage->getToken()->getUser()->getTeams())
                        ->orderBy('user.username', 'ASC');
                },
                'choice_label' => 'username',
                'placeholder' => 'All users',
                'required' => false,
            ])
            ->add('deleted', CheckboxType::class, [
                'label' => 'Search deleted strains ?',
                'required' => false,
            ])
        ;
    }
}
