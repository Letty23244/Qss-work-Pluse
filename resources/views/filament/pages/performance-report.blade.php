@php
    $summary = $this->getSummary();
    $rows = $this->getRows();
    $fmt = fn ($v) => number_format((float) $v, 2);
    $pct = fn ($v) => $v === null ? '—' : $v . '%';
@endphp

<x-filament-panels::page>
    <x-filament::section>
        <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-end">
            <div>
                <label class="fi-fo-field-wrp-label" style="font-size:.85rem">From</label>
                <x-filament::input.wrapper><x-filament::input type="date" wire:model.live="from" /></x-filament::input.wrapper>
            </div>
            <div>
                <label class="fi-fo-field-wrp-label" style="font-size:.85rem">To</label>
                <x-filament::input.wrapper><x-filament::input type="date" wire:model.live="to" /></x-filament::input.wrapper>
            </div>
            <div style="min-width:14rem">
                <label class="fi-fo-field-wrp-label" style="font-size:.85rem">Client</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="clientId">
                        <option value="">All clients</option>
                        @foreach ($this->getClients() as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        </div>
    </x-filament::section>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(10rem,1fr));gap:1rem">
        @foreach (['Target' => $fmt($summary['target']), 'Produced' => $fmt($summary['produced']), 'Pending' => $fmt($summary['pending']), 'Remaining' => $fmt($summary['remaining']), 'Achievement' => $pct($summary['achievement'])] as $label => $value)
            <x-filament::section>
                <div style="font-size:.8rem;opacity:.7">{{ $label }}</div>
                <div style="font-size:1.6rem;font-weight:700">{{ $value }}</div>
            </x-filament::section>
        @endforeach
    </div>

    <x-filament::section>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:.9rem">
                <thead>
                    <tr style="text-align:right;border-bottom:1px solid rgba(128,128,128,.35)">
                        <th style="text-align:left;padding:.5rem">Group</th>
                        <th style="padding:.5rem">Target</th>
                        <th style="padding:.5rem">Produced</th>
                        <th style="padding:.5rem">Pending</th>
                        <th style="padding:.5rem">Remaining</th>
                        <th style="padding:.5rem">Achievement</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $r)
                        <tr style="text-align:right;border-bottom:1px solid rgba(128,128,128,.15)">
                            <td style="text-align:left;padding:.5rem">{{ $r['label'] }}</td>
                            <td style="padding:.5rem">{{ $fmt($r['target']) }}</td>
                            <td style="padding:.5rem">{{ $fmt($r['produced']) }}</td>
                            <td style="padding:.5rem">{{ $fmt($r['pending']) }}</td>
                            <td style="padding:.5rem">{{ $fmt($r['remaining']) }}</td>
                            <td style="padding:.5rem;font-weight:600">{{ $pct($r['achievement']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="padding:1rem;text-align:center;opacity:.6">No data for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
