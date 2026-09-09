<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table ='locations';
    protected $fillable=[
        'name','status'
    ];

     public function scopeSearch($query, $term)
    {
        return $query->where('name',  'like', '%' . $term . '%');
    }
}
