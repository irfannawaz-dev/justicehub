@props(['name', 'show' => false, 'maxWidth' => '2xl'])

@php
$maxWidthPx = match($maxWidth) {
    'sm'  => '384px',
    'md'  => '448px',
    'lg'  => '512px',
    'xl'  => '576px',
    default => '672px',
};
@endphp

<div class="modal fade" id="modal-{{ $name }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: {{ $maxWidthPx }}; margin: 1.75rem auto;">
        <div class="modal-content" style="border-radius: 8px; overflow: hidden; border: none; box-shadow: 0 10px 40px rgba(0,0,0,.2);">
            {{ $slot }}
        </div>
    </div>
</div>

@if($show)
<script>document.addEventListener('DOMContentLoaded', function(){ jhOpenModal('{{ $name }}'); });</script>
@endif
