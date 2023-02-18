<?php

namespace Stillat\BladeParser\Workspaces\Concerns;

use Illuminate\Support\Collection;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Nodes\Concerns\InteractsWithBladeErrors;

trait ManagesWorkspaceErrors
{
    use InteractsWithBladeErrors;

    /**
     * Retrieves a collection of `BladeError` instances for the workspace.
     */
    public function getErrors(): Collection
    {
        $errors = [];

        /** @var Document $doc */
        foreach ($this->getDocuments() as $doc) {
            $errors = array_merge($errors, $doc->getErrors()->all());
        }

        return collect($errors);
    }
}
