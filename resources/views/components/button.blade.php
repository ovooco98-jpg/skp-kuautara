@props([
    'variant' => 'primary', // primary, secondary, success, danger, warning, info
    'size' => 'sm', // sm, md, lg
    'type' => 'button',
    'href' => null,
    'icon' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variantClasses = [
        'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
        'secondary' => 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500',
        'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'warning' => 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500',
        'info' => 'bg-cyan-600 text-white hover:bg-cyan-700 focus:ring-cyan-500',
        'outline-primary' => 'border-2 border-blue-600 text-blue-600 hover:bg-blue-50 focus:ring-blue-500',
        'outline-danger' => 'border-2 border-red-600 text-red-600 hover:bg-red-50 focus:ring-red-500',
    ];
    
    $sizeClasses = [
        'sm' => 'px-2.5 py-1 text-xs',
        'md' => 'px-3 py-1.5 text-sm',
        'lg' => 'px-4 py-2 text-base',
    ];
    
    $classes = $baseClasses . ' ' . ($variantClasses[$variant] ?? $variantClasses['primary']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <x-icon :name="$icon" class="mr-2" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <x-icon :name="$icon" class="mr-2" />
        @endif
        {{ $slot }}
    </button>
@endif

