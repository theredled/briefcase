<?php

namespace Bg\MiscBundle\Form;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Created by IntelliJ IDEA.
 * User: Benoît Guchet
 * Date: 30/12/2016
 * Time: 19:42
 */
class FileTypeExtension extends AbstractTypeExtension
{
    static public function getExtendedTypes()
    {
        return [FileType::class];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['thumb_url', 'full_url', 'editable', 'is_image']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars = array_replace($view->vars, [
            'type' => 'file',
            'value' => $form->getViewData(),
            'editable' => !empty($options['editable']),
            'is_image' => !empty($options['is_image']),
            'thumb_url' => !empty($options['thumb_url']) ? $options['thumb_url'] : '',
            'full_url' => !empty($options['full_url']) ? $options['full_url'] : '',
        ]);
    }
}
