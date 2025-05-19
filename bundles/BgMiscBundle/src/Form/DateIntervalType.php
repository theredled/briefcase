<?php

namespace Bg\MiscBundle\Form;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Created by IntelliJ IDEA.
 * User: Benoît Guchet
 * Date: 24/01/2017
 * Time: 00:31
 */
class DateIntervalType extends NumberType
{
    public function getBlockPrefix() {
        return 'dateinterval';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $builder->resetViewTransformers();
        $builder->addViewTransformer(new DateIntervalViewTransformer());
    }
}
