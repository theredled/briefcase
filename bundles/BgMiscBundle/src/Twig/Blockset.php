<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 31/10/2019
 * Time: 14:22
 */

namespace Bg\MiscBundle\Twig;

use Twig\Environment;
use Twig\Error\Error;
use Twig\Markup;
use Twig\Template;

/**
 * Class Blockset
 * @package BgBundle\Twig
 *
 * Un blockset est utilisé pour qu'une template Twig puisse comporter plusieurs blocs qui devront être
 *   utilisées séparément en terme de code (trop séparément pour être gérés nativement par Twig).
 *
 * Exemples :
 * - le sujet d'un email + son contenu
 * - le titre d'un onglet + son contenu
 *
 * Utilisation via Twig:
 * ```twig
 *  {% set bs = blockset('path/to/tpl.twig', {variables à passer si besoin}) %}
 *  {{ bs.getBlock('block_1') }}
 *  XXXXXX
 *  XXXX
 *  {{ bs.getBlock('block_2') }}
 * ```
 *
 * Utilisation via un controlleur:
 * ```php
  * $bs = $this->getBlockset('path/to/tpl.twig', [variables à passer si besoin]);
  * $blockContentA = $bs->getBlock('block_A');
  * $blockContentB = $bs->getBlock('block_B');
  * ```
  *
 *
 * Utilisation plus générale via PHP:
 * ```php
 * $bs = Blockset::build($twigService, $twigService->getGlobals(), 'path/to/tpl.twig', [variables à passer si besoin]);
 * $blockContentA = $bs->getBlock('block_A');
 * $blockContentB = $bs->getBlock('block_B');
 * ```
 *
 * Notes :
 * - Le contenu final des blocs est généré au moment de l'appel à build(), et non getBlock().
 */
class Blockset
{
    protected $blocks = [];
    protected $vars = [];

    public function __construct($vars = [])
    {
        $this->vars = $vars;
    }

    public function getVar($name, $default = null)
    {
        return isset($this->vars[$name]) ? $this->vars[$name] : $default;
    }

    public function addBlock($name, $content)
    {
        $this->blocks[$name] = $content;
    }

    public function getBlock($name, $default = null)
    {
        if (!$this->hasBlock($name)) {
            if ($default !== null)
                return $default;
            throw new Error('Block "'.$name.'" was not found.');
        }
        return $this->blocks[$name] ? new Markup($this->blocks[$name], 'UTF-8') : '';
    }

    public function hasBlock($name)
    {
        return isset($this->blocks[$name]);
    }

    public function blockIsNotEmpty($name)
    {
        return isset($this->blocks[$name]) and trim($this->blocks[$name]);
    }

    public function __call($name, $args)
    {
        return $this->getBlock($name);
    }

    static protected function renderTemplateWithBlocks(Environment $env, Template $template, array $context,
        array $blocks)
    {
        $level = ob_get_level();
        if ($env->isDebug()) {
            ob_start();
        } else {
            ob_start(function () {
                return '';
            });
        }
        try {
            $template->display($context, $blocks);
        } catch (\Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }

        return ob_get_clean();
    }

    static public function build(Environment $env, array $context, string $path, array $vars = []): Blockset
    {
        $context = array_merge($context, $vars);
        $template = $env->loadTemplate($path);
        $blocks = $template->getBlocks();
        $templateProxy = new TemplateProxy($template, $env);

        $proxyBlocks = array_map(function ($block) use ($templateProxy) {
            return [$templateProxy, $block[1]];
        }, $blocks);

        self::renderTemplateWithBlocks($env, $template, $context, $proxyBlocks);

        $blockset = new static($vars);
        foreach ($blocks as $name => $callable)
            $blockset->addBlock($name, $templateProxy->getBlockOutput($name));

        return $blockset;
    }
}
