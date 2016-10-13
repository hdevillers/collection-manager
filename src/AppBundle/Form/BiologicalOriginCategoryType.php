<?php

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class BiologicalOriginCategoryType extends AbstractType
{
    private $user;

    public function __construct(TokenStorage $token)
    {
        $this->user = $token->getToken()->getUser();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'Soil, Insect, ...',
                )
            ))
            ->add('team', EntityType::class, array(
                'class' => 'AppBundle\Entity\Team',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('teams')
                        ->leftJoin('teams.members', 'members')
                        ->where('members = :user')
                        ->setParameter('user', $this->user)
                        ->orderBy('teams.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => '-- select a team --',
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\BiologicalOriginCategory'
        ));
    }
}
