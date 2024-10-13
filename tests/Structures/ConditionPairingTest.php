<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Illuminate\Support\Facades\Blade;
use Stillat\BladeParser\Document\Structures\StructurePairAnalyzer;
use Stillat\BladeParser\Nodes\DirectiveNode;

test('basic condition pairing', function () {
    $template = <<<'EOT'
@if ($something == true)

@endif
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();

    $ifDirective = $doc->findDirectiveByName('if');
    $endIfDirective = $doc->findDirectiveByName('endif');

    $this->assertDirectivesArePaired($ifDirective, $endIfDirective);
});

test('else if else pairing', function () {
    $template = <<<'EOT'
@if ($something == true)

@elseif ($somethingElse)

@else

@endif
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();

    $if = $doc->findDirectiveByName('if');
    $elseIf = $doc->findDirectiveByName('elseif');
    $else = $doc->findDirectiveByName('else');
    $endIf = $doc->findDirectiveByName('endif');

    $this->assertDirectivesArePaired($if, $elseIf);
    $this->assertDirectivesArePaired($elseIf, $else);
    $this->assertDirectivesArePaired($else, $endIf);
});

test('nested condition pairing', function () {
    $template = <<<'EOT'
D1 @if ($something == true)
D2    @if ($something == true)
D3        @if ($something == true)
    
D4        @elseif ($somethingElse)
        
D5        @else
        
D6        @endif
D7    @elseif ($somethingElse)
    
D8    @else
    
D9    @endif
D10 @elseif ($somethingElse)

D11 @else

D12 @endif
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();
    $directives = $doc->getDirectives();
    $d1 = $directives[0];
    // @if
    $d2 = $directives[1];
    // @if
    $d3 = $directives[2];
    // @if
    $d4 = $directives[3];
    // @elseif
    $d5 = $directives[4];
    // @else
    $d6 = $directives[5];
    // @endif
    $d7 = $directives[6];
    // @elseif
    $d8 = $directives[7];
    // @else
    $d9 = $directives[8];
    // @endif
    $d10 = $directives[9];
    // @elseif
    $d11 = $directives[10];
    // @else
    $d12 = $directives[11];

    // @endif
    $this->assertDirectivesArePaired($d1, $d10);
    $this->assertDirectivesArePaired($d10, $d11);
    $this->assertDirectivesArePaired($d11, $d12);

    $this->assertDirectivesArePaired($d2, $d7);
    $this->assertDirectivesArePaired($d7, $d8);
    $this->assertDirectivesArePaired($d8, $d9);

    $this->assertDirectivesArePaired($d3, $d4);
    $this->assertDirectivesArePaired($d4, $d5);
    $this->assertDirectivesArePaired($d5, $d6);
});

test('custom condition pairing', function () {
    Blade::if('disk', function ($value) {
        return config('filesystems.default') === $value;
    });

    $template = <<<'EOT'
D1 @disk('local')
    <!-- The application is using the local disk... -->
D2 @elsedisk('s3')
    <!-- The application is using the s3 disk... -->
D3 @else
    <!-- The application is using some other disk... -->
D4 @enddisk
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();
    $directives = $doc->getDirectives();

    $d1 = $directives[0];
    // @disk
    $d2 = $directives[1];
    // @elsedisk
    $d3 = $directives[2];
    // @else
    $d4 = $directives[3];

    // @enddisk
    $this->assertDirectivesArePaired($d1, $d2);
    $this->assertDirectivesArePaired($d2, $d3);
    $this->assertDirectivesArePaired($d3, $d4);
});

test('default speculative conditions', function () {
    // Tests that the default speculative conditions can be paired
    // with "endif" directives. These default condition nodes
    // have their own corresponding end directive, but
    // can also be closed by using the "endif".
    $defaultSpeculativeConditions = StructurePairAnalyzer::getDefaultSpeculativeConditions();

    foreach ($defaultSpeculativeConditions as $directiveName) {
        $template = <<<EOT
D1 @if ('something')
D2    @{$directiveName} ('content')
    
D3    @endif
D4 @endif
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        $directives = $doc->getDirectives();

        $d1 = $directives[0];
        $d2 = $directives[1];
        $d3 = $directives[2];
        $d4 = $directives[3];

        $this->assertDirectivesArePaired($d1, $d4, $directiveName);
        $this->assertDirectivesArePaired($d2, $d3, $directiveName);
    }
});

