<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class KbArticleComment extends Model
{
 use TenantScoped;

 protected $fillable = ['tenant_id', 'article_id', 'user_id', 'comment'];

 public function article()
 {
 return $this->belongsTo(KbArticle::class, 'article_id');
 }

 public function user()
 {
 return $this->belongsTo(User::class);
 }
}
