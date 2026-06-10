@props(['headers' => [], 'empty' => 'No records found.'])

<div style="overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
        <thead>
            <tr style="border-bottom: 1px solid var(--rule);">
                @foreach($headers as $header)
                    <th style="text-align: left; padding: 10px 14px; font-size: 10.5px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3); white-space: nowrap;">
                        {{ $header }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
    @if(trim((string) $slot) === '')
        <div style="padding: 40px 20px; text-align: center; color: var(--ink-4); font-size: 13px;">
            {{ $empty }}
        </div>
    @endif
</div>
