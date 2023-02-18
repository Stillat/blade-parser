<?php

namespace Stillat\BladeParser\Tests\CompilerServices;

use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Tests\ParserTestCase;

class DirectiveDetailsTest extends ParserTestCase
{
    public function testDirectiveDetailsRetrievesInformationWithoutExceptions()
    {
        $instance = CoreDirectiveRetriever::instance();
        $this->assertNotNull($instance);
        $this->assertInstanceOf(CoreDirectiveRetriever::class, $instance);
        $this->assertEquals($instance, CoreDirectiveRetriever::instance());

        $this->assertNotEmpty($instance->getIncludeDirectiveNames());
        $this->assertNotEmpty($instance->getDebugDirectiveNames());
        $this->assertNotEmpty($instance->getDirectivesRequiringOpen());
        $this->assertNotEmpty($instance->getDirectiveNames());
        $this->assertNotEmpty($instance->getNonStructureDirectiveNames());
        $this->assertNotSame($instance->getNonStructureDirectiveNames(), $instance->getDirectiveNames());
        $this->assertNotEmpty($instance->getDirectivesRequiringArguments());
        $this->assertNotEmpty($instance->getDirectivesThatMustNotHaveArguments());
        $this->assertNotEmpty($instance->getDirectivesWithOptionalArguments());
    }
}
