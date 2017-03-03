<?php

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class StrainGmoType extends AbstractType
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
        $strainId = $builder->getData()->getId() ? $builder->getData()->getId() : 0;

        $builder
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('genotype', TextareaType::class, [
                'required' => false,
            ])
            ->add('strainPlasmids', CollectionType::class, [
                'entry_type' => StrainPlasmidType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
            ])
            ->add('parents', CollectionType::class, [
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => 'AppBundle\Entity\Strain',
                    'choice_label' => 'fullName',
                    'placeholder' => '-- select a parent --',
                    'query_builder' => function (EntityRepository $er) use ($strainId) {
                        return $er->createQueryBuilder('strain')
                            ->leftJoin('strain.tubes', 'tubes')
                            ->leftJoin('tubes.project', 'project')
                            ->leftJoin('project.members', 'members')
                            ->where('members = :user')
                                ->setParameter('user', $this->tokenStorage->getToken()->getUser())
                            ->andWhere('strain.id <> :id')
                                ->setParameter('id', $strainId)
                            ->orderBy('strain.autoName', 'ASC');
                    },
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Strain',
        ]);
    }

    public function getParent()
    {
        return StrainType::class;
    }
}
