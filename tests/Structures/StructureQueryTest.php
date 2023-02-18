<?php

namespace Stillat\BladeParser\Tests\Structures;

use Stillat\BladeParser\Nodes\Structures\CaseStatement;
use Stillat\BladeParser\Nodes\Structures\SwitchStatement;
use Stillat\BladeParser\Tests\ParserTestCase;

class StructureQueryTest extends ParserTestCase
{
    public function testBasicStructureQueries()
    {
        $template = <<<'EOT'
@if ($something)

    @forelse($users as $user)
    
    @endforelse

@endif
EOT;
        $doc = $this->getDocument($template);
        $this->assertCount(2, $doc->getAllStructures());
        $this->assertCount(1, $doc->getRootStructures());

        $this->assertCount(1, $doc->findDirectiveByName('if')->getRootStructures());
        $this->assertCount(1, $doc->findDirectiveByName('if')->getAllStructures());
    }

    public function testNestedStructures()
    {
        $template = <<<'EOT'
@switch($i)
    @case(1-2)
        First case...
 
         @switch($i2)
            @case(1)
                First case...
                @break
         
            @case(2)
                Second case...
                @break
         
            @default
                Default case...
        @endswitch
        
        @break
 
    @case(2)
        Second case...
                
            @switch($i)
                @case(1)
                    First case...
                    @break
             
                @case(2)
                    Second case...
                    @break
             
                @default
                    Default case...
            @endswitch
        
        @break
 
    @default
        Default case...
        
        @switch($i)
            @case(1)
                First case...
                
                @switch($i)
                    @case(1)
                        First case...
                        @break
                 
                    @case(2)
                        Second case...
                        @break
                 
                    @default
                        Default case...
                @endswitch
                
                @break
         
            @case(2)
                Second case...
                @break
         
            @default
                Default case...
        @endswitch
@endswitch
EOT;
        $doc = $this->getDocument($template);
        $this->assertCount(20, $doc->getAllStructures());
        $this->assertCount(1, $doc->getRootStructures());
        $structures = $doc->getAllStructures();

        $this->assertInstanceOf(SwitchStatement::class, $structures[0]);
        $this->assertInstanceOf(CaseStatement::class, $structures[1]);
        $this->assertInstanceOf(SwitchStatement::class, $structures[2]);
        $this->assertInstanceOf(CaseStatement::class, $structures[3]);
        $this->assertInstanceOf(CaseStatement::class, $structures[4]);
        $this->assertInstanceOf(CaseStatement::class, $structures[5]);
        $this->assertInstanceOf(CaseStatement::class, $structures[6]);
        $this->assertInstanceOf(SwitchStatement::class, $structures[7]);
        $this->assertInstanceOf(CaseStatement::class, $structures[8]);
        $this->assertInstanceOf(CaseStatement::class, $structures[9]);
        $this->assertInstanceOf(CaseStatement::class, $structures[10]);
        $this->assertInstanceOf(CaseStatement::class, $structures[11]);
        $this->assertInstanceOf(SwitchStatement::class, $structures[12]);
        $this->assertInstanceOf(CaseStatement::class, $structures[13]);
        $this->assertInstanceOf(SwitchStatement::class, $structures[14]);
        $this->assertInstanceOf(CaseStatement::class, $structures[15]);
        $this->assertInstanceOf(CaseStatement::class, $structures[16]);
        $this->assertInstanceOf(CaseStatement::class, $structures[17]);
        $this->assertInstanceOf(CaseStatement::class, $structures[18]);
        $this->assertInstanceOf(CaseStatement::class, $structures[19]);
    }
}
