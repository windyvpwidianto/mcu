<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Likelihood extends Model
{
   protected $table = 'likelihoods';

    protected $fillable = [
        'level',
        'name',
        'description',
    ];
}
