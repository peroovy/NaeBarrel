<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quality extends Model
{
    use HasFactory;

    protected $table = "qualities";

    public function items() {
        return $this->hasMany(Item::class, 'quality');
    }
}
