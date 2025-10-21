<div>
    <div class="mb-4 flex items-center space-x-4">
        <div>
            <label>Start date</label>
            <input type="date" wire:model.lazy="dateStart" class="border rounded px-2 py-1" />
        </div>
        <div>
            <label>End date</label>
            <input type="date" wire:model.lazy="dateEnd" class="border rounded px-2 py-1" />
        </div>

        <div>
            <label>Branch</label>
            <select wire:model="branchId" class="border rounded px-2 py-1">
                <option value="">All branches</option>
                @foreach($branches ?? [] as $b)
                    @php
                        $val = $b->code ?? $b->id ?? '';
                        $label = $b->name ?? $b->code ?? $b->id ?? '';
                    @endphp
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <button wire:click="$emit('load')" class="bg-blue-600 text-white px-3 py-1 rounded">Load</button>
        </div>
    </div>

    <div class="overflow-auto">
        <table class="min-w-full border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1">Account</th>
                    <th class="border px-2 py-1">Name</th>
                    <th class="border px-2 py-1 text-right">Opening Dr</th>
                    <th class="border px-2 py-1 text-right">Opening Cr</th>
                    <th class="border px-2 py-1 text-right">Movement Dr</th>
                    <th class="border px-2 py-1 text-right">Movement Cr</th>
                    <th class="border px-2 py-1 text-right">Balance Dr</th>
                    <th class="border px-2 py-1 text-right">Balance Cr</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $currentBranch = null;
                @endphp
                @foreach($rows ?? [] as $r)
                    @if($currentBranch !== $r['branch_id'])
                        @php $currentBranch = $r['branch_id']; @endphp
                        <tr class="bg-gray-200">
                            <td class="border px-2 py-1" colspan="8"><strong>Branch: {{ $r['branch_name'] }}</strong></td>
                        </tr>
                    @endif
                    <tr>
                        <td class="border px-2 py-1">{{ $r['account_number'] }}</td>
                        <td class="border px-2 py-1">{{ $r['account_name'] }}</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($r['opening_debit'], 2) }}</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($r['opening_credit'], 2) }}</td>
                        <td class="border px-2 py-1 text-right">
                            <a href="#" wire:click.prevent="showMovementDetail('{{ $r['account_number'] }}', '{{ $r['branch_id'] }}')">{{ number_format($r['movement_debit'], 2) }}</a>
                        </td>
                        <td class="border px-2 py-1 text-right">
                            <a href="#" wire:click.prevent="showMovementDetail('{{ $r['account_number'] }}', '{{ $r['branch_id'] }}')">{{ number_format($r['movement_credit'], 2) }}</a>
                        </td>
                        <td class="border px-2 py-1 text-right">{{ number_format($r['balance_debit'], 2) }}</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($r['balance_credit'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Simple modal for details -->
    @if($showDetail ?? false)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded shadow-lg w-11/12 max-w-4xl p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Movement detail - {{ $detailAccount ?? '' }}</h3>
                    <button wire:click="closeDetail" class="px-2 py-1 bg-gray-200 rounded">Close</button>
                </div>

                <div class="overflow-auto max-h-96">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border px-2 py-1">Date</th>
                                <th class="border px-2 py-1">Ref</th>
                                <th class="border px-2 py-1">Desc</th>
                                <th class="border px-2 py-1 text-right">Dr</th>
                                <th class="border px-2 py-1 text-right">Cr</th>
                                <th class="border px-2 py-1">Branch</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detailRows ?? [] as $d)
                                <tr>
                                    <td class="border px-2 py-1">{{ $d->date }}</td>
                                    <td class="border px-2 py-1">{{ $d->reference }}</td>
                                    <td class="border px-2 py-1">{{ $d->description }}</td>
                                    <td class="border px-2 py-1 text-right">{{ $d->dc === 'D' ? number_format($d->amount,2) : '' }}</td>
                                    <td class="border px-2 py-1 text-right">{{ $d->dc === 'C' ? number_format($d->amount,2) : '' }}</td>
                                    <td class="border px-2 py-1">{{ $d->branch_name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
