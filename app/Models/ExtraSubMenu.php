<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtraSubMenu extends Model
{
        protected $table = 'extra_sub_menus';

    protected $fillable = [
        'sub_menu_id',
        'menu',
        'route',
         'request_route',
        'status',
        'urutan',
        'icon',
    ];
    public function SubMenu()
    {
        return $this->belongsTo(SubMenu::class);
    }
    public function scopeStatus($query)
    {
        $query->when(
            fn($query) => $query->where('status', 'enabled')
        );
    }
    public function scopeUrutan($query)
    {
        $query->when(
            fn($query) => $query->orderBy('urutan', 'ASC')
        );
    }
}
