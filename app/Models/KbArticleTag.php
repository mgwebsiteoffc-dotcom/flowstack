<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class KbArticleTag extends Model
{
 use TenantScoped;

 protected $fillable = ['tenant_id', 'name'];

 public function articles()
 {
 return $this->belongsToMany(KbArticle::class, 'kb_article_tag_pivot');
 }
}
