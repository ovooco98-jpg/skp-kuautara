@props([
    'title' => null,
    'header' => null,
    'footer' => null,
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-sm border border-gray-200']) }}>
    @if($title || $header)
        <div class="px-4 py-3 border-b border-gray-200">
            @if($title)
                <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
            @endif
            @if($header)
                {{ $header }}
            @endif
        </div>
    @endif
    
    <div class="{{ $padding ? 'p-4' : '' }}">
        {{ $slot }}
    </div>
    
    @if($footer)
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg">
            {{ $footer }}
        </div>
    @endif
</div>

