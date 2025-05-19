<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 31/10/2019
 * Time: 02:07
 */

namespace Bg\MiscBundle\Form;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class DismemberedButtonTypeExtension extends AbstractTypeExtension
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $dismembered = $form->getRoot()->getConfig()->getOption('dismembered');

        if ($dismembered) {
            $view->vars['attr']['form'] = $form->getRoot()->getName();
        }
    }

    static public function getExtendedTypes()
    {
        return [ButtonType::class];
    }
}
