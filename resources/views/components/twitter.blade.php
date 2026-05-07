@php($data = app('lazy-seo')->data($seo ?? null, $overrides ?? []))
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $data->title }}">
<meta name="twitter:description" content="{{ $data->description }}">
@if($data->image)
    <meta name="twitter:image" content="{{ $data->image }}">
@endif
