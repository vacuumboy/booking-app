@props([
    'sparkColor' => '#ffffff',
    'sparkCount' => 8,
    'sparkSize' => 10,
    'duration' => 400,
    'type' => 'button'
])

<div x-data="clickSpark()" 
     x-init="init(); sparkColor = '{{ $sparkColor }}'; sparkCount = {{ $sparkCount }}; sparkSize = {{ $sparkSize }}; duration = {{ $duration }};"
     @click="handleClick($event)"
     class="relative inline-block">
    <canvas x-ref="canvas" class="absolute inset-0 w-full h-full pointer-events-none z-10"></canvas>
    
    <button {{ $attributes->merge(['type' => $type, 'class' => 'relative z-20 transition-all duration-200']) }}>
        {{ $slot }}
    </button>
</div> 