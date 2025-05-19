<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 2019-06-13
 * Time: 21:36
 */

namespace Bg\MiscBundle\Form;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReadOnlyEntityType extends AbstractType
{
    protected $registry;

    public function __construct(RegistryInterface $registry = null)
    {
        $this->registry = $registry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new EntityToLabelTransformer($options['multiple']));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'hidden' => false,
            'disabled' => true,
            'multiple' => false,
            'attr' => ['class' => 'readonly-entity-field'],
        ));
    }

    public function getParent()
    {
        return TextType::class;
    }
}
