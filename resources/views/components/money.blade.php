@props(['value', 'decimals' => 0, 'symbol' => '₹'])
<span {{ $attributes->merge(['class' => 'money-wrapper']) }}>{!! \App\Support\Money::format($value, (int) $decimals, $symbol) !!}</span>
