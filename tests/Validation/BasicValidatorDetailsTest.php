<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('has validator class', function () {
    $validator = $this->getValidator()->withCoreValidators();

    foreach (config('blade.validation.core_validators') as $className) {
        expect($validator->hasValidatorClass($className))->toBeTrue();
    }
});

test('has validator instances', function () {
    $validator = $this->getValidator()->withCoreValidators();

    foreach ($validator->getNodeValidators() as $nodeValidator) {
        expect($validator->hasValidatorInstance($nodeValidator))->toBeTrue();
    }

    foreach ($validator->getDocumentValidators() as $documentValidator) {
        expect($validator->hasDocumentValidatorInstance($documentValidator))->toBeTrue();
    }
});

test('remove validators', function () {
    $validator = $this->getValidator()->withCoreValidators();

    expect($validator->getValidators())->toHaveCount(19);

    foreach ($validator->getValidators() as $validatorInstance) {
        $validator->removeValidator(get_class($validatorInstance));
    }

    expect($validator->getValidators())->toHaveCount(0);

    foreach (config('blade.validation.core_validators') as $className) {
        expect($validator->hasValidatorClass($className))->toBeFalse();
    }

    // Test we can add them back.
    $validator->withCoreValidators();
    expect($validator->getValidators())->toHaveCount(19);
});
