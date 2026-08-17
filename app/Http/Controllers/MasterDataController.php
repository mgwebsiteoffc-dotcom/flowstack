<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\MasterItem;
use App\Models\TaskTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Master data management: expense categories, task tags and service types.
 */
class MasterDataController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        $tags = TaskTag::orderBy('name')->get();
        $services = MasterItem::where('type', 'service_type')->orderBy('order_index')->get();
        $defaultServices = \App\Models\ClientService::TYPES;

        return view('settings.master', compact('categories', 'tags', 'services', 'defaultServices'));
    }

    // --- Expense categories ---

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:expense_categories,name'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        ExpenseCategory::create([
            'tenant_id' => app('currentTenant')->id,
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#6B7280',
        ]);

        return back()->with('success', 'Expense category added.');
    }

    public function destroyCategory(ExpenseCategory $category)
    {
        $category->delete();

        return back()->with('success', 'Expense category deleted.');
    }

    // --- Task tags ---

    public function storeTag(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:task_tags,name'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        TaskTag::create([
            'tenant_id' => app('currentTenant')->id,
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#6B7280',
        ]);

        return back()->with('success', 'Task tag added.');
    }

    public function destroyTag(TaskTag $tag)
    {
        $tag->delete();

        return back()->with('success', 'Task tag deleted.');
    }

    // --- Service types ---

    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $slug = Str::slug($validated['name']);

        $exists = in_array($slug, \App\Support\ServiceCatalog::slugs(), true);

        if ($exists) {
            return back()->with('error', 'A service with this name already exists.');
        }

        MasterItem::create([
            'tenant_id' => app('currentTenant')->id,
            'type' => 'service_type',
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#6B7280',
            'meta' => ['slug' => $slug],
            'is_active' => true,
            'order_index' => MasterItem::where('type', 'service_type')->count(),
        ]);

        return back()->with('success', 'Service type added. It is now available on client, project and task forms.');
    }

    public function destroyService(MasterItem $item)
    {
        if ($item->type !== 'service_type') {
            abort(400);
        }

        $item->delete();

        return back()->with('success', 'Service type removed.');
    }
}
