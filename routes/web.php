<?php

use App\Http\Controllers\HazardController;
use App\Http\Controllers\ManhoursController;
use App\Http\Controllers\WpiReportExportController;
use App\Livewire\Administration\BusinessUnit\BusinessUnits;
use App\Livewire\Administration\CauseAnalysis\Kta;
use App\Livewire\Administration\CauseAnalysis\Tta;
use App\Livewire\Administration\Companies\CompanyIndex;
use App\Livewire\Administration\Compliances\Index as CompliancesIndex;
use App\Livewire\Administration\Contractor\Contractors;
use App\Livewire\Administration\Custodian\Custodian;
use App\Livewire\Administration\Departement\Department;
use App\Livewire\Administration\DeptGroup\DepartmentGroup;
use App\Livewire\Administration\DeptGroup\Group;
use App\Livewire\Administration\EquipmentMaster\Index as EquipmentMasterIndex;
use App\Livewire\Administration\EquipmentMaster\InspectionChecklist;
use App\Livewire\Administration\EventGeneral\ErmAssignmentManager;
use App\Livewire\Administration\EventGeneral\EventCategory;
use App\Livewire\Administration\EventGeneral\EventSubType;
use App\Livewire\Administration\EventGeneral\EventType;
use App\Livewire\Administration\EventGeneral\ModeratorAssignmentManager;
use App\Livewire\Administration\Locations\Location;
use App\Livewire\Administration\Menu\ExtraSubMenu;
use App\Livewire\Administration\Menu\ListMenu;
use App\Livewire\Administration\Menu\ListSubMenu;
use App\Livewire\Administration\PartOfBody\Index as PartOfBodyIndex;
use App\Livewire\Administration\People\Compliance;
use App\Livewire\Administration\People\Details as PeopleDetails;
use App\Livewire\Administration\People\User;
use App\Livewire\Administration\RelasiContUser\ContractorUserManager;
use App\Livewire\Administration\RelasiDeptUser\DepartmentUserManager;
use App\Livewire\Administration\RiskAssessment\Assessement;
use App\Livewire\Administration\RiskConsequence\RiskConsequence;
use App\Livewire\Administration\RiskLikelihood\RiskLikelihood;
use App\Livewire\Administration\RiskMatrix\Grid;
use App\Livewire\Administration\Roles\Role;
use App\Livewire\Administration\Translation\Index as TranslationIndex;
use App\Livewire\Administration\WorkflowEvent\Hazard as WorkflowEventHazard;
use App\Livewire\Administration\WorkflowEvent\WpiWorkflowManager;
use App\Livewire\Administrator\UserRoleManager\UserRole;
use App\Livewire\Dashboard\Hazard;
use App\Livewire\Hazard\HazardDetail;
use App\Livewire\Hazard\HazardForm;
use App\Livewire\Hazard\HazardReportPanel;
use App\Livewire\Incident\Create as IncidentCreate;
use App\Livewire\Incident\Index as IncidentIndex;
use App\Livewire\Incident\Update as IncidentUpdate;
use App\Livewire\Inspection\FireInspection;
use App\Livewire\Inspection\FireInspectionEdit;
use App\Livewire\Inspection\FireInspectionList;
use App\Livewire\Manhours\Index;
use App\Livewire\Mcu\DoctorReview;
use App\Livewire\Mcu\GenerateSchedule;
use App\Livewire\Mcu\InputResult;
use App\Livewire\Mcu\McuDashboard;
use App\Livewire\Mcu\McuResultList;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Wpi\Index as WpiForm;
use App\Livewire\Wpi\WpiList;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/.well-known/assetlinks.json', function () {
    return response()->json([
        [
            "relation" => ["delegate_permission/common.handle_all_urls"],
            "target" => [
                "namespace" => "android_app",
                "package_name" => "com.archimining.tosar",
                "sha256_cert_fingerprints" => ["72:A4:4A:B3:44:C1:56:4A:8F:EF:C4:83:38:A2:DC:CA:88:E2:65:10:89:C0:88:B4:DE:79:84:6A:BC:9E:1A:4E"]
            ]
        ]
    ]);
});

Route::get('/view-document/{path}', function ($path) {
    try {
        // 1. Dekripsi string path yang dikirim dari Blade
        $decryptedPath = Crypt::decryptString($path);

        // 2. Pastikan file ada di disk storage (biasanya 'public')
        if (!Storage::disk('public')->exists($decryptedPath)) {
            abort(404, 'File tidak ditemukan.');
        }

        // 3. Ambil informasi file untuk header (Opsional, tapi bagus untuk browser)
        $mimeType = Storage::disk('public')->mimeType($decryptedPath);

        // 4. Return file sebagai response (Streaming)
        // response()->file() atau Storage::response() akan membuka file di browser (seperti PDF/Gambar)
        return Storage::disk('public')->response($decryptedPath);
    } catch (DecryptException $e) {
        // Jika link dimanipulasi oleh user, dekripsi akan gagal
        abort(403, 'Akses ditolak: Link tidak valid.');
    }
})->name('document.secure-view')->middleware('auth');

