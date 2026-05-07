<div class="lazy-seo-dashboard" style="font-family: system-ui, sans-serif; display: grid; gap: 1rem;">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem;">
        <div>
            <h2 style="font-size:1.4rem; margin:0;">Lazy SEO Monitoring</h2>
            <p style="margin:.25rem 0 0; color:#64748b;">Latest crawl snapshots, issue trends and redirect usage.</p>
        </div>

        @if($latest)
            <strong style="font-size:2rem;">{{ $latest->score }}/100</strong>
        @endif
    </div>

    @if($latest)
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap:.75rem;">
            <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;"><strong>{{ $latest->pages_count }}</strong><br><span>Pages</span></div>
            <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;"><strong>{{ $latest->issues_count }}</strong><br><span>Issues</span></div>
            <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;"><strong>{{ $latest->broken_links_count + $externalBrokenLinks }}</strong><br><span>Broken links</span></div>
            <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;"><strong>{{ $criticalIssues }}</strong><br><span>Errors</span></div>
            <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;"><strong>{{ $warningIssues }}</strong><br><span>Warnings</span></div>
            <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;"><strong>{{ $noticeIssues }}</strong><br><span>Notices</span></div>
            <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;"><strong>{{ $averageScore ?? '—' }}</strong><br><span>Avg score</span></div>
        </div>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:1rem;">
            <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;">
                <h3 style="margin:0 0 .75rem; font-size:1rem;">Score history</h3>
                <div style="display:flex; align-items:end; gap:.35rem; height:110px; border-bottom:1px solid #e2e8f0; padding-top:.5rem;">
                    @foreach($scoreHistory as $scan)
                        <div title="#{{ $scan->id }} · {{ $scan->score }}/100 · {{ $scan->created_at?->format('Y-m-d H:i') }}" style="flex:1; min-width:12px; height:{{ max(4, $scan->score) }}%; background:#334155; border-radius:6px 6px 0 0;"></div>
                    @endforeach
                </div>
            </div>

            <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;">
                <h3 style="margin:0 0 .75rem; font-size:1rem;">Most common issues</h3>
                <div style="display:grid; gap:.5rem;">
                    @forelse($commonIssueTypes as $issueType)
                        <div style="display:flex; justify-content:space-between; gap:1rem;">
                            <span><code>{{ $issueType->type }}</code> <small style="color:#64748b;">{{ $issueType->severity }}</small></span>
                            <strong>{{ $issueType->aggregate }}</strong>
                        </div>
                    @empty
                        <span style="color:#64748b;">No issues in the latest scan.</span>
                    @endforelse
                </div>
            </div>

            <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;">
                <h3 style="margin:0 0 .75rem; font-size:1rem;">Top redirects</h3>
                <div style="display:grid; gap:.5rem;">
                    @forelse($topRedirects as $redirect)
                        <div style="display:grid; gap:.15rem;">
                            <strong>{{ $redirect->hits }} hits · {{ $redirect->status_code }}</strong>
                            <small style="word-break:break-all; color:#64748b;">{{ $redirect->old_url }} → {{ $redirect->new_url ?: 'gone' }}</small>
                        </div>
                    @empty
                        <span style="color:#64748b;">No redirect hits yet.</span>
                    @endforelse
                </div>
            </div>
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
                            <td style="padding:.75rem; word-break:break-all;">{{ $scan->start_url }}</td>
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
