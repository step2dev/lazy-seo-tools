<div class="lazy-seo-scan-detail grid gap-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-slate-950">Scan #{{ $scan->id }}</h2>
            <p class="mt-1 break-all text-sm text-slate-500">{{ $scan->start_url }} · {{ $scan->created_at?->format('Y-m-d H:i') }}</p>
        </div>
        <div class="text-right">
            <strong class="text-3xl font-bold text-slate-950">{{ $scan->score }}/100</strong>
            <div @class(['text-sm font-medium', 'text-emerald-600' => $scan->score_delta >= 0, 'text-red-600' => $scan->score_delta < 0])>{{ $scan->score_delta >= 0 ? '+' : '' }}{{ $scan->score_delta }}</div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">
        @foreach([
            'Pages' => $scan->pages_count,
            'Open' => $openIssues,
            'Errors' => $criticalIssues,
            'Warnings' => $warningIssues,
            'Notices' => $noticeIssues,
            'Ignored' => $ignoredIssues,
            'Resolved' => $manuallyResolvedIssues,
        ] as $label => $value)
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <strong class="text-xl font-semibold text-slate-950">{{ $value }}</strong>
                <span class="mt-1 block text-sm text-slate-500">{{ $label }}</span>
            </div>
        @endforeach
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <select wire:model.live="severity" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none focus:border-slate-500 focus:ring-4 focus:ring-slate-100">
            <option value="">All severities</option>
            <option value="error">Errors</option>
            <option value="warning">Warnings</option>
            <option value="notice">Notices</option>
        </select>
        <select wire:model.live="status" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none focus:border-slate-500 focus:ring-4 focus:ring-slate-100">
            <option value="">All statuses</option>
            <option value="open">Open</option>
            <option value="resolved">Resolved</option>
            <option value="ignored">Ignored</option>
        </select>
        <select wire:model.live="type" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none focus:border-slate-500 focus:ring-4 focus:ring-slate-100">
            <option value="">All types</option>
            @foreach($issueTypes as $issueType)
                <option value="{{ $issueType }}">{{ $issueType }}</option>
            @endforeach
        </select>
        <input wire:model.live.debounce.300ms="search" placeholder="Search URL, type, message" class="min-w-64 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none focus:border-slate-500 focus:ring-4 focus:ring-slate-100">
    </div>

    <div class="flex flex-wrap gap-2">
        <button type="button" wire:click="markSelectedResolved" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Mark resolved</button>
        <button type="button" wire:click="ignoreSelected" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Ignore</button>
        <button type="button" wire:click="reopenSelected" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Reopen</button>
    </div>

    <div class="overflow-auto rounded-2xl border border-slate-200">
        <table class="min-w-[980px] w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3"></th>
                    <th class="px-4 py-3">Severity</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">URL</th>
                    <th class="px-4 py-3">Message</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($issues as $issue)
                    <tr>
                        <td class="px-4 py-3"><input class="rounded border-slate-300" type="checkbox" wire:model.live="selected.{{ $issue->id }}"></td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-700">{{ $issue->severity }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-700">{{ $issue->status }}</td>
                        <td class="whitespace-nowrap px-4 py-3"><code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-700">{{ $issue->type }}</code></td>
                        <td class="break-all px-4 py-3 text-slate-700">{{ $issue->url ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $issue->message }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <button class="text-sm font-medium text-slate-950 underline underline-offset-4" type="button" wire:click="markIssueResolved({{ $issue->id }})">Resolve</button>
                            <button class="ml-3 text-sm font-medium text-slate-950 underline underline-offset-4" type="button" wire:click="ignoreIssue({{ $issue->id }})">Ignore</button>
                            <button class="ml-3 text-sm font-medium text-slate-950 underline underline-offset-4" type="button" wire:click="reopenIssue({{ $issue->id }})">Reopen</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">No issues found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $issues->links() }}
</div>
