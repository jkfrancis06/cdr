<?php


namespace App\DQL;


use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Lexer;

class ToChar extends \Doctrine\ORM\Query\AST\Functions\FunctionNode
{

    public $timestamp = null;
    public $pattern = null;

    /**
     * @inheritDoc
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'to_char('.$this->timestamp->dispatch($sqlWalker) . ', ' . $this->pattern->dispatch($sqlWalker) . ')';
    }

    /**
     * @inheritDoc
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->timestamp = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->pattern = $parser->ArithmeticPrimary(); // I'm not sure about `ArithmeticPrimary()` but it works. Post a comment, if you know more details!
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

}