<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 21/11/2018
 * Time: 01:02
 */

namespace Bg\MiscBundle\old;

use Assetic\Asset\AssetInterface;
use Symfony\Bundle\AsseticBundle\Twig\AsseticNode;
use Twig_Compiler;

class CustomAsseticNode extends AsseticNode
{
    protected function compileAsset(Twig_Compiler $compiler, AssetInterface $asset, $name)
    {
        $vars = $asset->getVars();
        if ($vars) {
            $compiler->write("// check variable conditions\n");

            foreach ($vars as $var) {
                $compiler
                    ->write("if (!isset(\$context['assetic']['vars']['$var'])) {\n")
                    ->indent()
                    ->write("throw new \RuntimeException(sprintf('The asset \"".$name."\" expected variable \"".$var."\" to be set, but got only these vars: %s. Did you set-up a value supplier?', isset(\$context['assetic']['vars']) && \$context['assetic']['vars'] ? implode(', ', \$context['assetic']['vars']) : '# none #'));\n")
                    ->outdent()
                    ->write("}\n")
                ;
            }

            $compiler->raw("\n");
        }

        $compiler
            ->write('$context["sourcePath"] = ')
            ->string("/".$asset->getSourcePath())
            ->raw(";\n")
        ;

        $compiler
            ->write("// asset \"$name\"\n")
            ->write('$context[')
            ->repr($this->getAttribute('var_name'))
            ->raw('] = ')
        ;

        $this->compileAssetUrl($compiler, $asset, $name);

        $compiler
            ->raw(";\n")
            ->subcompile($this->getNode('body'))
        ;
    }
}
