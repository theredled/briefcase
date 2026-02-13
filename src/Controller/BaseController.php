<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 13/02/2026
 * Time: 07:29
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class BaseController extends AbstractController
{
    protected function denyAccessUnlessExists($entity)
    {
        if (!$entity)
            throw $this->createNotFoundException('L\'élément n\'existe pas.');
    }
}
