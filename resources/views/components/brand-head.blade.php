{{-- Brand accent: redefines the indigo/purple Tailwind palettes from config('brand') --}}
@php
    $palette = \App\Support\Brand::palette();
    $secondary = \App\Support\Brand::secondary();
    $secPalette = (function () use ($secondary) {
        $base = \App\Support\Brand::hexToRgb($secondary);
        $shades = ['50' => 0.94, '100' => 0.85, '200' => 0.70, '300' => 0.50, '400' => 0.25, '500' => 0.08, '600' => 0, '700' => 0.18, '800' => 0.32, '900' => 0.48];
        $out = [];
        foreach ($shades as $shade => $amount) {
            if ($amount === 0) { $out[$shade] = $secondary; continue; }
            $target = $shade < 600 ? [255, 255, 255] : [0, 0, 0];
            $r = (int) round($base[0] + ($target[0] - $base[0]) * $amount);
            $g = (int) round($base[1] + ($target[1] - $base[1]) * $amount);
            $b = (int) round($base[2] + ($target[2] - $base[2]) * $amount);
            $out[$shade] = sprintf('#%02x%02x%02x', max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));
        }
        return $out;
    })();
@endphp
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                indigo: @json($palette),
                purple: @json($secPalette),
            }
        }
    }
}
</script>
