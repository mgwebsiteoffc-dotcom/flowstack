@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
    'canonical' => null,
    'jsonLd' => null,
])
@php
    $siteName = config('app.name', 'Agency OS');
    $pageTitle = $title ? $title.' · '.$siteName : $siteName.' — Run your agency on autopilot';
    $pageDesc = $description ?: 'Agency OS is the all-in-one agency management platform: clients, projects, tasks, leads, proposals, invoicing, reporting and a client portal.';
    $pageImage = $image ?: url('/favicon.svg');
    $pageUrl = $canonical ?: url()->current();
@endphp
<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDesc }}">
<link rel="canonical" href="{{ $pageUrl }}">

<!-- Open Graph -->
<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDesc }}">
<meta property="og:url" content="{{ $pageUrl }}">
<meta property="og:image" content="{{ $pageImage }}">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDesc }}">
<meta name="twitter:image" content="{{ $pageImage }}">

<meta name="robots" content="index, follow">

@if ($jsonLd)
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endif
