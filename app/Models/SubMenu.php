<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\ParentMenuActiveScope;

class SubMenu extends Model
{
    protected $table = 'sub_menus';

    protected $fillable = [
        'menu_id',
        'menu',
        'route',
        'request_route',
        'status',
        'urutan',
        'icon',
    ];
    protected static function booted()
{
    static::addGlobalScope(new ParentMenuActiveScope);
}
    public function Menu()
    {
        return $this->belongsTo(Menu::class);
    }
        public function ExtraSubMenu()
    {
        return $this->hasMany(ExtraSubMenu::class)->where('status', 'enabled')->urutan();
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
