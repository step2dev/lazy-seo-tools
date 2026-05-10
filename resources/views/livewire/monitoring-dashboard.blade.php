<div class="lazy-seo-dashboard grid gap-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-slate-950">Lazy SEO Monitoring</h2>
            <p class="mt-1 text-sm text-slate-500">Latest crawl snapshots, issue trends, weak pages and redirect usage.</p>
        </div>

        @if($latest)
            <div class="text-right">
                <strong class="text-3xl font-bold text-slate-950">{{ $latest->score }}/100</strong>
                <div @class(['text-sm font-medium', 'text-emerald-600' => $latest->score_delta >= 0, 'text-red-600' => $latest->score_delta < 0])>{{ $latest->score_delta >= 0 ? '+' : '' }}{{ $latest->score_delta }} since previous scan</div>
            </div>
        @endif
    </div>

    @if($latest)
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
            @foreach([
                'Pages' => $latest->pages_count,
                'Total issues' => $latest->issues_count,
                'Broken links' => $latest->broken_links_count + $externalBrokenLinks,
                'Open errors' => $criticalIssues,
                'Open warnings' => $warningIssues,
                'Open notices' => $noticeIssues,
                'Ignored' => $ignoredIssues,
                'Avg score' => $averageScore ?? '—',
                'Pending' => $pendingScans,
                'Running' => $runningScans,
                'Failed' => $failedScans,
            ] as $label => $value)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <strong class="text-xl font-semibold text-slate-950">{{ $value }}</strong>
                    <span class="mt-1 block text-sm text-slate-500">{{ $label }}</span>
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 p-4">
                <h3 class="mb-3 text-sm font-semibold text-slate-950">Score history</h3>
                <div class="flex h-28 items-end gap-1 border-b border-slate-200 pt-2">
                    @foreach($scoreHistory as $scan)
                        <div class="min-w-3 flex-1 rounded-t-md bg-slate-700" title="#{{ $scan->id }} · {{ $scan->score }}/100 · {{ $scan->created_at?->format('Y-m-d H:i') }}" style="height: {{ max(4, $scan->score) }}%"></div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 p-4">
                <h3 class="mb-3 text-sm font-semibold text-slate-950">Most common open issues</h3>
                <div class="grid gap-2 text-sm">
                    @forelse($commonIssueTypes as $issueType)
                        <div class="flex justify-between gap-4">
                            <span><code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">{{ $issueType->type }}</code> <small class="text-slate-500">{{ $issueType->severity }}</small></span>
                            <strong>{{ $issueType->aggregate }}</strong>
                        </div>
                    @empty
                        <span class="text-slate-500">No open issues in the latest scan.</span>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 p-4">
                <h3 class="mb-3 text-sm font-semibold text-slate-950">Weakest pages</h3>
                <div class="grid gap-2 text-sm">
                    @forelse($worstPages as $page)
                        <div class="grid gap-1">
                            <strong class="text-slate-950">{{ $page->issues_count }} issues · {{ $page->errors_count }} errors</strong>
                            <small class="break-all text-slate-500">{{ $page->url }}</small>
                        </div>
                    @empty
                        <span class="text-slate-500">No weak pages detected.</span>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="overflow-auto rounded-2xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">URL</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Score</th>
                        <th class="px-4 py-3">Pages</th>
                        <th class="px-4 py-3">Issues</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($scans as $scan)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-700">{{ $scan->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="break-all px-4 py-3 text-slate-700">{{ $scan->start_url }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-700">{{ $scan->status }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-950">{{ $scan->status === 'completed' ? $scan->score : '—' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $scan->pages_count }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $scan->issues_count }}</td>
                            <td class="px-4 py-3 text-right"><a class="font-medium text-slate-950 underline underline-offset-4" href="{{ route('lazy-seo.scans.show', $scan) }}">Open</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-sm text-slate-500">
            No scans yet. Run <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-700">php artisan lazy-seo:monitor https://example.com</code>.
        </div>
    @endif
</div>
