<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Part extends Model
{
    protected $guarded = [];

    protected $casts = ['status' => 'boolean'];

    public function officials(): HasMany
    {
        return $this->hasMany(Official::class);
    }
}
