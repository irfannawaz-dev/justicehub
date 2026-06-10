@props(['hubs' => [], 'activeHub' => 'all'])

<div class="dropdown" style="position: relative;">
    <button class="dropdown-toggle btn-ghost" data-bs-toggle="dropdown" aria-expanded="false"
        style="display: flex; align-items: center; gap: 8px;">
        <x-lucide-map-pin style="width: 13px; height: 13px; color: var(--ochre);" />
        <span>{{ $activeHub === 'all' ? 'All Hubs' : ($hubs->firstWhere('id', $activeHub)?->name ?? 'All Hubs') }}</span>
        <x-lucide-chevron-down style="width: 11px; height: 11px; color: var(--ink-4);" />
    </button>

    <ul class="dropdown-menu" style="padding: 0; border: 1px solid var(--rule); border-radius: 0; background: var(--paper); box-shadow: var(--shadow-card); min-width: 200px; margin-top: 4px;">
        <li>
            <form method="POST" action="{{ route('settings.hub') }}">
                @csrf
                <button type="submit" name="hub_id" value="all"
                    style="width: 100%; padding: 10px 14px; text-align: left; border: none; background: {{ $activeHub === 'all' ? 'var(--parchment-2)' : 'transparent' }}; color: var(--ink); font-size: 13px; cursor: pointer; font-family: inherit;"
                    class="tr-hover">
                    All Hubs
                </button>
            </form>
        </li>
        @foreach($hubs as $hub)
        <li>
            <form method="POST" action="{{ route('settings.hub') }}">
                @csrf
                <button type="submit" name="hub_id" value="{{ $hub->id }}"
                    style="width: 100%; padding: 10px 14px; text-align: left; border: none; background: {{ $activeHub === $hub->id ? 'var(--parchment-2)' : 'transparent' }}; color: var(--ink); font-size: 13px; cursor: pointer; font-family: inherit;"
                    class="tr-hover">
                    {{ $hub->name }}
                </button>
            </form>
        </li>
        @endforeach
    </ul>
</div>

<style>.dropdown-toggle::after { display: none !important; }</style>
