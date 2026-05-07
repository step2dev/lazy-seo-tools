<div class="lazy-seo-dashboard" style="font-family: system-ui, sans-serif; display: grid; gap: 1rem;">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem;">
        <div>
            <h2 style="font-size:1.4rem; margin:0;">Lazy SEO Monitoring</h2>
            <p style="margin:.25rem 0 0; color:#64748b;">Latest crawl snapshots and SEO health.</p>
        </div>

        @if($latest)
            <strong style="font-size:2rem;">{{ $latest->score }}/100</strong>
        @endif
    </div>

    @if($latest)
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap:.75rem;">
            <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;"><strong>{{ $latest->pages_count }}</strong><br><span>Pages</span></div>
            <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;"><strong>{{ $latest->issues_count }}</strong><br><span>Issues</span></div>
            <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;"><strong>{{ $criticalIssues }}</strong><br><span>Errors</span></div>
            <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;"><strong>{{ $warningIssues }}</strong><br><span>Warnings</span></div>
            <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;"><strong>{{ $noticeIssues }}</strong><br><span>Notices</span></div>
        </div>

        <div style="border:1px solid #e2e8f0; border-radius:12px; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead style="background:#f8fafc; text-align:left;">
                    <tr>
                        <th style="padding:.75rem;">Date</th>
                        <th style="padding:.75rem;">URL</th>
                        <th style="padding:.75rem;">Score</th>
                        <th style="padding:.75rem;">Pages</th>
                        <th style="padding:.75rem;">Issues</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($scans as $scan)
                        <tr style="border-top:1px solid #e2e8f0;">
                            <td style="padding:.75rem; white-space:nowrap;">{{ $scan->created_at?->format('Y-m-d H:i') }}</td>
                            <td style="padding:.75rem;">{{ $scan->start_url }}</td>
                            <td style="padding:.75rem;"><strong>{{ $scan->score }}</strong></td>
                            <td style="padding:.75rem;">{{ $scan->pages_count }}</td>
                            <td style="padding:.75rem;">{{ $scan->issues_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="border:1px dashed #cbd5e1; border-radius:12px; padding:1rem; color:#64748b;">
            No scans yet. Run <code>php artisan lazy-seo:monitor https://example.com</code>.
        </div>
    @endif
</div>
