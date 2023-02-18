<?php

namespace Stillat\BladeParser\Workspaces;

use Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Stillat\BladeParser\Contracts\PathFormatter;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Errors\BladeError;
use Stillat\BladeParser\Support\Utilities\Paths;
use Stillat\BladeParser\Workspaces\Concerns\CompilesWorkspace;
use Stillat\BladeParser\Workspaces\Concerns\ManagesWorkspaceErrors;
use Stillat\BladeParser\Workspaces\Concerns\ProxiesDocumentCalls;
use Stillat\BladeParser\Workspaces\Concerns\ValidatesWorkspaces;

class Workspace
{
    use ValidatesWorkspaces, ProxiesDocumentCalls, ManagesWorkspaceErrors,
        CompilesWorkspace;

    /**
     * Indicates if the workspace should resolve structures on document instances.
     *
     * This will be set automatically by the workspace validation
     * features if any registered validator has the `$requiresStructures`
     * property set to `true`.
     */
    private bool $shouldResolveStructures = false;

    /**
     * The PathFormatter implementation used by the workspace.
     */
    protected PathFormatter $pathFormatter;

    /**
     * A list of validation errors across all Documents within the workspace.
     *
     * @var BladeError[]
     */
    protected array $validationErrors = [];

    /**
     * The extensions that will be parsed as Blade templates.
     *
     * @var string[]
     */
    protected array $bladeExtensions = ['.blade.php'];

    /**
     * @var Document[]
     */
    protected array $documents = [];

    /**
     * The paths of all Blade templates added to the workspace.
     *
     * @var string[]
     */
    protected array $filePaths = [];

    /**
     * The directories discovered within the workspace.
     *
     * @var string[]
     */
    protected array $workspaceDirectories = [];

    public function __construct()
    {
        $this->withPathFormatter(new TempPathFormatter);
    }

    protected function getFiles(string $directory): Generator
    {
        $directoryIterator = new RecursiveDirectoryIterator($directory);

        yield from new RecursiveIteratorIterator($directoryIterator);
    }

    /**
     * Returns the number of template files discovered in the workspace.
     */
    public function getFileCount(): int
    {
        return count($this->filePaths);
    }

    /**
     * Recursively adds all Blade templates to the workspace
     * discovered within the provided directory.
     *
     * @param  string  $directory The path.
     * @return $this
     */
    public function addDirectory(string $directory): Workspace
    {
        /** @var SplFileInfo $file */
        foreach ($this->getFiles($directory) as $file) {
            if ($file->isDir()) {
                $path = Paths::normalizePathWithTrailingSlash($file->getPath());
                if (! in_array($path, $this->workspaceDirectories)) {
                    $this->workspaceDirectories[] = $path;
                }

                continue;
            }
            if (in_array($file->getFilename(), ['.', '..'])) {
                continue;
            }

            $this->addFile($file->getPathname());
        }

        return $this;
    }

    /**
     * Adds a single Blade template to the workspace.
     *
     * If the provided path does not end with a
     * configured Blade extension, the file
     * will *not* be added to the workspace.
     *
     * @param  string  $path The file path.
     * @return $this
     */
    public function addFile(string $path): Workspace
    {
        $this->filePaths[] = $path;

        if (! Str::endsWith(mb_strtolower($path), $this->bladeExtensions)) {
            return $this;
        }

        $dirName = Paths::normalizePathWithTrailingSlash(Str::beforeLast(Paths::normalizePath($path), '/'));

        if (! in_array($dirName, $this->workspaceDirectories)) {
            $this->workspaceDirectories[] = $dirName;
        }

        $document = Document::fromText(file_get_contents($path), $path);

        if ($this->shouldResolveStructures) {
            $document->resolveStructures();
        }

        $this->documents[] = $document;

        return $this;
    }

    /**
     * Returns the number of `Document` instances within the workspace.
     */
    public function getDocumentCount(): int
    {
        return count($this->documents);
    }

    /**
     * Returns a collection of `Document` instances.
     */
    public function getDocuments(): Collection
    {
        return collect($this->documents);
    }

    /**
     * Performs cleanup actions on the workspace,
     * such as removing any temporary files/etc
     */
    public function cleanUp(): void
    {
        $this->removeCompiledFiles();
    }
}
