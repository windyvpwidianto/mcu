<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\SubMenu;
use App\Models\ExtraSubMenu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Pastikan Role Dasar Tersedia ──────────────────────────────
        $roleAdmin = Role::firstOrCreate(['name' => 'Administrator']);
        $roleMod   = Role::firstOrCreate(['name' => 'Moderator']);
        $roleMed   = Role::firstOrCreate(['name' => 'Medical Staff']);
        $roleUser  = Role::firstOrCreate(['name' => 'User']);

        // Assign role Administrator ke user pertama jika belum punya
        $firstUser = User::first();
        if ($firstUser && !$firstUser->roles()->where('role_id', $roleAdmin->id)->exists()) {
            $firstUser->roles()->attach($roleAdmin->id);
            $firstUser->role_id = $roleAdmin->id;
            $firstUser->save();
        }

        // ── 2. Kosongkan tabel menu jika ada data lama ───────────────────
        ExtraSubMenu::truncate();
        SubMenu::truncate();
        Menu::truncate();

        // ── 3. Menu: Dashboard ──────────────────────────────────────────
        Menu::create([
            'menu'          => 'Dashboard',
            'icon'          => 'home',
            'route'         => 'dashboard',
            'request_route' => 'dashboard*',
            'status'        => 'enabled',
            'urutan'        => 1,
        ]);

        // ── 4. Menu: Hazard Report ──────────────────────────────────────
        Menu::create([
            'menu'          => 'Hazard',
            'icon'          => 'exclamation-triangle',
            'route'         => 'hazard',
            'request_route' => 'hazard*',
            'status'        => 'enabled',
            'urutan'        => 2,
        ]);

        // ── 5. Menu: Laporan Insiden ────────────────────────────────────
        Menu::create([
            'menu'          => 'Laporan Insiden',
            'icon'          => 'shield-exclamation',
            'route'         => 'incident',
            'request_route' => 'incident*',
            'status'        => 'enabled',
            'urutan'        => 3,
        ]);

        // ── 6. Menu: Manhours ───────────────────────────────────────────
        Menu::create([
            'menu'          => 'Manhours',
            'icon'          => 'clock',
            'route'         => 'manhours',
            'request_route' => 'manhours*',
            'status'        => 'enabled',
            'urutan'        => 4,
        ]);

        // ── 7. Menu: WPI (Inspeksi) ─────────────────────────────────────
        $menuWpi = Menu::create([
            'menu'          => 'WPI',
            'icon'          => 'clipboard-document-check',
            'route'         => null,
            'request_route' => 'inspeksi*',
            'status'        => 'enabled',
            'urutan'        => 5,
        ]);

        SubMenu::create([
            'menu_id'       => $menuWpi->id,
            'menu'          => 'WPI Reports',
            'icon'          => 'clipboard-document-list',
            'route'         => 'wpi.list',
            'request_route' => 'inspeksi/wpi*',
            'status'        => 'enabled',
            'urutan'        => 1,
        ]);
        SubMenu::create([
            'menu_id'       => $menuWpi->id,
            'menu'          => 'Create WPI',
            'icon'          => 'plus-circle',
            'route'         => 'wpi.create',
            'request_route' => 'inspeksi/wpi/create*',
            'status'        => 'enabled',
            'urutan'        => 2,
        ]);
        SubMenu::create([
            'menu_id'       => $menuWpi->id,
            'menu'          => 'Fire Protections',
            'icon'          => 'fire',
            'route'         => 'fire-inspection-list',
            'request_route' => 'inspeksi/fire_inspection_list*',
            'status'        => 'enabled',
            'urutan'        => 3,
        ]);
        SubMenu::create([
            'menu_id'       => $menuWpi->id,
            'menu'          => 'Create Fire Protection',
            'icon'          => 'plus',
            'route'         => 'fire-inspection',
            'request_route' => 'inspeksi/fire_inspection*',
            'status'        => 'enabled',
            'urutan'        => 4,
        ]);

        // ── 8. Menu: MCU Schedule ───────────────────────────────────────
        $menuMcu = Menu::create([
            'menu'          => 'MCU Schedule',
            'icon'          => 'heart',
            'route'         => null,
            'request_route' => 'mcu*',
            'status'        => 'enabled',
            'urutan'        => 6,
        ]);

        SubMenu::create([
            'menu_id'       => $menuMcu->id,
            'menu'          => 'Dashboard MCU',
            'icon'          => 'chart-bar',
            'route'         => 'mcu.dashboard',
            'request_route' => 'mcu/dashboard*',
            'status'        => 'enabled',
            'urutan'        => 1,
        ]);
        SubMenu::create([
            'menu_id'       => $menuMcu->id,
            'menu'          => 'MCU List',
            'icon'          => 'table-cells',
            'route'         => 'mcu.list',
            'request_route' => 'mcu/list*',
            'status'        => 'enabled',
            'urutan'        => 2,
        ]);
        SubMenu::create([
            'menu_id'       => $menuMcu->id,
            'menu'          => 'Generate Schedule',
            'icon'          => 'calendar',
            'route'         => 'mcu.generate',
            'request_route' => 'mcu/generate*',
            'status'        => 'enabled',
            'urutan'        => 3,
        ]);
        SubMenu::create([
            'menu_id'       => $menuMcu->id,
            'menu'          => 'Input Result',
            'icon'          => 'pencil-square',
            'route'         => 'mcu.input-result',
            'request_route' => 'mcu/input-result*',
            'status'        => 'enabled',
            'urutan'        => 4,
        ]);
        SubMenu::create([
            'menu_id'       => $menuMcu->id,
            'menu'          => 'Doctor Review',
            'icon'          => 'check-badge',
            'route'         => 'mcu.doctor-review',
            'request_route' => 'mcu/doctor-review*',
            'status'        => 'enabled',
            'urutan'        => 5,
        ]);

        // ── 9. Menu: Event General ──────────────────────────────────────
        $menuEg = Menu::create([
            'menu'          => 'Event General',
            'icon'          => 'calendar-days',
            'route'         => null,
            'request_route' => 'event_general*',
            'status'        => 'enabled',
            'urutan'        => 7,
        ]);

        SubMenu::create([
            'menu_id'       => $menuEg->id,
            'menu'          => 'ERM Assignment',
            'icon'          => 'user-group',
            'route'         => 'event_general-ErmAssignmentManager',
            'request_route' => 'event_general/ErmAssignmentManager*',
            'status'        => 'enabled',
            'urutan'        => 1,
        ]);
        SubMenu::create([
            'menu_id'       => $menuEg->id,
            'menu'          => 'Location',
            'icon'          => 'map-pin',
            'route'         => 'event_general-location',
            'request_route' => 'event_general/location*',
            'status'        => 'enabled',
            'urutan'        => 2,
        ]);

        // ── 10. Menu: Administrator ─────────────────────────────────────
        $menuAdmin = Menu::create([
            'menu'          => 'Administrator',
            'icon'          => 'cog-6-tooth',
            'route'         => null,
            'request_route' => 'administration*',
            'status'        => 'enabled',
            'urutan'        => 8,
        ]);

        SubMenu::create([
            'menu_id'       => $menuAdmin->id,
            'menu'          => 'Perusahaan',
            'icon'          => 'building-office',
            'route'         => 'administration-companies',
            'request_route' => 'administration/companies*',
            'status'        => 'enabled',
            'urutan'        => 1,
        ]);
        SubMenu::create([
            'menu_id'       => $menuAdmin->id,
            'menu'          => 'Departemen',
            'icon'          => 'building-office-2',
            'route'         => 'administration-department',
            'request_route' => 'administration/department*',
            'status'        => 'enabled',
            'urutan'        => 2,
        ]);
        SubMenu::create([
            'menu_id'       => $menuAdmin->id,
            'menu'          => 'Kontraktor',
            'icon'          => 'truck',
            'route'         => 'administration-contractor',
            'request_route' => 'administration/contractor*',
            'status'        => 'enabled',
            'urutan'        => 3,
        ]);
        SubMenu::create([
            'menu_id'       => $menuAdmin->id,
            'menu'          => 'Bisnis Unit',
            'icon'          => 'briefcase',
            'route'         => 'administration-Business-Units',
            'request_route' => 'administration/business_unit*',
            'status'        => 'enabled',
            'urutan'        => 4,
        ]);
        SubMenu::create([
            'menu_id'       => $menuAdmin->id,
            'menu'          => 'Departemen Group',
            'icon'          => 'rectangle-group',
            'route'         => 'administration-department-group',
            'request_route' => 'administration/department-group*',
            'status'        => 'enabled',
            'urutan'        => 5,
        ]);
        SubMenu::create([
            'menu_id'       => $menuAdmin->id,
            'menu'          => 'Kustodian',
            'icon'          => 'user-circle',
            'route'         => 'administration-custodian',
            'request_route' => 'administration/custodian*',
            'status'        => 'enabled',
            'urutan'        => 6,
        ]);
        SubMenu::create([
            'menu_id'       => $menuAdmin->id,
            'menu'          => 'Lokasi',
            'icon'          => 'map-pin',
            'route'         => 'location',
            'request_route' => 'administration/location*',
            'status'        => 'enabled',
            'urutan'        => 7,
        ]);

        // SubMenu dengan ExtraSubMenu: Manajemen Menu
        $subManajemenMenu = SubMenu::create([
            'menu_id'       => $menuAdmin->id,
            'menu'          => 'Manajemen Menu',
            'icon'          => 'bars-3',
            'route'         => null,
            'request_route' => 'administration/menu*',
            'status'        => 'enabled',
            'urutan'        => 8,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subManajemenMenu->id,
            'menu'        => 'Menu',
            'route'       => 'administration-menu',
            'status'      => 'enabled',
            'urutan'      => 1,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subManajemenMenu->id,
            'menu'        => 'Sub Menu',
            'route'       => 'administration-menu-submenu',
            'status'      => 'enabled',
            'urutan'      => 2,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subManajemenMenu->id,
            'menu'        => 'Extra Sub Menu',
            'route'       => 'administration-menu-extrasubmenu',
            'status'      => 'enabled',
            'urutan'      => 3,
        ]);

        // SubMenu dengan ExtraSubMenu: User Management
        $subUserMgmt = SubMenu::create([
            'menu_id'       => $menuAdmin->id,
            'menu'          => 'User Management',
            'icon'          => 'users',
            'route'         => null,
            'request_route' => 'administration/userManager*',
            'status'        => 'enabled',
            'urutan'        => 9,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subUserMgmt->id,
            'menu'        => 'Roles',
            'route'       => 'roles',
            'status'      => 'enabled',
            'urutan'      => 1,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subUserMgmt->id,
            'menu'        => 'User Roles',
            'route'       => 'user_roles',
            'status'      => 'enabled',
            'urutan'      => 2,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subUserMgmt->id,
            'menu'        => 'People',
            'route'       => 'people',
            'status'      => 'enabled',
            'urutan'      => 3,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subUserMgmt->id,
            'menu'        => 'Dept User',
            'route'       => 'departmentUserManager',
            'status'      => 'enabled',
            'urutan'      => 4,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subUserMgmt->id,
            'menu'        => 'Contractor User',
            'route'       => 'contractorUserManager',
            'status'      => 'enabled',
            'urutan'      => 5,
        ]);

        // SubMenu dengan ExtraSubMenu: Event General Setup
        $subEventSetup = SubMenu::create([
            'menu_id'       => $menuAdmin->id,
            'menu'          => 'Event General Setup',
            'icon'          => 'adjustments-horizontal',
            'route'         => null,
            'request_route' => 'administration/event_general*',
            'status'        => 'enabled',
            'urutan'        => 10,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subEventSetup->id,
            'menu'        => 'Event Category',
            'route'       => 'administration-event_general-eventCategory',
            'status'      => 'enabled',
            'urutan'      => 1,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subEventSetup->id,
            'menu'        => 'Event Type',
            'route'       => 'administration-event_general-eventType',
            'status'      => 'enabled',
            'urutan'      => 2,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subEventSetup->id,
            'menu'        => 'Event Sub Type',
            'route'       => 'administration-event_general-eventSubType',
            'status'      => 'enabled',
            'urutan'      => 3,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subEventSetup->id,
            'menu'        => 'ERM Assignment',
            'route'       => 'administration-event_general-ErmAssignmentManager',
            'status'      => 'enabled',
            'urutan'      => 4,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subEventSetup->id,
            'menu'        => 'Moderator Assignment',
            'route'       => 'administration-event_general-ModeratorAssignmentManager',
            'status'      => 'enabled',
            'urutan'      => 5,
        ]);

        // SubMenu dengan ExtraSubMenu: Risk Management
        $subRisk = SubMenu::create([
            'menu_id'       => $menuAdmin->id,
            'menu'          => 'Risk Management',
            'icon'          => 'scale',
            'route'         => null,
            'request_route' => 'administration/risk*',
            'status'        => 'enabled',
            'urutan'        => 11,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subRisk->id,
            'menu'        => 'Consequence',
            'route'       => 'administration-risk-Consequence',
            'status'      => 'enabled',
            'urutan'      => 1,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subRisk->id,
            'menu'        => 'Likelihood',
            'route'       => 'administration-risk-Likelihood',
            'status'      => 'enabled',
            'urutan'      => 2,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subRisk->id,
            'menu'        => 'Matrix',
            'route'       => 'administration-risk-Matrix',
            'status'      => 'enabled',
            'urutan'      => 3,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subRisk->id,
            'menu'        => 'Assessment',
            'route'       => 'administration-risk-Assessement',
            'status'      => 'enabled',
            'urutan'      => 4,
        ]);

        // SubMenu dengan ExtraSubMenu: Workflows
        $subWorkflows = SubMenu::create([
            'menu_id'       => $menuAdmin->id,
            'menu'          => 'Workflows',
            'icon'          => 'arrow-path',
            'route'         => null,
            'request_route' => 'administration/workflows*',
            'status'        => 'enabled',
            'urutan'        => 12,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subWorkflows->id,
            'menu'        => 'Hazard Workflow',
            'route'       => 'hazard.workflows',
            'status'      => 'enabled',
            'urutan'      => 1,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subWorkflows->id,
            'menu'        => 'WPI Workflow',
            'route'       => 'wpi.workflows',
            'status'      => 'enabled',
            'urutan'      => 2,
        ]);

        // SubMenu dengan ExtraSubMenu: Equipment Master
        $subEquip = SubMenu::create([
            'menu_id'       => $menuAdmin->id,
            'menu'          => 'Equipment Master',
            'icon'          => 'wrench',
            'route'         => null,
            'request_route' => 'administration/equipment-master*',
            'status'        => 'enabled',
            'urutan'        => 13,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subEquip->id,
            'menu'        => 'Equipment',
            'route'       => 'equipment-master',
            'status'      => 'enabled',
            'urutan'      => 1,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subEquip->id,
            'menu'        => 'Inspection Checklist',
            'route'       => 'inspection-checklist',
            'status'      => 'enabled',
            'urutan'      => 2,
        ]);

        // SubMenu dengan ExtraSubMenu: Cause Analysis
        $subCause = SubMenu::create([
            'menu_id'       => $menuAdmin->id,
            'menu'          => 'Cause Analysis',
            'icon'          => 'magnifying-glass',
            'route'         => null,
            'request_route' => 'administration/cause_analysis*',
            'status'        => 'enabled',
            'urutan'        => 14,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subCause->id,
            'menu'        => 'KTA',
            'route'       => 'kta',
            'status'      => 'enabled',
            'urutan'      => 1,
        ]);
        ExtraSubMenu::create([
            'sub_menu_id' => $subCause->id,
            'menu'        => 'TTA',
            'route'       => 'tta',
            'status'      => 'enabled',
            'urutan'      => 2,
        ]);

        SubMenu::create([
            'menu_id'       => $menuAdmin->id,
            'menu'          => 'Compliances',
            'icon'          => 'shield-check',
            'route'         => 'compliances',
            'request_route' => 'administration/compliances*',
            'status'        => 'enabled',
            'urutan'        => 15,
        ]);

        SubMenu::create([
            'menu_id'       => $menuAdmin->id,
            'menu'          => 'Translation',
            'icon'          => 'language',
            'route'         => 'translation',
            'request_route' => 'administration/translation*',
            'status'        => 'enabled',
            'urutan'        => 16,
        ]);
    }
}
