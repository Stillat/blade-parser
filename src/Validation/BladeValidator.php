<?php

namespace Stillat\BladeParser\Validation;

use Illuminate\Support\Collection;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Nodes\AbstractNode;

class BladeValidator
{
    /**
     * A list of configured node validators.
     *
     * @var AbstractNodeValidator[]
     */
    protected array $validators = [];

    /**
     * A list of configured document validator instances.
     *
     * @var AbstractDocumentValidator[]
     */
    protected array $documentValidators = [];

    /**
     * A list of strings representing all previously observed results.
     *
     * This is used to help reduce noise if multiple validation
     * results (of the same type) are reported for the same line.
     *
     * @var string[]
     */
    protected array $resultSlugs = [];

    /**
     * A list of a node validation results.
     *
     * @var ValidationResult[]
     */
    protected array $results = [];

    /**
     * A list of all document validation results.
     *
     * @var ValidationResult[]
     */
    protected array $documentResults = [];

    /**
     * Indicates if any registered validators require structural analysis.
     *
     * Validator implementations may indicate that they require a
     * document instance to resolve its structures in order to
     * function correctly. When they do, the `resolveStructures()`
     * method is automatically invoked on each document instance.
     */
    protected bool $validatorsRequireStructures = false;

    /**
     * A list of all core node validator instances.
     *
     * These are populated with the entries contained within
     * the `blade.validation.core_validators` config entry.
     *
     * @var AbstractNodeValidator[]
     */
    protected array $coreValidators = [];

    /**
     * A list of all core document validator instances.
     *
     * These are populated with the entries contained within
     * the `blade.validation.core_validators` config entry.
     *
     * @var AbstractDocumentValidator[]
     */
    protected array $coreDocumentValidators = [];

    /**
     * A list of all registered validator instances.
     *
     * This list is used to help prevent multiple validator instances
     * of the same type from being added to the validator.
     */
    protected array $registeredValidators = [];

    /**
     * Sets the internal core validator instances.
     *
     * By default, these are loaded from the `blade.validation.core_validators`
     * configuration entry.
     *
     * @internal
     *
     * @param  AbstractNodeValidator[]  $instances  The node validator instances.
     * @return $this
     */
    public function setCoreValidatorInstances(array $instances): BladeValidator
    {
        $this->coreValidators = $instances;

        return $this;
    }

    /**
     * Sets the internal core document validator instances.
     *
     * By default, these are loaded from the `blade.validation.core_validators`
     * configuration entry.
     *
     * @internal
     *
     * @param  AbstractDocumentValidator[]  $instances  The document validator instances.
     * @return $this
     */
    public function setCoreDocumentValidatorInstances(array $instances): BladeValidator
    {
        $this->coreDocumentValidators = $instances;

        return $this;
    }

    /**
     * Adds all the core validator instances to the current `BladeValidator` instance.
     */
    public function withCoreValidators(): BladeValidator
    {
        return $this->addValidators($this->coreValidators)->addDocumentValidators($this->coreDocumentValidators);
    }

    /**
     * Returns the total number of validator instances registered with `BladeValidator` instance.
     */
    public function getValidatorCount(): int
    {
        // The component name validator just runs automatically, hence +1.
        return count($this->documentValidators) + count($this->validators) + 1;
    }

    /**
     * Returns a collection of all registered validator instances.
     */
    public function getValidators(): Collection
    {
        return collect(array_merge($this->validators, $this->documentValidators));
    }

    /**
     * Returns a collection containing only the registered node validator instances.
     */
    public function getNodeValidators(): Collection
    {
        return collect($this->validators);
    }

    /**
     * Returns a collection containing only the registered document validator instances.
     */
    public function getDocumentValidators(): Collection
    {
        return collect($this->documentValidators);
    }

    /**
     * Indicates if any registered validator instances require structures to be resolved.
     *
     * @internal
     */
    public function hasValidatorRequiringStructures(): bool
    {
        return $this->validatorsRequireStructures;
    }

    /**
     * Tests if the `BladeValidator` instance contains the provided validator.
     *
     * @param  AbstractNodeValidator  $validator  The validator to check.
     */
    public function hasValidatorInstance(AbstractNodeValidator $validator): bool
    {
        $class = get_class($validator);

        if (! array_key_exists($class, $this->registeredValidators)) {
            return false;
        }

        return $this->registeredValidators[$class] === $validator;
    }

    /**
     * Tests if the `BladeValidator` instance contains the provided validator.
     *
     * @param  AbstractDocumentValidator  $validator  The validator to check.
     */
    public function hasDocumentValidatorInstance(AbstractDocumentValidator $validator): bool
    {
        $class = get_class($validator);

        if (! array_key_exists($class, $this->registeredValidators)) {
            return false;
        }

        return $this->registeredValidators[$class] === $validator;
    }

