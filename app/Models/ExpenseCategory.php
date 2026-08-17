<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use TenantScoped;

    protected $fillable = ['tenant_id', 'name', 'color'];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
