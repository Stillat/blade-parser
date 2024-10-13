<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\Structures\CaseStatement;
use Stillat\BladeParser\Nodes\Structures\SwitchStatement;

test('basic structure queries', function () {
    $template = <<<'EOT'
@if ($something)

    @forelse($users as $user)
    
    @endforelse

@endif
EOT;
    $doc = $this->getDocument($template);
    expect($doc->getAllStructures())->toHaveCount(2);
    expect($doc->getRootStructures())->toHaveCount(1);

    expect($doc->findDirectiveByName('if')->getRootStructures())->toHaveCount(1);
    expect($doc->findDirectiveByName('if')->getAllStructures())->toHaveCount(1);
});

test('nested structures', function () {
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
    expect($doc->getAllStructures())->toHaveCount(20);
    expect($doc->getRootStructures())->toHaveCount(1);
    $structures = $doc->getAllStructures();

    expect($structures[0])->toBeInstanceOf(SwitchStatement::class);
    expect($structures[1])->toBeInstanceOf(CaseStatement::class);
    expect($structures[2])->toBeInstanceOf(SwitchStatement::class);
    expect($structures[3])->toBeInstanceOf(CaseStatement::class);
    expect($structures[4])->toBeInstanceOf(CaseStatement::class);
    expect($structures[5])->toBeInstanceOf(CaseStatement::class);
    expect($structures[6])->toBeInstanceOf(CaseStatement::class);
    expect($structures[7])->toBeInstanceOf(SwitchStatement::class);
    expect($structures[8])->toBeInstanceOf(CaseStatement::class);
    expect($structures[9])->toBeInstanceOf(CaseStatement::class);
    expect($structures[10])->toBeInstanceOf(CaseStatement::class);
    expect($structures[11])->toBeInstanceOf(CaseStatement::class);
    expect($structures[12])->toBeInstanceOf(SwitchStatement::class);
    expect($structures[13])->toBeInstanceOf(CaseStatement::class);
    expect($structures[14])->toBeInstanceOf(SwitchStatement::class);
    expect($structures[15])->toBeInstanceOf(CaseStatement::class);
    expect($structures[16])->toBeInstanceOf(CaseStatement::class);
    expect($structures[17])->toBeInstanceOf(CaseStatement::class);
    expect($structures[18])->toBeInstanceOf(CaseStatement::class);
    expect($structures[19])->toBeInstanceOf(CaseStatement::class);
});
