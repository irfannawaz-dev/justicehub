@props(['icon' => 'folder', 'message' => 'No records found.', 'action' => null, 'actionLabel' => null])

<div style="padding: 60px 20px; text-align: center;">
    <div style="margin-bottom: 16px;">
        <x-dynamic-component :component="'lucide-' . $icon" style="width: 40px; height: 40px; color: var(--ink-4); margin: 0 auto; opacity: 0.5;" />
    </div>
    <div style="font-size: 14px; color: var(--ink-3); margin-bottom: 16px;">
        {{ $message }}
    </div>
    @if($action)
    <a href="{{ $action }}" class="btn-primary" style="display: inline-flex;">
        <x-lucide-plus style="width: 14px; height: 14px;" />
        {{ $actionLabel ?? 'Create New' }}
    </a>
    @endif
</div>
