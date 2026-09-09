<?php

use App\Models\Hazard;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as Trail;

/*
|--------------------------------------------------------------------------
| Hazard Breadcrumbs
|--------------------------------------------------------------------------
| Pastikan nama breadcrumb sama dengan nama route agar mudah dipanggil.
| Nama breadcrumb boleh beda dari nama route, tapi sebaiknya konsisten.
*/

// Halaman daftar hazard
Breadcrumbs::for('hazard', function (Trail $trail) {
    // Ganti 'Home' dengan nama lain jika mau
    $trail->push('Hazard List', route('hazard'));
});

// Form create hazard
Breadcrumbs::for('hazard-form', function (Trail $trail) {
    $trail->parent('hazard');
    $trail->push('Create Hazard', route('hazard-form'));
});

// Detail (toleran: menerima model atau id)
Breadcrumbs::for('hazard-detail', function (Trail $trail, $hazard) {
    $trail->parent('hazard');

    // Jika $hazard adalah id (int/string) -> ambil model
    if (is_numeric($hazard) || is_string($hazard)) {
        $hazard = Hazard::find($hazard);
    }

    if ($hazard) {
        $title = "Detail #{$hazard->no_referensi}";
        $url = route('hazard-detail', $hazard);
    } else {
        // fallback bila model tidak ditemukan (mis. saat generate dipanggil sebelum load)
        $title = "Detail";
        $url = '#';
    }

    $trail->push($title, $url);
});

// WPI List
Breadcrumbs::for('wpi.list', function (Trail $trail) {
    $trail->push('WPI Reports', route('wpi.list'));
});
// WPI Create
Breadcrumbs::for('wpi.create', function (Trail $trail) {
    $trail->parent('wpi.list');
    $trail->push('Create WPI Report', route('wpi.create'));
});
// WPI Edit
Breadcrumbs::for('wpi.edit', function (Trail $trail, $id) {
    $trail->parent('wpi.list');
    $trail->push('Edit WPI Report', route('wpi.edit', $id));
});

// fire-inspection List
Breadcrumbs::for('fire-inspection-list', function (Trail $trail) {
    $trail->push('Fire protections', route('fire-inspection-list'));
});
// fire-inspection Create
Breadcrumbs::for('fire-inspection', function (Trail $trail) {
    $trail->parent('fire-inspection-list');
    $trail->push('Create Fire Protection Report', route('fire-inspection'));
});
// fire-inspection Edit
Breadcrumbs::for('fire-inspection-edit', function (Trail $trail, $id) {
    $trail->parent('fire-inspection-list');
    $trail->push('Edit Fire Protection Report', route('fire-inspection-edit', $id));
});

// Incident
Breadcrumbs::for('incident', function ($trail) {
    $trail->push('Incident List', route('incident'));
});

//Incident > Create Incident
Breadcrumbs::for('incident-create', function ($trail,) {
    $trail->parent('incident');
    $trail->push('Create Incident', route('incident-create'));
});
Breadcrumbs::for('incident-detail', function ($trail, $id) {
    $trail->parent('incident');
    $trail->push('Detail Incident', route('incident-detail', $id));
});
