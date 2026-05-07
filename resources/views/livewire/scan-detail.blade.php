<div class="lazy-seo-scan-detail" style="font-family: system-ui, sans-serif; display:grid; gap:1rem;">
    <div style="display:flex; justify-content:space-between; gap:1rem; align-items:flex-start;">
        <div>
            <h2 style="font-size:1.4rem; margin:0;">Scan #{{ $scan->id }}</h2>
            <p style="margin:.25rem 0 0; color:#64748b; word-break:break-all;">{{ $scan->start_url }} · {{ $scan->created_at?->format('Y-m-d H:i') }}</p>
        </div>
        <div style="text-align:right;">
            <strong style="font-size:2rem;">{{ $scan->score }}/100</strong>
            <div style="color:{{ $scan->score_delta >= 0 ? '#16a34a' : '#dc2626' }}; font-size:.85rem;">{{ $scan->score_delta >= 0 ? '+' : '' }}{{ $scan->score_delta }}</div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap:.75rem;">
        <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;"><strong>{{ $scan->pages_count }}</strong><br><span>Pages</span></div>
        <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;"><strong>{{ $openIssues }}</strong><br><span>Open</span></div>
        <div style="border:1px solid #fee2e2; border-radius:12px; padding:1rem;"><strong>{{ $criticalIssues }}</strong><br><span>Errors</span></div>
        <div style="border:1px solid #fef3c7; border-radius:12px; padding:1rem;"><strong>{{ $warningIssues }}</strong><br><span>Warnings</span></div>
        <div style="border:1px solid #dbeafe; border-radius:12px; padding:1rem;"><strong>{{ $noticeIssues }}</strong><br><span>Notices</span></div>
        <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;"><strong>{{ $ignoredIssues }}</strong><br><span>Ignored</span></div>
        <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;"><strong>{{ $manuallyResolvedIssues }}</strong><br><span>Resolved</span></div>
    </div>

    <div style="display:flex; flex-wrap:wrap; gap:.75rem; align-items:center;">
        <select wire:model.live="severity" style="padding:.5rem; border:1px solid #cbd5e1; border-radius:8px;">
            <option value="">All severities</option>
            <option value="error">Errors</option>
            <option value="warning">Warnings</option>
            <option value="notice">Notices</option>
        </select>
        <select wire:model.live="status" style="padding:.5rem; border:1px solid #cbd5e1; border-radius:8px;">
            <option value="">All statuses</option>
            <option value="open">Open</option>
            <option value="resolved">Resolved</option>
            <option value="ignored">Ignored</option>
        </select>
        <select wire:model.live="type" style="padding:.5rem; border:1px solid #cbd5e1; border-radius:8px;">
            <option value="">All types</option>
            @foreach($issueTypes as $issueType)
                <option value="{{ $issueType }}">{{ $issueType }}</option>
            @endforeach
        </select>
        <input wire:model.live.debounce.300ms="search" placeholder="Search URL, type, message" style="padding:.5rem; border:1px solid #cbd5e1; border-radius:8px; min-width:220px;">
    </div>

    <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
        <button type="button" wire:click="markSelectedResolved" style="padding:.45rem .7rem; border:1px solid #cbd5e1; border-radius:8px; background:white;">Mark resolved</button>
        <button type="button" wire:click="ignoreSelected" style="padding:.45rem .7rem; border:1px solid #cbd5e1; border-radius:8px; background:white;">Ignore</button>
        <button type="button" wire:click="reopenSelected" style="padding:.45rem .7rem; border:1px solid #cbd5e1; border-radius:8px; background:white;">Reopen</button>
    </div>

    <div style="border:1px solid #e2e8f0; border-radius:12px; overflow:auto;">
        <table style="width:100%; border-collapse:collapse; min-width:980px;">
            <thead style="background:#f8fafc; text-align:left;">
                <tr>
                    <th style="padding:.75rem;"></th>
                    <th style="padding:.75rem;">Severity</th>
                    <th style="padding:.75rem;">Status</th>
                    <th style="padding:.75rem;">Type</th>
                    <th style="padding:.75rem;">URL</th>
                    <th style="padding:.75rem;">Message</th>
                    <th style="padding:.75rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($issues as $issue)
                    <tr style="border-top:1px solid #e2e8f0;">
                        <td style="padding:.75rem;"><input type="checkbox" wire:model.live="selected.{{ $issue->id }}"></td>
                        <td style="padding:.75rem; white-space:nowrap;">{{ $issue->severity }}</td>
                        <td style="padding:.75rem; white-space:nowrap;">{{ $issue->status }}</td>
                        <td style="padding:.75rem; white-space:nowrap;"><code>{{ $issue->type }}</code></td>
                        <td style="padding:.75rem; word-break:break-all;">{{ $issue->url ?: '—' }}</td>
                        <td style="padding:.75rem;">{{ $issue->message }}</td>
                        <td style="padding:.75rem; white-space:nowrap;">
                            <button type="button" wire:click="markIssueResolved({{ $issue->id }})">Resolve</button>
                            <button type="button" wire:click="ignoreIssue({{ $issue->id }})">Ignore</button>
                            <button type="button" wire:click="reopenIssue({{ $issue->id }})">Reopen</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="padding:1rem; color:#64748b;">No issues found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $issues->links() }}
</div>
