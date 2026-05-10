<div class="lazy-seo-analyzer grid gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex flex-wrap items-center gap-3 text-sm text-slate-700">
        <span class="font-medium">SEO Score:</span>
        <span @class([
            'rounded-full px-3 py-1 text-xs font-semibold text-white',
            'bg-emerald-600' => $result['grade'] === 'green',
            'bg-amber-500' => $result['grade'] === 'orange',
            'bg-red-600' => ! in_array($result['grade'], ['green', 'orange'], true),
        ])>{{ $result['score'] }} / 50</span>
    </div>

    @if (count($result['warnings']))
        <ul class="list-disc space-y-1 pl-5 text-sm text-slate-500">
            @foreach($result['warnings'] as $warning)
                <li>{{ $warning }}</li>
            @endforeach
        </ul>
    @else
        <p class="text-sm font-medium text-emerald-600">Все ок ✅</p>
    @endif
</div>
