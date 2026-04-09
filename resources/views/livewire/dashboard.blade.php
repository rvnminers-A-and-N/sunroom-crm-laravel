@section('page-title', 'Dashboard')

<div>
    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Total Contacts" :value="$totalContacts" color="emerald-500">
            <x-slot name="icon">
                <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card label="Total Companies" :value="$totalCompanies" color="blue-500">
            <x-slot name="icon">
                <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card label="Pipeline Value" :value="'$' . number_format($pipelineValue)" color="brand-orange">
            <x-slot name="icon">
                <svg class="w-6 h-6 text-brand-orange" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card label="Won Revenue" :value="'$' . number_format($wonRevenue)" color="emerald-500">
            <x-slot name="icon">
                <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0016.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.003 6.003 0 01-5.54 0" />
                </svg>
            </x-slot>
        </x-stat-card>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Pipeline Chart --}}
        <div class="xl:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-text-primary mb-4">Deal Pipeline</h2>
            <div x-data="{
                stages: @js($stages->map(fn($s) => $s->value)),
                counts: @js($stages->map(fn($s) => (int) ($dealsByStage[$s->value]->count ?? 0))),
                values: @js($stages->map(fn($s) => (float) ($dealsByStage[$s->value]->total_value ?? 0))),
                colors: ['#F4C95D', '#F9A66C', '#F76C6C', '#02795F', '#015F4E', '#6B7280'],
                chart: null,
                init() {
                    this.chart = new Chart(this.$refs.canvas, {
                        type: 'bar',
                        data: {
                            labels: this.stages,
                            datasets: [{
                                label: 'Deal Count',
                                data: this.counts,
                                backgroundColor: this.colors,
                                borderRadius: 6,
                                barPercentage: 0.6,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        afterLabel: (ctx) => '$' + this.values[ctx.dataIndex].toLocaleString()
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1 },
                                    grid: { color: '#f3f4f6' }
                                },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }
            }">
                <div class="h-64">
                    <canvas x-ref="canvas"></canvas>
                </div>
            </div>

            {{-- Stage legend --}}
            <div class="flex flex-wrap gap-4 mt-4 pt-4 border-t border-gray-100">
                @foreach($stages as $i => $stage)
                    @php
                        $stageColors = ['Lead' => '#F4C95D', 'Qualified' => '#F9A66C', 'Proposal' => '#F76C6C', 'Negotiation' => '#02795F', 'Won' => '#015F4E', 'Lost' => '#6B7280'];
                        $data = $dealsByStage[$stage->value] ?? null;
                    @endphp
                    <div class="flex items-center gap-2 text-sm">
                        <div class="w-3 h-3 rounded-full" style="background: {{ $stageColors[$stage->value] }}"></div>
                        <span class="text-gray-600">{{ $stage->value }}</span>
                        <span class="font-semibold text-text-primary">{{ $data->count ?? 0 }}</span>
                        @if($data && $data->total_value > 0)
                            <span class="text-gray-400">${{ number_format($data->total_value) }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Recent Activities --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-text-primary mb-4">Recent Activity</h2>

            @if($recentActivities->isEmpty())
                <p class="text-gray-500 text-sm text-center py-8">No activities yet.</p>
            @else
                <div class="space-y-4">
                    @foreach($recentActivities as $activity)
                        <div class="flex gap-3">
                            <div class="mt-0.5 shrink-0">
                                <x-activity-type-icon :type="$activity->type" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-text-primary truncate">{{ $activity->subject }}</p>
                                <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                                    @if($activity->contact)
                                        <span>{{ $activity->contact->full_name }}</span>
                                        <span>&middot;</span>
                                    @endif
                                    <span>{{ $activity->occurred_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
