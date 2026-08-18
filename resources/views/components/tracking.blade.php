@props(['placement' => 'head'])
@php
    $pixels = \Illuminate\Support\Facades\Cache::remember('tracking_pixels_'.$placement, 300, function () use ($placement) {
        return \App\Models\TrackingPixel::active()->where('placement', $placement)->get(['code']);
    });
@endphp
@foreach ($pixels as $pixel)
    {!! $pixel->code !!}
@endforeach