    /**
     * Tests if the `BladeValidator` instance contains any validator
     * instance with the provided class name.
     *
     * @param  string  $validatorClass  The class name to check.
     */
    public function hasValidatorClass(string $validatorClass): bool
    {
        return array_key_exists($validatorClass, $this->registeredValidators);
    }

    /**
     * Removes a validator instance with the provided validator class name.
     *
     * @param  string  $validatorClass  The validator class name.
     * @return $this
     */
    public function removeValidator(string $validatorClass): BladeValidator
    {
        $this->validators = collect($this->validators)->filter(function ($validator) use ($validatorClass) {
            return get_class($validator) === $validatorClass;
        })->values()->all();

        $this->documentValidators = collect($this->documentValidators)->filter(function ($validator) use ($validatorClass) {
            return get_class($validator) === $validatorClass;
        })->values()->all();

        unset($this->registeredValidators[$validatorClass]);

        return $this;
    }

    /**
     * Registers a document validator with the `BladeValidator` instance.
     *
     * @param  AbstractDocumentValidator  $validator  The validator instance.
     * @return $this
     */
    public function addDocumentValidator(AbstractDocumentValidator $validator): BladeValidator
    {
        $validationClass = get_class($validator);

        if (array_key_exists($validationClass, $this->registeredValidators)) {
            return $this;
        }

        if ($validator->requiresStructures) {
            $this->validatorsRequireStructures = true;
        }

        $this->registeredValidators[$validationClass] = $validator;
        $this->documentValidators[] = $validator;

        return $this;
    }

    /**
     * Registers multiple document validators with the `BladeValidator` instance.
     *
     * @param  AbstractDocumentValidator[]  $validators  The document validator instances.
     * @return $this
     */
    public function addDocumentValidators(array $validators): BladeValidator
    {
        foreach ($validators as $validator) {
            $this->addDocumentValidator($validator);
        }

        return $this;
    }

    /**
     * Registers a single node validator instance with the `BladeValidator` instance.
     *
     * @param  AbstractNodeValidator  $validator  The node validator instance.
     * @return $this
     */
    public function addValidator(AbstractNodeValidator $validator): BladeValidator
    {
        $validationClass = get_class($validator);

        if (array_key_exists($validationClass, $this->registeredValidators)) {
            return $this;
        }

        if ($validator->requiresStructures) {
            $this->validatorsRequireStructures = true;
        }

        $this->registeredValidators[$validationClass] = $validator;
        $this->validators[] = $validator;

        return $this;
    }

    /**
     * Adds the validators to validator instance.
     *
     * @param  AbstractNodeValidator[]  $validators  The validators.
     */
    public function addValidators(array $validators): BladeValidator
    {
        foreach ($validators as $validator) {
            $this->addValidator($validator);
        }

        return $this;
    }

    /**
     * Clears the internal state.
     *
     * This method is automatically invoked between validation runs.
     *
     * @internal
     *
     * @return $this
     */
    public function reset(): BladeValidator
    {
        $this->resultSlugs = [];
        $this->documentResults = [];
        $this->results = [];

        return $this;
    }

    /**
     * Runs all registered node validators against the provided node list.
     *
     * @param  AbstractNode[]  $nodes  The nodes to validate.
     */
    public function validateNodes(array $nodes): Collection
    {
        foreach ($nodes as $node) {
            foreach ($this->validators as $validator) {
                if ($results = $validator->validate($node)) {
                    if (! is_array($results)) {
                        $results = [$results];
                    }

                    /** @var ValidationResult $result */
                    foreach ($results as $result) {
                        $slug = $result->toSlug();

                        if (array_key_exists($slug, $this->resultSlugs)) {
                            continue;
                        }

                        $this->results[] = $result;
                        $this->resultSlugs[$slug] = true;
                    }
                }
            }
        }

        return $this->getResults();
    }

    /**
     * Runs all registered document validators against the provided document.
     *
     * This method will *not* automatically run registered node validators
     * against the document's nodes.
     *
     * @param  Document  $document  The document to validate.
     */
    public function validateDocument(Document $document): Collection
    {
        $this->documentResults = [];

        foreach ($this->documentValidators as $validator) {
            if ($results = $validator->validate($document)) {
                if (! is_array($results)) {
                    $results = [$results];
                }

                foreach ($results as $result) {
                    $slug = $result->toSlug();

                    if (array_key_exists($slug, $this->resultSlugs)) {
                        continue;
                    }

                    $this->documentResults[] = $result;
                    $this->resultSlugs[$slug] = true;
                }
            }
        }

        return $this->getDocumentResults();
    }

    /**
     * Returns a collection of node validation results.
     */
    public function getResults(): Collection
    {
        return collect($this->results);
    }

    /**
     * Returns a collection of document validation results.
     */
    public function getDocumentResults(): Collection
    {
        return collect($this->documentResults);
    }
}
