<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'Orders';

    protected $fillable = ['customer', 'payed'];

    public $dates = ['created_at', 'updated_at'];
}
