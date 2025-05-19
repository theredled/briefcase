<?php

namespace Bg\MiscBundle\Form;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Created by IntelliJ IDEA.
 * User: Benoît Guchet
 * Date: 30/12/2016
 * Time: 19:42
 */
class ChoiceTypeExtension extends AbstractTypeExtension
{
    static public function getExtendedTypes()
    {
        return [ChoiceType::class];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('placeholder', '---');
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
    }
}
