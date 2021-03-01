<?php

namespace Stillat\BladeParser\Tests\Structures;

use Stillat\BladeParser\Parsers\Structures\VerbatimBlockParser;
use Stillat\BladeParser\Tests\ParserTestCase;

class VerbatimTest extends ParserTestCase
{
    public function testExtremeExamplesMatchLaravelOutput()
    {
        $string = 'SL01 {{ $title }}
SL02 @@verbatim {{ $title}} @verbatim <!--STARTPAIR_1--> hm {{ title }}
SL03 @@verbatim
SL04 @verbatim
SL05 @verbatim
SL06 @verbatim
SL07 @verbatim
SL08 @@endverbatim<!--ENDPAIR_1--><p></p>
SL09 @endverbatim
SL10 @verbatim
SL11 @endverbatim
SL12 {{$title}}
SL13 @endverbatim';
        $expected = 'SL01 <?php echo e($title); ?>

SL02 @verbatim <?php echo e($title); ?>  <!--STARTPAIR_1--> hm {{ title }}
SL03 @@verbatim
SL04 @verbatim
SL05 @verbatim
SL06 @verbatim
SL07 @verbatim
SL08 @<!--ENDPAIR_1--><p></p>
SL09 @endverbatim
SL10 
SL11 
SL12 <?php echo e($title); ?>

SL13 @endverbatim';
        $this->assertSame($expected, $this->compiler->compileString($string));
    }

    public function testHighlyConvolutedPairingsMatchLaravelOutput()
    {
        $string = 'SL01 {{ $title }}
SL02 @@verbatim {{ $title}} @verbatim <!--STARTPAIR_1--> hm {{ title }}
SL03 @@verbatim
SL04 @verbatim
SL05 @verbatim
SL06 @verbatim
SL07 @verbatim
SL08 @@endverbatim<!--ENDPAIR_1--><p></p>
SL09 @endverbatim
SL10 @verbatim
SL11 @endverbatim
SL12 {{$title}}
SL13 @endverbatim
SL14 {{ $title }}
SL15 @verbatim <!--STARTPAIR_2-->
SL16 {{ $title }}
SL17 @endverbatim <!--ENDPAIR_2-->
SL18 {{ $title }}
SL19 @endverbatim
SL20 <!--Start Example-->
SL21 @@verbatim {{ $title }} @verbatim <!--STARTPAIR_3-->{{ $title }}
SL22 {{ $title }}
SL23 @ @endverbatim <!--ENDPAIR_3-->';
        $expected = 'SL01 <?php echo e($title); ?>

SL02 @verbatim <?php echo e($title); ?>  <!--STARTPAIR_1--> hm {{ title }}
SL03 @@verbatim
SL04 @verbatim
SL05 @verbatim
SL06 @verbatim
SL07 @verbatim
SL08 @<!--ENDPAIR_1--><p></p>
SL09 @endverbatim
SL10 
SL11 
SL12 <?php echo e($title); ?>

SL13 @endverbatim
SL14 <?php echo e($title); ?>

SL15  <!--STARTPAIR_2-->
SL16 {{ $title }}
SL17  <!--ENDPAIR_2-->
SL18 <?php echo e($title); ?>

SL19 @endverbatim
SL20 <!--Start Example-->
SL21 @verbatim <?php echo e($title); ?>  <!--STARTPAIR_3-->{{ $title }}
SL22 {{ $title }}
SL23 @  <!--ENDPAIR_3-->';

        $this->assertSame($expected, $this->compiler->compileString($string));
    }

    public function testVerbatimDoesNotAllowPhpTagToBeParsed()
    {
        $string = '@verbatim
    @php
@endverbatim';
        $expected = '
    @php
';
        $this->assertSame($expected, $this->compiler->compileString($string));
    }

    public function testParserCanDetermineIfBalancedOrNot()
    {
        $parser = new VerbatimBlockParser();
        $parser->setTokens(mb_str_split('@verbatim

@endverbatim
'));

        $parser->parse();
        $this->assertTrue($parser->getIsBalanced());

        $parser->setTokens(mb_str_split('@verbatim

@endverbatim
@endverbatim
'));

        $parser->parse();
        $this->assertFalse($parser->getIsBalanced());
    }

    public function testVerbatimIsCompiled()
    {
        $string = '<span>Hello, world.</span>@verbatim
{{ I am not Blade. }} @endverbatim
<span>{{ $title }}</span>';
        $expected = '<span>Hello, world.</span>
{{ I am not Blade. }} 
<span><?php echo e($title); ?></span>';

        $this->assertSame($expected, $this->compiler->compileString($string));
    }
}
