@php($data = app('lazy-seo')->data($seo ?? null, $overrides ?? []))
<meta property="og:title" content="{{ $data->title }}">
<meta property="og:description" content="{{ $data->description }}">
<meta property="og:type" content="{{ $data->type }}">
<meta property="og:url" content="{{ $data->url }}">
@if($data->image)
    <meta property="og:image" content="{{ $data->image }}">
@endif
