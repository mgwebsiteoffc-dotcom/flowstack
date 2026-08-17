<?php

namespace App\Http\Controllers;

use App\Models\Plan;

class PricingController extends Controller
{
    public function index()
    {
        $plans = Plan::query()->where('is_active', true)->orderBy('price_monthly')->get();

        return view('pricing.index', compact('plans'));
    }
}