test('ambiguous condition directive pairing', function ($open, $close) {
    // The constructed templates will be similar to:
    // @if ('content')
    //    @auth ('content_two')
    //        @auth ('content_three')
    //        @endAuth
    //    @endif
    // @endif
    $template = <<<EOT
D1 @if ('content')

D2    @{$open} ('content_two')
    
D3        @{$open} ('content_three')
        
D4        @{$close}
    
D5    @endif

D6 @endif
EOT;

    $doc = $this->getDocument($template);
    $doc->resolveStructures();
    $directives = $doc->getDirectives();

    $d1 = $directives[0];
    $d2 = $directives[1];
    $d3 = $directives[2];
    $d4 = $directives[3];
    $d5 = $directives[4];
    $d6 = $directives[5];

    $this->assertDirectivesArePaired($d1, $d6, $open.' '.$close);
    $this->assertDirectivesArePaired($d2, $d5, $open.' '.$close);
    $this->assertDirectivesArePaired($d3, $d4, $open.' '.$close);
})->with('conditionalDirectives');

test('unpaired conditions do not cause infinite loop', function () {
    $template = <<<'EOT'

@if ($something) 

@elseif ($somethingElse)

@can ($doThis)

@endCan

EOT;
    $doc = $this->getDocument($template)->resolveStructures();

    /** @var DirectiveNode[] $directives */
    $directives = $doc->getDirectives()->all();

    expect($directives[0]->isClosedBy)->toBeNull();
    expect($directives[1]->isOpenedBy)->toBeNull();
    expect($directives[1]->isClosedBy)->toBeNull();
    $this->assertDirectivesArePaired($directives[2], $directives[3]);
});

test('unpaired conditions do not cause infinite loop two', function () {
    $template = <<<'EOT'
@if   ($something) 
    <p>one</p>
@elseif ($something
    <p>two</p>

@elseif
    
EOT;
    $this->getDocument($template)->resolveStructures();
    expect(true)->toBeTrue();
});

test('unpaired conditions do not cause infinite loop three', function () {
    $template = <<<'EOT'

@if   ($something) 
    <P>THIS</p>
@elseif ($something
    <p>HMM.</p>

@elseif
    @else
EOT;
    $this->getDocument($template)->resolveStructures();
    expect(true)->toBeTrue();
});

test('unpaired conditions do not cause infinite loop four', function () {
    $template = <<<'EOT'
@if (Route::has('login'))
A
 @auth 
 B      @else  
 
 C    @if (Route::has('register'))     D   @endif
EOT;
    $this->getDocument($template)->resolveStructures();
    expect(true)->toBeTrue();
});

test('mixed conditions are paired', function () {
    $template = <<<'EOT'
D1 @if (Route::has('login'))
    One
D2        @auth
            Two
D3        @else
            Three

D4            @if (Route::has('register'))
                Four
D5            @endif
D6        @endauth
D7 @endif
EOT;
    $directives = $this->getDocument($template)->resolveStructures()->getDirectives()->all();

    $this->assertDirectivesArePaired($directives[0], $directives[6]);
    $this->assertDirectivesArePaired($directives[1], $directives[2]);
    $this->assertDirectivesArePaired($directives[2], $directives[5]);
    $this->assertDirectivesArePaired($directives[3], $directives[4]);
});

dataset('conditionalDirectives', function () {
    return [
        ['unless', 'endunless'],
        ['env', 'endEnv'],
        ['cannot', 'endcannot'],
        ['can', 'endcan'],
        ['canany', 'endcanany'],
        ['auth', 'endAuth'],
        ['production', 'endProduction'],
        ['guest', 'endGuest'],
        ['isset', 'endIsset'],
    ];
});
