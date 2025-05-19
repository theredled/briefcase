<?php

namespace Bg\MiscBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Created by IntelliJ IDEA.
 * User: Benoît Guchet
 * Date: 30/11/2016
 * Time: 16:39
 */
class YesNoType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'choices' => ['Oui' => true, 'Non' => false],
            'expanded' => true,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->setAttribute('required', $options['required']);
    }

    public function getParent() {
        return ChoiceType::class;
    }
}
