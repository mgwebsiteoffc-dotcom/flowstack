<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\TrackingLink;
use App\Models\TrackingPixel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Manage tracking pixels (Meta/FB, GA4, TikTok, Google Ads, custom) that are
 * injected site-wide, plus a library of campaign tracking URLs.
 */
class SuperAdminTrackingController extends Controller
{
    public function index()
    {
        $pixels = TrackingPixel::orderBy('priority')->get();
        $links = TrackingLink::orderBy('name')->get();

        return view('super-admin.tracking', compact('pixels', 'links'));
    }

    public function storePixel(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['required', 'in:'.implode(',', TrackingPixel::PROVIDERS)],
            'placement' => ['required', 'in:head,body'],
            'code' => ['required', 'string'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        TrackingPixel::create($validated + [
            'is_active' => $request->boolean('is_active', true),
            'priority' => $validated['priority'] ?? 0,
        ]);

        $this->clearCache();

        return back()->with('success', 'Tracking pixel added and will be injected site-wide.');
    }

    public function togglePixel(TrackingPixel $pixel)
    {
        $pixel->update(['is_active' => ! $pixel->is_active]);
        $this->clearCache();

        return back()->with('success', $pixel->is_active ? 'Pixel enabled.' : 'Pixel disabled.');
    }

    public function destroyPixel(TrackingPixel $pixel)
    {
        $pixel->delete();
        $this->clearCache();

        return back()->with('success', 'Pixel removed.');
    }

    public function storeLink(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:500'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        TrackingLink::create($validated + ['is_active' => true]);

        return back()->with('success', 'Tracking link added.');
    }

    public function destroyLink(TrackingLink $link)
    {
        $link->delete();

        return back()->with('success', 'Tracking link removed.');
    }

    protected function clearCache(): void
    {
        Cache::forget('tracking_pixels');
    }
}
