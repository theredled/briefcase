<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 31/10/2019
 * Time: 02:07
 */

namespace Bg\MiscBundle\Form;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DismemberedFormTypeExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('dismembered', false);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $isCsrfToken = (!$form->getParent() and isset($view->parent->vars['dismembered']));
        $dismembered = ($form->getRoot()->getConfig()->getOption('dismembered')
            or ($isCsrfToken and $view->parent->vars['dismembered']));

        if (!$form->getParent() and !$isCsrfToken) {
            $view->vars['attr']['id'] = $view->vars['name'];
            $view->vars['dismembered'] = $dismembered;
            $view->vars['render_rest'] = !$dismembered;
        }
        elseif ($dismembered) {
            $view->vars['attr']['form'] = $isCsrfToken
                ? $view->parent->vars['name']
                : $form->getRoot()->getName();
        }
    }

    static public function getExtendedTypes()
    {
        return [FormType::class];
    }
}
