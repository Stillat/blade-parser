<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Tests\ParserTestCase;

class BasicValidatorDetailsTest extends ParserTestCase
{
    public function testHasValidatorClass()
    {
        $validator = $this->getValidator()->withCoreValidators();

        foreach (config('blade.validation.core_validators') as $className) {
            $this->assertTrue($validator->hasValidatorClass($className));
        }
    }

    public function testHasValidatorInstances()
    {
        $validator = $this->getValidator()->withCoreValidators();

        foreach ($validator->getNodeValidators() as $nodeValidator) {
            $this->assertTrue($validator->hasValidatorInstance($nodeValidator));
        }

        foreach ($validator->getDocumentValidators() as $documentValidator) {
            $this->assertTrue($validator->hasDocumentValidatorInstance($documentValidator));
        }
    }

    public function testRemoveValidators()
    {
        $validator = $this->getValidator()->withCoreValidators();

        $this->assertCount(19, $validator->getValidators());

        foreach ($validator->getValidators() as $validatorInstance) {
            $validator->removeValidator(get_class($validatorInstance));
        }

        $this->assertCount(0, $validator->getValidators());

        foreach (config('blade.validation.core_validators') as $className) {
            $this->assertFalse($validator->hasValidatorClass($className));
        }

        // Test we can add them back.
        $validator->withCoreValidators();
        $this->assertCount(19, $validator->getValidators());
    }
}
