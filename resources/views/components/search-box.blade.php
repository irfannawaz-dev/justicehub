@props(['placeholder' => 'Search...', 'name' => 'search', 'value' => ''])

@php $uid = 'sb-' . uniqid(); @endphp

<div style="position: relative;" id="{{ $uid }}">
    <x-lucide-search style="width: 14px; height: 14px; position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--ink-4);" />
    <input
        type="text"
        name="{{ $name }}"
        id="{{ $uid }}-input"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        class="inp"
        style="padding-left: 34px; padding-right: 32px; font-size: 13px;"
        oninput="document.getElementById('{{ $uid }}-clear').style.display = this.value ? 'flex' : 'none';"
        {{ $attributes }}
    >
    <button
        id="{{ $uid }}-clear"
        type="button"
        onclick="document.getElementById('{{ $uid }}-input').value=''; this.style.display='none'; document.getElementById('{{ $uid }}-input').dispatchEvent(new Event('input'));"
        style="display: {{ $value ? 'flex' : 'none' }}; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--ink-4); padding: 4px; align-items: center;"
    >
        <x-lucide-x style="width: 12px; height: 12px;" />
    </button>
</div>
