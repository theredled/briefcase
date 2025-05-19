<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 2019-06-11
 * Time: 00:40
 */

namespace Bg\MiscBundle\Form;

use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;

class LazyLoadingChoiceLoader extends CallbackChoiceLoader
{
    protected $chosen = [];
    protected $visibleChoiceList = null;
    protected $fullChoiceList = null;

    public function loadValuesForChoices(array $choices, $value = null)
    {
        if (empty($choices))
            return [];

        $this->chosen = $choices;

        $values = [];
        foreach ($choices as $c)
            $values[] = $c ? (string)$c->getId() : null;

        return $values;
    }

    public function loadChoicesForValues(array $values, $value = null)
    {
        if (empty($values))
            return [];

        return $this->loadChoiceList($value, true)->getChoicesForValues($values);
    }

    public function loadVisibleChoiceList($value = null) {
        if (null !== $this->visibleChoiceList)
            return $this->visibleChoiceList;

        return $this->visibleChoiceList = new ArrayChoiceList($this->chosen, $value);
    }

    public function loadChoiceList($value = null, $getFull = false)
    {
        return $getFull ? parent::loadChoiceList($value) : $this->loadVisibleChoiceList($value);
    }

}
