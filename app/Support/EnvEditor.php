<?php

namespace App\Support;

/**
 * Minimal .env editor: sets/updates KEY=VALUE pairs in the .env file
 * without touching comments, ordering or anything else. Used by the
 * Settings > Email (Gmail) page so mail can be configured from the UI.
 */
class EnvEditor
{
    /**
     * @param  array<string, string|null>  $pairs
     */
    public static function set(array $pairs): void
    {
        $path = base_path('.env');
        $raw = file_exists($path) ? file_get_contents($path) : '';
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);
        $lines = $raw === '' ? [] : explode("\n", $raw);

        foreach ($pairs as $key => $value) {
            $found = false;
            foreach ($lines as $i => $line) {
                $trimmed = ltrim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '#') || ! str_contains($trimmed, '=')) {
                    continue;
                }
                $existingKey = trim(explode('=', $trimmed, 2)[0]);
                if ($existingKey !== $key) {
                    continue;
                }
                $lines[$i] = $key.'='.self::encode((string) $value);
                $found = true;
                break;
            }
            if (! $found) {
                $lines[] = $key.'='.self::encode((string) $value);
            }
        }

        file_put_contents($path, implode("\n", $lines)."\n");
    }

    private static function encode(string $value): string
    {
        if ($value === '' || $value === 'null') {
            return 'null';
        }
        // Quote values that contain characters the .env parser treats specially.
        if (preg_match('/[\s#"\'\\\\]/', $value)) {
            return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
        }

        return $value;
    }
}
