<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Lazy SEO Scan</title>@livewireStyles</head>
<body style="margin:0; padding:2rem; background:#f8fafc; color:#0f172a;">
    <main style="max-width:1300px; margin:0 auto; display:grid; gap:1.5rem;">
        <nav style="display:flex; gap:1rem; font-family:system-ui,sans-serif;">
            <a href="{{ route('lazy-seo.dashboard') }}">Dashboard</a>
            <a href="{{ route('lazy-seo.issues') }}">Issues</a>
            <a href="{{ route('lazy-seo.redirects') }}">Redirects</a>
        </nav>
        @livewire('lazy-seo-scan-detail', ['scan' => $scan])
    </main>
    @livewireScripts
</body>
</html>
