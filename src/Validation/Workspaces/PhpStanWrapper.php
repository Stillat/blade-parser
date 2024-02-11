<?php

namespace Stillat\BladeParser\Validation\Workspaces;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stillat\BladeParser\Errors\ErrorFamily;
use Stillat\BladeParser\Errors\Exceptions\CompilationException;
use Stillat\BladeParser\Errors\Exceptions\UnsupportedNodeException;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Validation\ValidationResult;
use Stillat\BladeParser\Workspaces\Workspace;
use Symfony\Component\Process\Process;

class PhpStanWrapper
{
    /**
     * A temporary directory where Blade files can be compiled.
     */
    protected string $directory = '';

    /**
     * Sets the directory path where blade files will be compiled to.
     *
     * @param  string  $directory  The directory path.
     * @return $this
     */
    public function setDirectory(string $directory): PhpStanWrapper
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Tests if the PHPStan-enabled document validator can run.
     *
     * In order to run, PHPStan must be available within the project's
     * vendor directory. In addition, the `blade.validation.phpstan.enabled`
     * configuration entry must be set to a "truthy" value.
     */
    public static function canRun(): bool
    {
        return config('blade.validation.phpstan.enabled', true) && self::vendorBinExists();
    }

    public static function vendorBinExists(): bool
    {
        return file_exists(base_path('vendor/bin/phpstan'));
    }

    /**
     * Runs analysis on the compiled output of all documents within the provided workspace.
     *
     * @param  Workspace  $workspace  The workspace instance.
     *
     * @throws CompilationException
     * @throws UnsupportedNodeException
     */
    public function checkWorkspace(Workspace $workspace): void
    {
        $workspace->compile($this->directory);
        $result = $this->runAnalysis();

        if (! array_key_exists('files', $result) || count($result['files']) == 0) {
            return;
        }
        $fileErrors = $result['files'];

        foreach ($fileErrors as $path => $errors) {
            $doc = $workspace->getCompiledDocument($path);
            if ($doc === null) {
                continue;
            }

            foreach ($errors['messages'] as $message) {
                $bladeLine = $workspace->getSourceLine($path, $message['line']);
                $node = $doc->findAllNodesStartingOnLine($bladeLine)->allNotOfType(LiteralNode::class)->last();
                if ($node == null) {
                    continue;
                }

                $errMsg = $message['message'];

                $checkMsg = mb_strtolower($errMsg);

                // Filter out some noise.
                if (Str::contains($checkMsg, [
                    'might not be defined', 'syntax error',
                ])) {
                    continue;
                }

                if ($node instanceof DirectiveNode || $node instanceof EchoNode) {
                    $errMsg = trim($errMsg);
                    if (Str::endsWith($errMsg, '.')) {
                        $errMsg = mb_substr($errMsg, 0, -1);
                    }

                    if ($node instanceof DirectiveNode) {
                        $errMsg .= " near [@{$node->content}]";
                    } else {
                        $content = Str::limit(Str::squish(trim($node->content)), 20);
                        $errMsg .= " near {$content}";
                    }
                }

                $validationResult = new ValidationResult($node, $errMsg);
                $validationResult->errorFamily = ErrorFamily::Validation;
                $validationResult->createdFromValidatorClass = get_class();
                $doc->addValidationResult($validationResult);
            }
        }

        $workspace->removeCompiledFiles();
    }

    private function runAnalysis(): array
    {
        $phpStan = base_path('vendor/bin/phpstan');
        $proc = new Process([$phpStan, 'analyse', $this->directory, '--xdebug', '--error-format=json'], base_path());
        $proc->run();

        try {
            return json_decode($proc->getOutput(), true);
        } catch (Exception $e) {
            Log::error($e);
        }

        return [];
    }
}
