<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['category_id', 'amount', 'description', 'date'];
    
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function category()    
    {
        return $this->belongsTo(Category::class);
    }
}
