<x-filament-widgets::widget>
    <x-filament::section
        heading="Game Activity Details"
        description="Recent character and account activity, plus distribution by character class."
    >
        @if (! empty($errors))
            <div class="mb-4 rounded-lg border border-warning-300 bg-warning-50 p-3 text-sm text-warning-800 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-200">
                @foreach ($errors as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            {{-- Character class distribution --}}
            <x-filament::section compact heading="Characters by Class" description="Active character distribution">
                <div style="width: 100%; overflow-x: auto;">
                    <table style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.875rem; line-height: 1.45;">
                        <thead style="background: var(--fi-color-gray-50, #f9fafb);">
                            <tr>
                                <th scope="col" style="padding: 0.8rem 1rem; text-align: left; font-weight: 600; white-space: nowrap; border-bottom: 1px solid var(--fi-color-gray-200, #e5e7eb);">Class</th>
                                <th scope="col" style="padding: 0.8rem 1rem; text-align: right; font-weight: 600; white-space: nowrap; border-bottom: 1px solid var(--fi-color-gray-200, #e5e7eb);">Characters</th>
                                <th scope="col" style="width: 35%; padding: 0.8rem 1rem; text-align: left; font-weight: 600; white-space: nowrap; border-bottom: 1px solid var(--fi-color-gray-200, #e5e7eb);">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($classes as $item)
                                @php
                                    $classMax = max(1, (int) max(array_column($classes, 'total')));
                                    $classPercent = (int) round(((int) $item['total'] / $classMax) * 100);
                                @endphp
                                <tr>
                                    <td style="padding: 0.7rem 1rem; font-weight: 500; white-space: nowrap; border-bottom: 1px solid var(--fi-color-gray-200, #e5e7eb);">
                                        <span style="display:inline-flex; align-items:center; gap:.6rem;">
                                            <img src="{{ asset('images/icon/' . $item['icon']) }}" alt="" loading="lazy" style="width:1.5rem;height:1.5rem;object-fit:contain;">
                                            <span>{{ $item['label'] }}</span>
                                        </span>
                                    </td>
                                    <td style="padding: 0.7rem 1rem; text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; border-bottom: 1px solid var(--fi-color-gray-200, #e5e7eb);">{{ number_format($item['total']) }}</td>
                                    <td style="padding: 0.7rem 1rem; border-bottom: 1px solid var(--fi-color-gray-200, #e5e7eb);">
                                        <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                            <div class="h-full rounded-full bg-primary-600 dark:bg-primary-500" style="width: {{ max(2, min(100, $classPercent)) }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="padding: 1.5rem 1rem; text-align: center; opacity: .7;">No class data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            {{-- Recent characters --}}
            <x-filament::section compact heading="Recently Created Characters" description="Latest character records">
                <div style="width: 100%; overflow-x: auto;">
                    <table style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.875rem; line-height: 1.45;">
                        <thead style="background: var(--fi-color-gray-50, #f9fafb);">
                            <tr>
                                <th scope="col" style="padding: 0.8rem 1rem; text-align: left; font-weight: 600; white-space: nowrap; border-bottom: 1px solid var(--fi-color-gray-200, #e5e7eb);">Character / Account</th>
                                <th scope="col" style="padding: 0.8rem 1rem; text-align: left; font-weight: 600; white-space: nowrap; border-bottom: 1px solid var(--fi-color-gray-200, #e5e7eb);">Details</th>
                                <th scope="col" style="padding: 0.8rem 1rem; text-align: right; font-weight: 600; white-space: nowrap; border-bottom: 1px solid var(--fi-color-gray-200, #e5e7eb);">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recent_characters as $char)
                                <tr>
                                    <td style="min-width: 9rem; padding: 0.7rem 1rem; border-bottom: 1px solid var(--fi-color-gray-200, #e5e7eb);">
                                        <div class="font-medium text-gray-950 dark:text-white">{{ $char['name'] ?? $char['char_name'] ?? ('Character #' . ($char['char_id'] ?? '?')) }}</div>
                                        <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $char['account_name'] ?? $char['user_id'] ?? 'Unknown account' }}</div>
                                    </td>
                                    <td style="padding: 0.7rem 1rem; white-space: nowrap; border-bottom: 1px solid var(--fi-color-gray-200, #e5e7eb);">
                                        <div>Lv. {{ $char['lev'] ?? '—' }} · {{ $char['class_label'] ?? ($char['class'] ?? '—') }}</div>
                                        <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $char['race_label'] ?? 'Unknown Race' }}</div>
                                        <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">ID {{ $char['char_id'] ?? '—' }}</div>
                                    </td>
                                    <td style="padding: 0.7rem 1rem; text-align: right; white-space: nowrap; font-size: 0.75rem; border-bottom: 1px solid var(--fi-color-gray-200, #e5e7eb);">
                                        {{ ! empty($char['create_date']) ? \Illuminate\Support\Carbon::parse($char['create_date'])->diffForHumans() : 'Unavailable' }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="padding: 1.5rem 1rem; text-align: center; opacity: .7;">No character data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            {{-- Recent accounts --}}
            <x-filament::section compact heading="Recently Created Accounts" description="Latest account records">
                <div style="width: 100%; overflow-x: auto;">
                    <table style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.875rem; line-height: 1.45;">
                        <thead style="background: var(--fi-color-gray-50, #f9fafb);">
                            <tr>
                                <th scope="col" style="padding: 0.8rem 1rem; text-align: left; font-weight: 600; white-space: nowrap; border-bottom: 1px solid var(--fi-color-gray-200, #e5e7eb);">Account</th>
                                <th scope="col" style="padding: 0.8rem 1rem; text-align: right; font-weight: 600; white-space: nowrap; border-bottom: 1px solid var(--fi-color-gray-200, #e5e7eb);">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recent_accounts as $account)
                                <tr>
                                    <td style="padding: 0.7rem 1rem; font-weight: 500; border-bottom: 1px solid var(--fi-color-gray-200, #e5e7eb);">{{ $account['account'] ?? 'Unknown account' }}</td>
                                    <td style="padding: 0.7rem 1rem; text-align: right; white-space: nowrap; font-size: 0.75rem; border-bottom: 1px solid var(--fi-color-gray-200, #e5e7eb);">
                                        {{ ! empty($account['create_date']) ? \Illuminate\Support\Carbon::parse($account['create_date'])->diffForHumans() : 'Unavailable' }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" style="padding: 1.5rem 1rem; text-align: center; opacity: .7;">No account creation history available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>

        @if (is_array($online))
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 14rem), 1fr)); gap: 1rem; margin-top: 1.5rem;">
                <x-filament::section compact>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Server connectivity</div>
                    <div class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ $online['server_status'] ?? 'UNKNOWN' }}</div>
                </x-filament::section>
                <x-filament::section compact>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Online players</div>
                    <div class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ isset($online['online_players']) ? number_format($online['online_players']) : 'Unavailable' }}</div>
                </x-filament::section>
                <x-filament::section compact>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Player details</div>
                    <div class="mt-1 text-sm font-medium text-gray-950 dark:text-white">{{ $online['players_detail'] ?? 'No additional details' }}</div>
                </x-filament::section>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
