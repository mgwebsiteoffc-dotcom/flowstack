<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Expense::with('category', 'client', 'addedBy');

        if ($user->isAccountManager()) {
            $clientIds = Client::where('account_manager_id', $user->id)->pluck('id');
            $query->whereIn('client_id', $clientIds);
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($clientId = $request->input('client_id')) {
            $query->where('client_id', $clientId);
        }

        if ($from = $request->input('from')) {
            $query->where('expense_date', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->where('expense_date', '<=', $to);
        }

        $expenses = $query->latest()->paginate(config('tenancy.pagination_size'))->withQueryString();
        $categories = ExpenseCategory::orderBy('name')->get();
        $clients = Client::orderBy('company_name')->get();
        $total = (clone $query)->sum('amount');

        if ($request->input('export')) {
            $rows = $expenses->map(fn ($e) => [
                'Title' => $e->title,
                'Category' => $e->category?->name,
                'Client' => $e->client?->company_name,
                'Amount' => $e->amount,
                'Date' => $e->expense_date->toDateString(),
                'Billable' => $e->is_billable ? 'Yes' : 'No',
            ])->toArray();

            return Excel::download(new \App\Exports\ArrayExport($rows, ['Title', 'Category', 'Client', 'Amount', 'Date', 'Billable']), 'expenses-'.now()->format('Y-m-d').'.xlsx');
        }

        return view('finance.expenses.index', compact('expenses', 'categories', 'clients', 'total'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'category_id' => ['nullable', 'exists:expense_categories,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'description' => ['nullable', 'string'],
            'is_billable' => ['sometimes', 'boolean'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $data = $validated;
        $data['tenant_id'] = app('currentTenant')->id;
        $data['added_by'] = auth()->id();
        unset($data['receipt']);

        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('tenants/'.app('currentTenant')->id.'/expenses/receipts', 'tenant');
            $data['receipt_file'] = $path;
        }

        Expense::create($data);

        return back()->with('success', 'Expense recorded.');
    }

    public function destroy(Expense $expense)
    {
        $this->authorize('update', $expense->client ?? new Client);

        $expense->delete();

        return back()->with('success', 'Expense deleted.');
    }
}
