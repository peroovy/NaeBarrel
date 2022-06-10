<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Market extends Model
{
    use HasFactory;

    protected $table = 'market';

    protected $fillable = ['item_id', 'price', 'client_id'];

    public $timestamps = false;
}
