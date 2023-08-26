<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'Customer';

    protected $fillable = ['Title', 'Email Address', 'FirstName LastName', 'registered_since' , 'phone' ];

    public $dates = ['created_at', 'updated_at'];
}
