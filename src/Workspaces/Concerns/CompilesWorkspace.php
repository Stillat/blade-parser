<?php

namespace Stillat\BladeParser\Workspaces\Concerns;

use Illuminate\Support\Str;
use Stillat\BladeParser\Compiler\AppendState;
use Stillat\BladeParser\Contracts\PathFormatter;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Document\DocumentCompilerOptions;
use Stillat\BladeParser\Errors\Exceptions\CompilationException;
use Stillat\BladeParser\Errors\Exceptions\UnsupportedNodeException;
use Stillat\BladeParser\Support\Utilities\Paths;
use Stillat\BladeParser\Workspaces\Workspace;

trait CompilesWorkspace
{
    /**
     * A list of all files created by the workspace compiler.
     *
     * @var string[]
     */
    protected array $compiledFiles = [];

    /**
     * The path to the last temporary directory used.
     *
     * This is set by the `compile(string $outputDirectory)`
     * method, and can help with any cleanup operations.
     */
    protected string $lastTempDirectory = '';

    /**
     * A mapping of compiled template paths and their original Document instances.
     *
     * The key of this array will be the compiled path,
     * and the corresponding value be the `Document`.
     */
    protected array $compiledDocuments = [];

    /**
     * Provides a source mapping for compiled documents.
     *
     * The key of this array will be the compiled path.
     * The value will be an array whose key corresponds
     * to the line in the output PHP, and its value
     * will be the node's starting line within
     * the original Blade template file.
     */
    protected array $docSourceMaps = [];

    /**
     * The compiler options to use when compiling documents.
     *
     * If no options are specified, a set of defaults
     * will be applied automatically when compiling.
     */
    protected ?DocumentCompilerOptions $compilerOptions = null;

    /**
     * Sets the PathFormatter implementation used by the workspace.
     *
     * PathFormatter implementations are used to determine
     * what the final output file paths look like.
     *
     * @param  PathFormatter  $formatter The path formatter.
     */
    public function withPathFormatter(PathFormatter $formatter): Workspace
    {
        $this->pathFormatter = $formatter;

        return $this;
    }

    /**
     * Attempts to remove all compiled files produced by the workspace.
     */
    public function removeCompiledFiles(): Workspace
    {
        foreach ($this->compiledFiles as $compiledPath => $originalFile) {
            @unlink($compiledPath);
        }

        return $this;
    }

    /**
     * Sets the workspace compiler options.
     *
     * @param  DocumentCompilerOptions  $options The compiler options.
     */
    public function withCompilerOptions(DocumentCompilerOptions $options): Workspace
    {
        $this->compilerOptions = $options;

        return $this;
    }

    /**
     * Removes any custom compiler options and restores the workspace to defaults.
     */
    public function withDefaultCompilerOptions(): Workspace
    {
        $this->compilerOptions = null;

        return $this;
    }

    /**
     * Returns the currently configured compiler options.
     *
     * If no custom compiler options were configured, a
     * set of default options will be created.
     */
    public function getCompilerOptions(): DocumentCompilerOptions
    {
        if ($this->compilerOptions != null) {
            return $this->compilerOptions;
        }

        $options = new DocumentCompilerOptions();
        $options->throwExceptionOnUnknownComponentClass = false;

        return $options;
    }

    /**
     * Compiles all discovered Blade templates within the workspace.
     *
     * @param  string  $outputDirectory Where to store compiled files.
     *
     * @throws CompilationException
     * @throws UnsupportedNodeException
     */
    public function compile(string $outputDirectory): void
    {
        $outputDirectory = Paths::normalizePathWithTrailingSlash($outputDirectory);
        $this->lastTempDirectory = $outputDirectory;

        /** @var Document $doc */
        foreach ($this->getDocuments() as $doc) {
            $compilePath = $outputDirectory.$this->pathFormatter->getPath($doc);
            $options = $this->getCompilerOptions();

            $this->docSourceMaps[$compilePath] = [];
            $options->appendCallbacks[] = function (AppendState $state) use ($compilePath) {
                for ($i = $state->beforeLineNumber; $i <= $state->afterLineNumber; $i++) {
                    $this->docSourceMaps[$compilePath][$i] = $state->node->position->startLine;
                }
            };
            $result = $doc->compile($options);

            $dir = Paths::normalizePathWithTrailingSlash(Str::afterLast(Paths::normalizePath($compilePath), '/'));

            if (! file_exists($dir)) {
                @mkdir($dir, 0755, true);
            }

            file_put_contents($compilePath, $result);
            $this->compiledFiles[$compilePath] = $doc->getFilePath();
            $this->compiledDocuments[$compilePath] = $doc;
        }
    }

    /**
     * Retrieves the original Blade template line number for the given compiled PHP line.
     *
     * @param  string  $docPath The compiled path.
     * @param  int  $phpLine The target PHP line.
     */
    public function getSourceLine(string $docPath, int $phpLine): ?int
    {
        if (! array_key_exists($docPath, $this->docSourceMaps)) {
            return null;
        }
        if (! array_key_exists($phpLine, $this->docSourceMaps[$docPath])) {
            return null;
        }

        return $this->docSourceMaps[$docPath][$phpLine];
    }

    /**
     * Retrieves a Document instance using the provided compiled path name.
     *
     * @param  string  $path The compiled path.
     */
    public function getCompiledDocument(string $path): ?Document
    {
        if (! array_key_exists($path, $this->compiledDocuments)) {
            return null;
        }

        return $this->compiledDocuments[$path];
    }
}
