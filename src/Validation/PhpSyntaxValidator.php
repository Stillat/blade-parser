<?php

namespace Stillat\BladeParser\Validation;

use Exception;
use Illuminate\Support\Str;
use ParseError;
use Stillat\BladeParser\Compiler\AppendState;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Document\DocumentCompilerOptions;
use Stillat\BladeParser\Document\DocumentOptions;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Providers\ValidatorServiceProvider;

class PhpSyntaxValidator
{
    /**
     * The compiler options.
     */
    private DocumentCompilerOptions $compilerOptions;

    /**
     * A mapping of the Blade template lines to the emitted PHP output.
     */
    private array $sourceMap = [];

    public function __construct()
    {
        $this->compilerOptions = new DocumentCompilerOptions();
        $this->compilerOptions->throwExceptionOnUnknownComponentClass = false;
        $this->compilerOptions->ignoreDirectives = ValidatorServiceProvider::getIgnoreDirectives();
        $this->compilerOptions->appendCallbacks[] = function (AppendState $state) {
            for ($i = $state->beforeLineNumber; $i <= $state->afterLineNumber; $i++) {
                $this->sourceMap[$i] = $state->node->position->startLine;
            }
        };
    }

    /**
     * Resets the validator's internal state.
     */
    protected function resetState(): void
    {
        $this->sourceMap = [];
    }

    /**
     * Checks the provided document for any potential PHP syntax errors.
     *
     * @param  Document  $document  The document instance.
     * @param  int|null  $originalLine  An optional line number that will be used instead of any reported PHP line numbers.
     */
    public function checkDocument(Document $document, ?int $originalLine = null): PhpSyntaxValidationResult
    {
        $this->resetState();
        $syntaxResult = new PhpSyntaxValidationResult();

        try {
            $compiled = $document->compile($this->compilerOptions);

            $error = $this->checkForErrors($compiled);

            if ($error == null || Str::contains($error->getMessage(), 'end of input')) {
                return $syntaxResult;
            }

            $targetLine = $error->getLine() - 1;
            if (! array_key_exists($targetLine, $this->sourceMap)) {
                return $syntaxResult;
            }

            /** @var AbstractNode $interesting */
            $interesting = $document->findAllNodesStartingOnLine($targetLine)->allNotOfType(LiteralNode::class)->last();

            $message = '';

            if ($interesting != null) {
                $subjectString = Str::limit(Str::squish(trim($interesting->toString())), 20);

                if ($interesting instanceof DirectiveNode) {
                    $subjectString = '[@'.$interesting->content.']';
                }

                $useLine = $interesting->position->startLine;

                if ($originalLine != null) {
                    $useLine = $originalLine;
                }
                $message = ' near ['.$subjectString.']';

                if ($originalLine == null || ($targetLine - 1) > 0) {
                    $message .= ' (from line '.$useLine.')';
                }
            }

            $syntaxResult->detectedErrors = true;
            $syntaxResult->node = $interesting;
            $syntaxResult->errorLine = $targetLine;
            $syntaxResult->errorMessage = "Anticipated PHP compilation error: [{$error->getMessage()}]{$message}";
        } catch (Exception $ex) {
        }

        return $syntaxResult;
    }

    /**
     * Checks the provided content for any potential PHP syntax errors.
     *
     * @param  string  $content  The value to check.
     * @param  int|null  $originalLine  An optional line number that will be used instead of any reported PHP line number.
     */
    public function checkString(string $content, ?int $originalLine = null): PhpSyntaxValidationResult
    {
        return $this->checkDocument(
            Document::fromText($content, documentOptions: new DocumentOptions(ignoreDirectives: ValidatorServiceProvider::getIgnoreDirectives())), $originalLine
        );
    }

    /**
     * Attempts to locate any PHP syntax errors within the provided content.
     *
     * @param  string  $content  The PHP content.
     */
    private function checkForErrors(string $content): ?ParseError
    {
        $phpContent = "?><?php return; ?>\n".$content;
        try {
            $valResult = eval($phpContent);
        } catch (\ParseError $err) {
            return $err;
        }

        return null;
    }
}
