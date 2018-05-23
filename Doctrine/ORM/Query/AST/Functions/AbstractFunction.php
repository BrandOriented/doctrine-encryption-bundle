<?php
declare(strict_types=0);
/*
 * This file is part of the BrandOriented package.
 *
 * (c) Brand Oriented sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Dominik Labudzinski <dominik@labudzinski.com>
 * @name AbstractFunction.php - 23-05-2018 10:45
 */

namespace BrandOriented\Encryption\Doctrine\ORM\Query\AST\Functions;


use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Class AbstractFunction
 */
abstract class AbstractFunction extends FunctionNode
{
    /**
     * @var string
     */
    protected $functionPrototype;
    /**
     * @var array
     */
    protected $literalsMapping = [];
    /**
     * @var array
     */
    protected $literals = [];
    abstract protected function customiseFunction();
    /**
     * Sets function prototype
     *
     * @param string $functionPrototype
     */
    protected function setFunctionPrototype($functionPrototype)
    {
        $this->functionPrototype = $functionPrototype;
    }
    /**
     * Adds new literal mapping
     *
     * @param string $parserMethod
     */
    protected function addLiteralMapping($parserMethod)
    {
        $this->literalsMapping[] = $parserMethod;
    }

    /**
     * @param Parser $parser
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function parse(Parser $parser)
    {
        $this->customiseFunction();
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->feedParserWithLiterals($parser);
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * Feeds given parser with previously set literals
     *
     * @param Parser $parser
     * @throws \Doctrine\ORM\Query\QueryException
     */
    protected function feedParserWithLiterals(Parser $parser)
    {
        $literalsMappingCount = count($this->literalsMapping);
        $lastLitteral = $literalsMappingCount - 1;
        for ($i = 0; $i < $literalsMappingCount; $i++) {
            $parserMethod = $this->literalsMapping[$i];
            $this->literals[$i] = $parser->$parserMethod();
            if ($i < $lastLitteral) {
                $parser->match(Lexer::T_COMMA);
            }
        }
    }
    /**
     * {@inheritDoc}
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        $dispateched = [];
        foreach ($this->literals as $literal) {
            $dispateched[] = $literal->dispatch($sqlWalker);
        }
        return vsprintf($this->functionPrototype, $dispateched);
    }
}