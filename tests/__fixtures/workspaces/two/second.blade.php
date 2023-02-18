
@include ('one')

@if ($this)

    @if ($this)

    @endif

@endif

{{-- Comment --}}

@switch ($value)

    @case (1)
        @switch ($value)

            @default
            @break
        @endswitch
    @break

    @default
    @break
@endswitch

{{-- Comment --}}


@verbatim 

@include ('one')

@if ($this)

    @if ($this)

    @endif

@endif

{{-- Comment --}}

{{ $hello }}

@switch ($value)

    @case (1)
        @switch ($value)

            @default
            @break
        @endswitch
    @break

    @default
    @break
@endswitch

{{-- Comment --}}


@endverbatim


<?php $variable++; ?>

@php
    $variable--;
@endphp


<x-profile />

@forelse ($users as $user)

    @forelse ($user->tasks as $task)

    @empty

    @endforelse

@empty

@endforelse