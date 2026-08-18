<?php

namespace App\Support;

/**
 * Brand helpers for the public website:
 *  - palette(): derive a full indigo/purple shade palette from one hex accent
 *  - screenshot(): path of a real dashboard screenshot when uploaded
 */
class Brand
{
    public static function accent(): string
    {
        return (string) config('brand.accent', '#4f46e5');
    }

    public static function secondary(): string
    {
        return (string) config('brand.accent_secondary') ?: static::mix(self::accent(), '#a855f7', 60);
    }

    /**
     * Full palette keyed by shade (50..900) derived from the accent + white/black.
     *
     * @return array<string, string>
     */
    public static function palette(): array
    {
        $base = static::hexToRgb(static::accent());

        // Shade -> how far to mix toward white (light) or black (dark).
        $shades = [
            '50' => ['white', 0.94], '100' => ['white', 0.85], '200' => ['white', 0.70],
            '300' => ['white', 0.50], '400' => ['white', 0.25],
            '500' => ['black', 0.08],
            '600' => ['none', 0],
            '700' => ['black', 0.18], '800' => ['black', 0.32], '900' => ['black', 0.48],
        ];

        $palette = [];
        foreach ($shades as $shade => [$dir, $amount]) {
            if ($dir === 'none') {
                $palette[$shade] = static::accent();
                continue;
            }
            $target = $dir === 'white' ? [255, 255, 255] : [0, 0, 0];
            $r = (int) round($base[0] + ($target[0] - $base[0]) * $amount);
            $g = (int) round($base[1] + ($target[1] - $base[1]) * $amount);
            $b = (int) round($base[2] + ($target[2] - $base[2]) * $amount);
            $palette[$shade] = sprintf('#%02x%02x%02x', max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));
        }

        return $palette;
    }

    /**
     * Real screenshot URL when uploaded, else null (caller falls back to mockup).
     */
    public static function screenshot(string $key): ?string
    {
        $name = config('brand.screenshot_'.$key);

        if (! $name) {
            return null;
        }

        $path = public_path('screenshots/'.$name);

        return is_file($path) ? asset('screenshots/'.$name) : null;
    }

    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    public static function mix(string $a, string $b, int $percent): string
    {
        $ra = static::hexToRgb($a);
        $rb = static::hexToRgb($b);

        return sprintf('#%02x%02x%02x',
            (int) round($ra[0] + ($rb[0] - $ra[0]) * $percent / 100),
            (int) round($ra[1] + ($rb[1] - $ra[1]) * $percent / 100),
            (int) round($ra[2] + ($rb[2] - $ra[2]) * $percent / 100)
        );
    }
}
