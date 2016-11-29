<?php

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class PrimerType extends AbstractType
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
            ->add('team', EntityType::class, array(
                'class' => 'AppBundle\Entity\Team',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('team')
                        ->leftJoin('team.members', 'members')
                        ->where('members = :user')
                            ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                        ->orderBy('team.name', 'ASC');
                }
            ))
            ->add('name', TextType::class, array(
                'attr' => array(
                    'data-help' => 'The name you want to use.',
                ),
            ))
            ->add('description', TextareaType::class, array(
                'required' => false,
            ))
            ->add('orientation', ChoiceType::class, array(
                'choices' => [
                    'Forward' => 'forward',
                    'Reverse' => 'reverse'
                ],
                'multiple' => false,
                'expanded' => true,
            ))
            ->add('quality', ChoiceType::class, array(
                'choices' => [
                    'Bulk' => 'bulk',
                    'Purified' => 'purified',
                ],
                'placeholder' => 'Select a quality',
                'required' => false,
            ))
            ->add('sequence', TextType::class)
            ->add('fivePrimeExtension', TextType::class, array(
                'label' => '5\' Extension',
                'required' => false,
            ))
            ->add('fivePrimeExtensionName', TextType::class, array(
                'label' => '5\' Extension Name',
                'required' => false,
            ))
            ->add('threePrimeExtension', TextType::class, array(
                'label' => '3\' Extension',
                'required' => false,
            ))
            ->add('threePrimeExtensionName', TextType::class, array(
                'label' => '3\' Extension Name',
                'required' => false,
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Primer',
        ));
    }
}