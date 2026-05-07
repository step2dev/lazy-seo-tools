<div class="lazy-seo-issues" style="font-family: system-ui, sans-serif; display:grid; gap:1rem;">
    <h2 style="font-size:1.4rem; margin:0;">SEO Issues</h2>

    <div style="display:flex; flex-wrap:wrap; gap:.75rem;">
        <select wire:model.live="scanId" style="padding:.5rem; border:1px solid #cbd5e1; border-radius:8px;">
            @foreach($scans as $scan)
                <option value="{{ $scan->id }}">#{{ $scan->id }} · {{ $scan->start_url }} · {{ $scan->created_at?->format('Y-m-d H:i') }}</option>
            @endforeach
        </select>

        <select wire:model.live="severity" style="padding:.5rem; border:1px solid #cbd5e1; border-radius:8px;">
            <option value="">All severities</option>
            <option value="error">Errors</option>
            <option value="warning">Warnings</option>
            <option value="notice">Notices</option>
        </select>

        <select wire:model.live="type" style="padding:.5rem; border:1px solid #cbd5e1; border-radius:8px;">
            <option value="">All types</option>
            @foreach($types as $issueType)
                <option value="{{ $issueType }}">{{ $issueType }}</option>
            @endforeach
        </select>
    </div>

    <div style="border:1px solid #e2e8f0; border-radius:12px; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="background:#f8fafc; text-align:left;">
                <tr>
                    <th style="padding:.75rem;">Severity</th>
                    <th style="padding:.75rem;">Type</th>
                    <th style="padding:.75rem;">URL</th>
                    <th style="padding:.75rem;">Message</th>
                </tr>
            </thead>
            <tbody>
                @forelse($issues as $issue)
                    <tr style="border-top:1px solid #e2e8f0;">
                        <td style="padding:.75rem; white-space:nowrap;">{{ $issue->severity }}</td>
                        <td style="padding:.75rem; white-space:nowrap;"><code>{{ $issue->type }}</code></td>
                        <td style="padding:.75rem; word-break:break-all;">{{ $issue->url ?: '—' }}</td>
                        <td style="padding:.75rem;">{{ $issue->message }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="padding:1rem; color:#64748b;">No issues found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $issues->links() }}
</div>
