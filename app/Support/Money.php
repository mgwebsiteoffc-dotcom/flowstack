<?php

namespace App\Support;

class Money
{
    /**
     * Render a currency figure wrapped in maskable spans.
     *
     * Normally shows the formatted amount; when <body> carries the
     * `finance-masked` class (toggled from the topbar) the value is
     * hidden and a placeholder is shown instead.
     */
    public static function format($value, int $decimals = 0, string $symbol = '₹'): string
    {
        $formatted = $symbol.number_format((float) $value, $decimals);

        return '<span class="money"><span class="money-value">'.$formatted.'</span>'
            .'<span class="money-mask" aria-hidden="true">'.$symbol.' ••••••</span></span>';
    }
}
