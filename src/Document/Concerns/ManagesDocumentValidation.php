<?php

namespace Stillat\BladeParser\Document\Concerns;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\AbstractDocumentValidator;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\BladeValidator;
use Stillat\BladeParser\Validation\ValidationResult;
use Stillat\BladeParser\Validation\ValidatorFactory;

trait ManagesDocumentValidation
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
     * Adds a single node validator instance to the internal `BladeValidator` instance.
     *
     * @param  AbstractNodeValidator  $validator The node validator.
     */
    public function addValidator(AbstractNodeValidator $validator): Document
    {
        $this->validator()->addValidator($validator);

        return $this;
    }

    /**
     * Adds a single document validator instance to the internal `BladeValidator` instance.
     *
     * @param  AbstractDocumentValidator  $validator The document validator.
     */
    public function addDocumentValidator(AbstractDocumentValidator $validator): Document
    {
        $this->validator()->addDocumentValidator($validator);

        return $this;
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
    public function withCoreValidators(): Document
    {
        $this->validator()->withCoreValidators();

        return $this;
    }

    /**
     * Adds a validator instance to the internal `BladeValidator` instance.
     *
     * @param  AbstractNodeValidator  $validator The validator instance.
     */
    public function withValidator(AbstractNodeValidator $validator): Document
    {
        return $this->withValidators([$validator]);
    }

    /**
     * Adds a list of validator instances to the internal `BladeValidator` instance.
     *
     * @param  AbstractNodeValidator[]  $validators The validator instances.
     */
    public function withValidators(array $validators): Document
    {
        $this->validator()->addValidators($validators);

        return $this;
    }

    /**
     * Validates the document with the internal `BladeValidator` instance.
     *
     * After validation is complete, any produced validation errors
     * will be available through the `getErrors()` method, as well
     * as the `getValidationErrors()` method to retrieve only
     * the validation errors.
     */
    public function validate(): Document
    {
        return $this->validateWith($this->validator());
    }

    /**
     * Validates the document with the provided `BladeValidator` instance.
     *
     * After validation is complete, any produced validation errors
     * will be available through the `getErrors()` method, as well
     * as the `getValidationErrors()` method to retrieve only
     * the validation errors.
     *
     * @param  BladeValidator  $validator The validator instance.
     */
    public function validateWith(BladeValidator $validator): Document
    {
        if ($validator->hasValidatorRequiringStructures()) {
            $this->resolveStructures();
        }

        $this->validationErrors = $validator->reset()->validateNodes($this->getNodes()
            ->all())->map(fn (ValidationResult $r) => $r->toBladeError())
            ->all();

        $validator->validateDocument($this);

        /** @var ValidationResult $result */
        foreach ($validator->getDocumentResults() as $result) {
            $this->validationErrors[] = $result->toBladeError();
        }

        return $this;
    }
}
