<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'Products';

    protected $fillable = ['productname', 'price'];

    public $dates = ['created_at', 'updated_at'];
}
