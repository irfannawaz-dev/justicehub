@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white'])

<div class="dropdown relative">
    <div data-bs-toggle="dropdown" aria-expanded="false" style="cursor:pointer;">
        {{ $trigger }}
    </div>
    <div class="dropdown-menu {{ $align === 'left' ? '' : 'dropdown-menu-end' }}"
         style="padding: 4px 0; min-width: 12rem; border-radius: 6px; border: 1px solid #e5e7eb; box-shadow: 0 4px 16px rgba(0,0,0,.10);">
        <div class="{{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
