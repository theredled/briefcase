<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 2019-06-10
 * Time: 23:32
 */

namespace Bg\MiscBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LazyLoadedChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'url',
        ]);

        $resolver->setDefaults([
            'multiple' => false,
            'required' => false,
            /*'choice_loader' => new CallbackChoiceLoader(function() {

            }),*/
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['url'] = $options['url'];
    }

    public function getParent()
    {
        return EntityType::class;
    }

    public function getName()
    {
        return 'lazy_loaded_choice';
    }
}
