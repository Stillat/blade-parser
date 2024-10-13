<?php

namespace Stillat\BladeParser\Tests;

use Illuminate\Support\Facades\Blade;
use Orchestra\Testbench\TestCase;
use Stillat\BladeParser\Compiler\Compiler;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Document\DocumentFactory;
use Stillat\BladeParser\Errors\ConstructContext;
use Stillat\BladeParser\Errors\ErrorType;
use Stillat\BladeParser\Nodes\BaseNode;
use Stillat\BladeParser\Nodes\CommentNode;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\EchoType;
use Stillat\BladeParser\Nodes\Fragments\FragmentParameter;
use Stillat\BladeParser\Nodes\Fragments\HtmlFragment;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Nodes\Position;
use Stillat\BladeParser\Parser\DocumentParser;
use Stillat\BladeParser\Providers\ValidatorServiceProvider;
use Stillat\BladeParser\ServiceProvider;
use Stillat\BladeParser\Validation\AbstractDocumentValidator;
use Stillat\BladeParser\Validation\BladeValidator;
use Stillat\BladeParser\Validation\ValidatorFactory;
use Stillat\BladeParser\Workspaces\Workspace;

class ParserTestCase extends TestCase
{
    protected ?Compiler $compiler;

    protected function getPackageProviders($app)
    {
        return [
            ValidatorServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        $this->compiler = new Compiler(
            new DocumentParser
        );

        $this->compiler->resetState();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->compiler = null;
    }

    protected function registerDirective($directives)
    {
        if (! is_array($directives)) {
            $directives = [$directives];
        }

        foreach ($directives as $name) {
            Blade::directive($name, function () use ($name) {
                return '__stillat_blade_parser_test_directive_'.$name.'__';
            });
        }
    }

    protected function parser(): DocumentParser
    {
        $parser = new DocumentParser;

        $parser->setDirectiveNames(array_keys(Blade::getCustomDirectives()));

        return $parser;
    }

    protected function parseNodes($template): array
    {
        $parser = new DocumentParser;

        $parser->setDirectiveNames(array_keys(Blade::getCustomDirectives()));

        return $parser->parse($template);
    }

    protected function getDocument($template, array $customComponentTags = []): Document
    {
        $parser = new DocumentParser;
        $parser->registerCustomComponentTags($customComponentTags);
        $parser->setDirectiveNames(array_keys(Blade::getCustomDirectives()))->parse($template);

        return DocumentFactory::makeDocument()->syncFromParser($parser);
    }

    protected function getValidator(array $validators = []): BladeValidator
    {
        $validator = ValidatorFactory::makeBladeValidator();

        if (count($validators) > 0) {
            foreach ($validators as $validatorInstance) {
                if ($validatorInstance instanceof AbstractDocumentValidator) {
                    $validator->addDocumentValidator($validatorInstance);
                } else {
                    $validator->addValidator($validatorInstance);
                }
            }
        }

        return $validator;
    }

    protected function assertEchoContent($node, $expected)
    {
        $this->assertInstanceOf(EchoNode::class, $node);
        $this->assertSame(EchoType::Echo, $node->type);
        $this->assertNotSame('', $node->id);
        $this->assertSame($expected, $node->content);
    }

    protected function assertTripleEchoContent($node, $expected)
    {
        $this->assertInstanceOf(EchoNode::class, $node);
        $this->assertSame(EchoType::TripleEcho, $node->type);
        $this->assertNotSame('', $node->id);
        $this->assertSame($expected, $node->content);
    }

    protected function assertRawEchoNodeContent($node, $expected)
    {
        $this->assertInstanceOf(EchoNode::class, $node);
        $this->assertSame(EchoType::RawEcho, $node->type);
        $this->assertNotSame('', $node->id);
        $this->assertSame($expected, $node->content);
    }

    protected function assertDirectiveName($node, $expected)
    {
        $this->assertInstanceOf(DirectiveNode::class, $node);
        $this->assertNotSame('', $node->id);
        $this->assertSame($expected, $node->content);
    }

    protected function assertDirectiveContent($node, $expected, $paramExpected = null)
    {
        $this->assertInstanceOf(DirectiveNode::class, $node);

        $this->assertNotSame('', $node->id);
        $this->assertSame($expected, $node->content);

        if ($paramExpected != null) {
            $this->assertNotNull($node->arguments);
            $this->assertSame($paramExpected, $node->arguments->content);
        }
    }

    protected function assertLiteralContent($node, $expected)
    {
        $this->assertInstanceOf(LiteralNode::class, $node);
        $this->assertNotSame('', $node->id);
        $this->assertSame($expected, $node->content);
    }

    protected function assertCommentContent($node, $expected)
    {
        $this->assertInstanceOf(CommentNode::class, $node);
        $this->assertNotSame('', $node->id);
        $this->assertSame($expected, $node->content);
    }

    protected function assertStartPosition(Position $position, int $offset, int $line, int $column)
    {
        $this->assertSame($offset, $position->startOffset);
        $this->assertSame($line, $position->startLine);
        $this->assertSame($column, $position->startColumn);
    }

    public function assertEndPosition(Position $position, int $offset, int $line, int $column)
    {
        $this->assertSame($offset, $position->endOffset);
        $this->assertSame($line, $position->endLine);
        $this->assertSame($column, $position->endColumn);
    }

    public function assertClonedBasicDetailsMatch(BaseNode $nodeA, BaseNode $nodeB)
    {
        $this->assertFalse($nodeA === $nodeB);

        $this->assertNotSame($nodeA->id, $nodeB->id);

        $this->assertClonedPositionsMatch($nodeA->position, $nodeB->position);
        $this->assertSame($nodeA->content, $nodeB->content);
    }

    public function assertClonedPositionsMatch(?Position $positionA, ?Position $positionB)
    {
        if ($positionA == null || $positionB == null) {
            return;
        }

        $this->assertFalse($positionB === $positionA);

        $this->assertSame($positionA->startOffset, $positionB->startOffset);
        $this->assertSame($positionA->startLine, $positionB->startLine);
        $this->assertSame($positionA->startColumn, $positionB->startColumn);

        $this->assertSame($positionA->endOffset, $positionB->endOffset);
        $this->assertSame($positionA->endLine, $positionB->endLine);
        $this->assertSame($positionA->endColumn, $positionB->endColumn);
    }

    public function assertDirectivesArePaired(DirectiveNode $a, DirectiveNode $b, string $pairing = ''): void
    {
        $this->assertNotNull($a->isClosedBy, 'Pairing: '.$pairing);
        $this->assertNotNull($b->isOpenedBy, 'Pairing: '.$pairing);

        $this->assertEquals($a->isClosedBy, $b, 'Pairing: '.$pairing);
        $this->assertEquals($b->isOpenedBy, $a, 'Pairing: '.$pairing);
    }

    public function assertComponentsArePaired(ComponentNode $a, ComponentNode $b, string $pairing = ''): void
    {
        $this->assertNotNull($a->isClosedBy, 'Pairing: '.$pairing);
        $this->assertNotNull($b->isOpenedBy, 'Pairing: '.$pairing);

        $this->assertEquals($a->isClosedBy, $b, 'Pairing: '.$pairing);
        $this->assertEquals($b->isOpenedBy, $a, 'Pairing: '.$pairing);
    }

    public function assertHasErrorOnLine(int $line, ErrorType $type, ConstructContext $context)
    {
        $this->assertTrue($this->compiler->hasErrorOnLine($line, $type, $context));
    }

    protected function getWorkspaceDirectory(string $workspace): string
    {
        return __DIR__.'/__fixtures/workspaces/'.$workspace;
    }

    protected function getWorkspace(string $workspaceName): Workspace
    {
        $workspace = new Workspace;
        $workspace->addDirectory($this->getWorkspaceDirectory($workspaceName));

        return $workspace;
    }

    protected function assertFragmentPosition(HtmlFragment $fragment, int $startLine, int $startColumn, int $endLine, int $endColumn)
    {
        $this->assertNotNull($fragment->position);
        $this->assertSame($startLine, $fragment->position->startLine);
        $this->assertSame($startColumn, $fragment->position->startColumn);
        $this->assertSame($endLine, $fragment->position->endLine);
        $this->assertSame($endColumn, $fragment->position->endColumn);
    }

    protected function assertFragmentNamePosition(HtmlFragment $fragment, int $startLine, int $startColumn, int $endLine, int $endColumn)
    {
        $this->assertNotNull($fragment->name);
        $this->assertNotNull($fragment->name->position);
        $this->assertSame($startLine, $fragment->name->position->startLine);
        $this->assertSame($startColumn, $fragment->name->position->startColumn);

        $this->assertSame($endLine, $fragment->name->position->endLine);
        $this->assertSame($endColumn, $fragment->name->position->endColumn);
    }

    protected function assertFragmentContentPosition(HtmlFragment $fragment, int $startLine, int $startColumn, int $endLine, int $endColumn)
    {
        $this->assertNotNull($fragment->innerContent);
        $this->assertNotNull($fragment->innerContent->position);

        $this->assertSame($startLine, $fragment->innerContent->position->startLine);
        $this->assertSame($startColumn, $fragment->innerContent->position->startColumn);

        $this->assertSame($endLine, $fragment->innerContent->position->endLine);
        $this->assertSame($endColumn, $fragment->innerContent->position->endColumn);
    }

    protected function assertFragmentParameterPosition(FragmentParameter $parameter, int $startLine, int $startColumn, int $endLine, int $endColumn)
    {
        $this->assertNotNull($parameter->position);
        $this->assertSame($startLine, $parameter->position->startLine);
        $this->assertSame($startColumn, $parameter->position->startColumn);

        $this->assertSame($endLine, $parameter->position->endLine);
        $this->assertSame($endColumn, $parameter->position->endColumn);
    }
}
