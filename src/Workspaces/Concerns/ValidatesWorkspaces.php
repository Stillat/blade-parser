<?php

namespace Stillat\BladeParser\Workspaces\Concerns;

use Illuminate\Support\Collection;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\AbstractDocumentValidator;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\BladeValidator;
use Stillat\BladeParser\Validation\ValidatorFactory;
use Stillat\BladeParser\Workspaces\Workspace;

trait ValidatesWorkspaces
{
    private ?BladeValidator $validatorInstance = null;

    /**
     * Returns access to an internal `BladeValidator` instance.
     *
     * If a validator instance does not already exist, one will be
     * automatically created by calling the `ValidatorFactory::makeBladeValidator()`
     * method.
     */
    public function validator(): BladeValidator
    {
        if ($this->validatorInstance == null) {
            $this->validatorInstance = ValidatorFactory::makeBladeValidator();
        }

        return $this->validatorInstance;
    }

    /**
     * Includes the core validators on the internal `BladeValidator` instance.
     *
     * If a `BladeValidator` instance does not already exist on the
     * document, a new one will be created automatically via the
     * `ValidatorFactory::makeBladeValidator()` factory method.
     *
     * Under a typical installation environment, this will load
     * the validators that are configured under the config
     * path: `blade.validation.core_validators`
     */
    public function withCoreValidators(): Workspace
    {
        $this->validator()->withCoreValidators();

        return $this;
    }

    /**
     * Adds a single node validator instance to the internal `BladeValidator` instance.
     *
     * @param  AbstractNodeValidator  $validator  The node validator.
     */
    public function addValidator(AbstractNodeValidator $validator): Workspace
    {
        $this->validator()->addValidator($validator);

        return $this;
    }

    /**
     * Adds a single document validator instance to the internal `BladeValidator` instance.
     *
     * @param  AbstractDocumentValidator  $validator  The document validator.
     */
    public function addDocumentValidator(AbstractDocumentValidator $validator): Workspace
    {
        $this->validator()->addDocumentValidator($validator);

        return $this;
    }

    /**
     * Adds a validator instance to the internal `BladeValidator` instance.
     *
     * @param  AbstractNodeValidator  $validator  The validator instance.
     */
    public function withValidator(AbstractNodeValidator $validator): Workspace
    {
        $this->validator()->addValidator($validator);

        return $this;
    }

    /**
     * Adds a list of validator instances to the internal `BladeValidator` instance.
     *
     * @param  AbstractNodeValidator[]  $validators  The validator instances.
     */
    public function withValidators(array $validators): Workspace
    {
        $this->validator()->addValidators($validators);

        return $this;
    }

    /**
     * Returns all validation errors present in the workspace.
     */
    public function getValidationErrors(): Collection
    {
        return collect($this->validationErrors);
    }

    /**
     * Validates all documents within the workspace using the internal `BladeValidator` instance.
     */
    public function validate(): Workspace
    {
        if ($this->validator()->hasValidatorRequiringStructures()) {
            $this->resolveStructures();
        }

        $this->validationErrors = [];

        /** @var Document $document */
        foreach ($this->getDocuments() as $document) {
            $docResults = $document->validateWith($this->validator())->getValidationErrors()->all();

            $this->validationErrors = array_merge($this->validationErrors, $docResults);
        }

        return $this;
    }
}
