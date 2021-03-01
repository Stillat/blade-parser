<?php

namespace Stillat\BladeParser\Analyzers;

use PhpParser\Lexer\Emulative;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Parser\Php7;

class RuntimeTypes
{
    const TYPE_FUNCTION_CALL = 'functionCall';
    const TYPE_STRING = 'string';

    /**
     * The Php7 parser instance.
     *
     * @var Php7
     */
    private $parser = null;

    /**
     * The lexer instance.
     *
     * @var Emulative
     */
    private $lexer = null;

    protected $typeCache = [];

    public function __construct()
    {
        $this->lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);

        $this->parser = new Php7($this->lexer);
    }

    public function testType($input, $type)
    {
        $analyzedType = $this->inferType($input);

        if ($analyzedType === null) {
            return false;
        }

        return $analyzedType === $type;
    }

    public function inferType($input)
    {
        $inputHash = md5($input);

        if (array_key_exists($inputHash, $this->typeCache) === false) {
            $statements = $this->parser->parse('<?php ('.$input.');');

            if ($statements === null || count($statements) === 0) {
                $this->typeCache[$inputHash] = null;

                return null;
            }

            $firstEntry = $statements[0];

            if (($firstEntry instanceof Expression) === false) {
                $this->typeCache[$inputHash] = null;

                return null;
            }

            $expression = $firstEntry->expr;

            if ($expression instanceof FuncCall) {
                $this->typeCache[$inputHash] = self::TYPE_FUNCTION_CALL;

                return self::TYPE_FUNCTION_CALL;
            }

            if ($expression instanceof String_) {
                $this->typeCache[$inputHash] = self::TYPE_STRING;

                return self::TYPE_STRING;
            }

            $this->typeCache[$inputHash] = null;

            return null;
        }

        return $this->typeCache[$inputHash];
    }
}
