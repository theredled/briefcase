<?php

/**
 *  Fork de gregwar/formbundle
 */

namespace Bg\MiscBundle\Form;

use Exception;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Data transformation class
 *
 * @author Gregwar <g.passault@gmail.com>
 */
class EntityToLabelTransformer implements DataTransformerInterface
{
    private $multiple;

    public function __construct($multiple)
    {
        $this->multiple = $multiple;
    }

    public function transform($data)
    {
        if (null === $data) {
            return null;
        }

        if (!$this->multiple) {
            return $this->transformSingleEntity($data);
        }

        $return = array();

        foreach ($data as $element) {
            $return[] = $this->transformSingleEntity($element);
        }

        return implode(', ', $return);
    }

    protected function transformSingleEntity($data)
    {
        return (string)$data;
    }

    public function reverseTransform($data)
    {
        throw new Exception('Le champ est en lecture seule');
    }

    protected function reverseTransformSingleEntity($data)
    {
        throw new Exception('Le champ est en lecture seule');
    }
}
