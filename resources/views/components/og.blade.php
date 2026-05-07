<meta property="og:title" content="{{ $data['title'] }}">
<meta property="og:description" content="{{ $data['description'] }}">
<meta property="og:type" content="{{ $data['type'] }}">
<meta property="og:url" content="{{ $data['url'] }}">
@if(! empty($data['image']))
    <meta property="og:image" content="{{ $data['image'] }}">
@endif
