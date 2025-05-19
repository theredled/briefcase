<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 21/11/2018
 * Time: 00:56
 */

namespace Bg\MiscBundle\old;

use Assetic\Factory\AssetFactory;
use Assetic\ValueSupplierInterface;
use Symfony\Bundle\AsseticBundle\Twig\AsseticExtension;
use Symfony\Component\Templating\TemplateNameParserInterface;

class CustomAsseticExtension extends AsseticExtension
{
    private $useController;
    private $templateNameParser;
    private $enabledBundles;

    public function __construct(AssetFactory $factory, TemplateNameParserInterface $templateNameParser, $useController = false,
        $functions = array(), $enabledBundles = array(), ValueSupplierInterface $valueSupplier = null)
    {
        parent::__construct($factory, $templateNameParser, $useController, $functions, $enabledBundles, $valueSupplier);

        $this->useController = $useController;
        $this->templateNameParser = $templateNameParser;
        $this->enabledBundles = $enabledBundles;
    }

    public function getName() {
        return 'custom_twig_extension';
    }

    public function getTokenParsers()
    {
        return array(
            $this->createTokenParser('javascripts2', 'js/*.js'),
            $this->createTokenParser('stylesheets2', 'css/*.css'),
            $this->createTokenParser('image2', 'images/*', true),
        );
    }

    private function createTokenParser($tag, $output, $single = false)
    {
        $tokenParser = new CustomAsseticTokenParser($this->factory, $tag, $output, $single, array('package'));
        $tokenParser->setTemplateNameParser($this->templateNameParser);
        $tokenParser->setEnabledBundles($this->enabledBundles);

        return $tokenParser;
    }

}
