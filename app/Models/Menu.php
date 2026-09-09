<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Menu extends Model
{
    protected $table = 'menus';

    protected $fillable = [
        'menu',
        'request_route',
        'status',
        'route',
        'urutan',
        'icon',
    ];
    public function scopeStatus($query)
    {
        return $query->where('status', 'enabled');
    }
    public function scopeUrutan($query)
    {
        $query->when(
            fn($query) => $query->orderBy('urutan', 'ASC')
        );
    }
    public function subMenus()
    {
        return $this->hasMany(SubMenu::class, 'menu_id')->urutan()->status();
    }
}
