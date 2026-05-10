<!doctype html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lazy SEO Dashboard</title>
    @include('lazy-seo::partials.tailwind')
    @livewireStyles
</head>
<body class="min-h-full bg-slate-50 px-4 py-8 text-slate-950 antialiased">
    <main class="mx-auto grid max-w-6xl gap-6">
        @include('lazy-seo::partials.admin-nav')
        @livewire('lazy-seo-monitoring-dashboard')
    </main>
    @livewireScripts
</body>
</html>
