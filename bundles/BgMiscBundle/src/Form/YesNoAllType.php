<?php

namespace Bg\MiscBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Created by IntelliJ IDEA.
 * User: Benoît Guchet
 * Date: 30/11/2016
 * Time: 16:39
 */
class YesNoAllType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'choices' => ['Oui' => '1', 'Non' => '0'],
            'required' => false,
            'expanded' => false,
            'attr' => ['class' => 'small-widget'],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->resetViewTransformers();
        $builder->addViewTransformer(new CallbackTransformer(function ($modelVal) {
            if ($modelVal === null)
                return '';
            elseif ($modelVal === false)
                return '0';
            elseif ($modelVal === true)
                return '1';
        }, function ($viewVal) {
            if ($viewVal === '')
                return null;
            elseif ($viewVal === '0')
                return false;
            elseif ($viewVal === '1')
                return true;
        }));
    }

    public function getParent() {
        return ChoiceType::class;
    }

    public function getBlockPrefix()
    {
        return 'yesnoall';
    }
}
