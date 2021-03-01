<?php

namespace Stillat\BladeParser\Printers\Php\Concerns;

use Stillat\BladeParser\Nodes\Node;

trait PrintsAuthorizations
{

    protected function print_can(Node $node)
    {
        return '<?php if (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->check('.$node->innerContent().')): ?>';
    }

    protected function print_elsecan(Node $node)
    {
        return '<?php elseif (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->check('.$node->innerContent().')): ?>';
    }

    protected function print_endcan(Node $node)
    {
        return $this->phpEndIf();
    }

    protected function print_canany(Node $node)
    {
        return '<?php if (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->any('.$node->innerContent().')): ?>';
    }

    protected function print_elsecanany(Node $node)
    {
        return '<?php elseif (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->any('.$node->innerContent().')): ?>';
    }

    protected function print_cannot(Node $node)
    {
        return '<?php if (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->denies('.$node->innerContent().')): ?>';
    }

    protected function print_elsecannot(Node $node)
    {
        return '<?php elseif (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->denies('.$node->innerContent().')): ?>';
    }

    protected function print_endcannot(Node $node)
    {
        return $this->phpEndIf();
    }

    protected function print_auth(Node  $node)
    {
        $guard = '';

        if ($node->hasInnerExpression()) {
            $guard = $this->wrapInDoubleQuotes($this->trimQuotes($node->innerContent()));
        }

        return "<?php if(auth()->guard({$guard})->check()): ?>";
    }

    protected function print_endauth(Node  $node)
    {
        return $this->phpEndIf();
    }

    protected function print_elseauth(Node $node)
    {
        $guard = '';

        if ($node->hasInnerExpression()) {
            $guard = $this->wrapInDoubleQuotes($this->trimQuotes($node->innerContent()));
        }

        return "<?php elseif(auth()->guard({$guard})->check()): ?>";
    }

    protected function print_guest(Node  $node)
    {
        $guard = '';

        if ($node->hasInnerExpression()) {
            $guard = $this->wrapInDoubleQuotes($this->trimQuotes($node->innerContent()));
        }

        return "<?php if(auth()->guard({$guard})->guest()): ?>";
    }

    protected function print_elseguest(Node  $node)
    {
        $guard = '';

        if ($node->hasInnerExpression()) {
            $guard = $this->wrapInDoubleQuotes($this->trimQuotes($node->innerContent()));
        }

        return "<?php elseif(auth()->guard({$guard})->guest()): ?>";
    }

    protected function print_endguest(Node $node)
    {
        return $this->phpEndIf();
    }

}
