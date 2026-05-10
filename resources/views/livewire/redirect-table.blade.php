<div class="lazy-seo-redirects grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-slate-950">Redirects</h2>

    <div class="overflow-auto rounded-2xl border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Old URL</th>
                    <th class="px-4 py-3">New URL</th>
                    <th class="px-4 py-3">Code</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($redirects as $redirect)
                    <tr>
                        <td class="break-all px-4 py-3 text-slate-700">{{ $redirect->old_url }}</td>
                        <td class="break-all px-4 py-3 text-slate-700">{{ $redirect->new_url }}</td>
                        <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-950">{{ $redirect->status_code }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
