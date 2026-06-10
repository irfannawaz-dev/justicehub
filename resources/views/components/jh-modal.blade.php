@props(['name', 'title' => '', 'maxWidth' => '560px', 'noPadding' => false])

<div class="modal fade" id="modal-{{ $name }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: {{ $maxWidth }}; margin: 1.75rem auto;">
        <div class="modal-content" style="border: 1px solid var(--rule); border-radius: 4px; background: var(--paper); box-shadow: 0 12px 40px rgba(0,0,0,0.15); max-height: 90vh; overflow: hidden; display: flex; flex-direction: column;">

            @if($title)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px 24px 16px; border-bottom: 1px solid var(--rule-2); flex-shrink: 0;">
                <h3 class="serif" style="font-size: 20px; font-weight: 500; margin: 0; color: var(--forest);">
                    {{ $title }}
                </h3>
                <button type="button" data-bs-dismiss="modal"
                    style="background: none; border: none; cursor: pointer; color: var(--ink-3); padding: 4px; line-height: 1;">
                    <x-lucide-x style="width: 16px; height: 16px;" />
                </button>
            </div>
            @endif

            @if($noPadding)
            {{ $slot }}
            @else
            <div style="padding: 20px 24px 24px; overflow-y: auto;">
                {{ $slot }}
            </div>
            @endif

        </div>
    </div>
</div>
