<?php

namespace Bg\MiscBundle\Doctrine;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * GetVarFunction ::=
 *     "GETVAR" "(" ArithmeticPrimary ")"
 */
class GetVarFunction extends FunctionNode
{
    protected $expression = null;
    protected $varname = null;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->varname = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return '(@'.$this->varname->dispatch($sqlWalker).')';
    }
}