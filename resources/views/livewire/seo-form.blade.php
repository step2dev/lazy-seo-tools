<div class="lazy-seo-form rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5">
        <h2 class="text-lg font-semibold text-slate-950">SEO Form</h2>
        <p class="mt-1 text-sm text-slate-500">Manage page metadata without leaving the current screen.</p>
    </div>

    <div class="grid gap-4">
        <label class="grid gap-1.5 text-sm font-medium text-slate-700">
            URL
            <input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 shadow-sm outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-100" type="text" wire:model.defer="url" placeholder="https://example.com/page">
        </label>

        <label class="grid gap-1.5 text-sm font-medium text-slate-700">
            Title
            <input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 shadow-sm outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-100" type="text" wire:model.defer="title" placeholder="Title">
        </label>

        <label class="grid gap-1.5 text-sm font-medium text-slate-700">
            Description
            <textarea class="min-h-28 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 shadow-sm outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-100" wire:model.defer="description" placeholder="Description"></textarea>
        </label>

        <label class="grid gap-1.5 text-sm font-medium text-slate-700">
            Keywords
            <input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 shadow-sm outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-100" type="text" wire:model.defer="keywords" placeholder="Keywords">
        </label>

        <div>
            <button class="inline-flex items-center rounded-xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-200" type="button" wire:click="save">Save</button>
        </div>
    </div>
</div>
