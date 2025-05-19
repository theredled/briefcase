<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 31/10/2019
 * Time: 13:45
 */

namespace Bg\MiscBundle\Twig;

use Twig\Environment;
use Twig\Template;

class TemplateProxy extends Template
{
    protected $template;
    protected $env;
    protected $methodsOutputs = [];

    public function __construct(Template $template, Environment $env)
    {
        $this->template = $template;
        parent::__construct($env);
    }


    public function __call($method, $arguments)
    {
        $level = ob_get_level();
        if ($this->env->isDebug()) {
            ob_start();
        } else {
            ob_start(function () { return ''; });
        }
        try {
            call_user_func_array([$this->template, $method], $arguments);
        } catch (\Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }

        $output = ob_get_clean();

        if (strpos($method, 'block_') !== false) {
            $this->methodsOutputs[$method] = $output;
        }

        return $output;
    }

    public function getBlockOutput($name)
    {
        return $this->methodsOutputs['block_'.$name];
    }

    public function getTemplateName()
    {
        return $this->template ? $this->template->getTemplateName() : 'Blockset TemplateProxy';
    }

    public function getDebugInfo()
    {
        return $this->template ? $this->template->getDebugInfo() : 'Blockset debug info';
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        return $this->template->doDisplay($context, $blocks);
    }
}
