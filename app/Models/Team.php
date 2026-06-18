<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = ['name', 'login_slug', 'access_code'];

    public function getRouteKeyName(): string
    {
        return 'login_slug';
    }

    public function records(): HasMany
    {
        return $this->hasMany(ApRecord::class);
    }
}
