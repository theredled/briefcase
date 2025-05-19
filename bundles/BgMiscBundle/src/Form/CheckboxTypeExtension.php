<?php

namespace Bg\MiscBundle\Form;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Created by IntelliJ IDEA.
 * User: Benoît Guchet
 * Date: 30/12/2016
 * Time: 19:42
 */
class CheckboxTypeExtension extends AbstractTypeExtension
{
    static public function getExtendedTypes()
    {
        return [CheckboxType::class];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('label_before_widget', false);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['label_before_widget'] = $options['label_before_widget'];
        $view->vars['required'] = false;
    }
}