Route::get('dashboard', Hazard::class)->middleware(['auth', 'verified'])->name('dashboard');
Route::redirect('/', 'dashboard');
Route::redirect('/eventReport/hazardReportGuest/3', '/hazard/form', 301);
Route::get('hazard/form', HazardForm::class)->name('hazard-form');
Route::get('api/hazards/data', [HazardController::class, 'getExcelData'])->name('hazards.excel.data');
Route::get('api/manhours', [ManhoursController::class, 'getExcelData'])->name('manhours.excel.data');
Route::get('api/wpi-data', [WpiReportExportController::class, 'getExcelData'])->name('wpi.export.data');
Route::middleware(['auth', 'check.menu'])->group(function () {
    Route::redirect('settings', 'settings/profile');
    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
    Route::get('manhours', Index::class)->name('manhours');
    Route::get('inspeksi/fire_inspection', FireInspection::class)->name('fire-inspection');
    Route::get('inspeksi/fire_inspection_list', FireInspectionList::class)->name('fire-inspection-list');
    Route::get('inspeksi/fire-inspection/edit/{id}', FireInspectionEdit::class)->name('fire-inspection-edit');
    Route::get('inspeksi/wpi', WpiList::class)->name('wpi.list');
    Route::get('inspeksi/wpi/create', WpiForm::class)->name('wpi.create');
    Route::get('inspeksi/wpi/edit/{id?}', WpiForm::class)->name('wpi.edit');
    Route::get('hazard', HazardReportPanel::class)->name('hazard');
    Route::get('hazard/{hazard}', HazardDetail::class)->name('hazard-detail');
    Route::get('incident', IncidentIndex::class)->name('incident');
    Route::get('incident/create', IncidentCreate::class)->name('incident-create');
    Route::get('incident/detail/{id}', IncidentUpdate::class)->name('incident-detail');
});
// Bisa diakses keduanya
Route::middleware(['role:administrator,medical staff'])->group(function () {
    Route::get('mcu/generate', GenerateSchedule::class)->name('mcu.generate');
    Route::get('mcu/input-result', InputResult::class)->name('mcu.input-result');
    Route::get('mcu/doctor-review', DoctorReview::class)->name('mcu.doctor-review');
    Route::get('mcu/list', McuResultList::class)->name('mcu.list');
    Route::get('mcu/dashboard', McuDashboard::class)->name('mcu.dashboard');
});
Route::middleware(['role:administrator,moderator'])->group(function () {
     Route::get('event_general/ErmAssignmentManager', ErmAssignmentManager::class)->name('event_general-ErmAssignmentManager');
     Route::get('event_general/location', Location::class)->name('event_general-location');
});

Route::middleware(['role:Administrator'])->group(function () {
    Route::get('administration/companies', CompanyIndex::class)->name('administration-companies');
    Route::get('administration/department', Department::class)->name('administration-department');
    Route::get('administration/menu', ListMenu::class)->name('administration-menu');
    Route::get('administration/menu/submenu', ListSubMenu::class)->name('administration-menu-submenu');
    Route::get('administration/menu/extrasubmenu', ExtraSubMenu::class)->name('administration-menu-extrasubmenu');
    Route::get('administration/custodian', Custodian::class)->name('administration-custodian');
    Route::get('administration/department-group', DepartmentGroup::class)->name('administration-department-group');
    Route::get('administration/department-group/group', Group::class)->name('administration-department-group-group');
    Route::get('administration/contractor', Contractors::class)->name('administration-contractor');
    Route::get('administration/business_unit', BusinessUnits::class)->name('administration-Business-Units');
    Route::get('administration/event_general/event_category', EventCategory::class)->name('administration-event_general-eventCategory');
    Route::get('administration/event_general/event_type', EventType::class)->name('administration-event_general-eventType');
    Route::get('administration/event_general/event_sub_type', EventSubType::class)->name('administration-event_general-eventSubType');
    Route::get('administration/event_general/ErmAssignmentManager', ErmAssignmentManager::class)->name('administration-event_general-ErmAssignmentManager');
    Route::get('administration/event_general/ModeratorAssignmentManager', ModeratorAssignmentManager::class)->name('administration-event_general-ModeratorAssignmentManager');
    Route::get('administration/risk/Consequence', RiskConsequence::class)->name('administration-risk-Consequence');
    Route::get('administration/risk/Likelihood', RiskLikelihood::class)->name('administration-risk-Likelihood');
    Route::get('administration/risk/Assessement', Assessement::class)->name('administration-risk-Assessement');
    Route::get('administration/risk/Matrix', Grid::class)->name('administration-risk-Matrix');
    Route::get('administration/location', Location::class)->name('location');
    Route::get('administration/cause_analysis/kta', Kta::class)->name('kta');
    Route::get('administration/cause_analysis/tta', Tta::class)->name('tta');
    Route::get('administration/userManager/departmentUserManager', DepartmentUserManager::class)->name('departmentUserManager');
    Route::get('administration/userManager/contractorUserManager', ContractorUserManager::class)->name('contractorUserManager');
    Route::get('administration/userManager/roles', Role::class)->name('roles');
    Route::get('administration/userManager/user_roles', UserRole::class)->name('user_roles');
    Route::get('administration/userManager/people', User::class)->name('people');
    Route::get('administration/userManager/people/{id}/details', PeopleDetails::class)->name('people.details');
    Route::get('administration/userManager/people/{id}/compliance', Compliance::class)->name('people.compliance');
    Route::get('administration/workflows/hazard', WorkflowEventHazard::class)->name('hazard.workflows');
    Route::get('administration/workflows/wpi', WpiWorkflowManager::class)->name('wpi.workflows');
    Route::get('administration/equipment-master', EquipmentMasterIndex::class)->name('equipment-master');
    Route::get('administration/equipment-master/inspection-checklist', InspectionChecklist::class)->name('inspection-checklist');
    Route::get('administration/compliances', CompliancesIndex::class)->name('compliances');
    Route::get('administration/body-of-part', PartOfBodyIndex::class)->name('body-of-part');
    Route::get('administration/translation', TranslationIndex::class)->name('translation');
});
// route download storage files
require __DIR__ . '/auth.php';
