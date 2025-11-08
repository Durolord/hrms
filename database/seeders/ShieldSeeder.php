<?php

namespace Database\Seeders;

use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $rolesWithPermissions = '[{"name":"Admin","guard_name":"web","permissions":["import_allowance","export_allowance","view_allowance","create_allowance","update_allowance","delete_allowance","delete_any_allowance","shortlist_applicant","scheduleInterview_applicant","moveStage_applicant","viewAny_applicant","create_applicant","update_applicant","delete_applicant","import_attendance","export_attendance","view_attendance","view_any_attendance","create_attendance","update_attendance","delete_attendance","recordCheckIn_attendance","recordCheckOut_attendance","recordBreak_attendance","delete_any_attendance","view_bonus","view_any_bonus","create_bonus","update_bonus","delete_bonus","delete_any_bonus","view_branch","view_any_branch","create_branch","update_branch","delete_branch","delete_any_branch","view_deduction","view_any_deduction","create_deduction","update_deduction","delete_deduction","delete_any_deduction","import_department","export_department","view_department","view_any_department","create_department","update_department","restore_department","restore_any_department","delete_department","delete_any_department","force_delete_department","force_delete_any_department","view_designation","view_any_designation","create_designation","update_designation","restore_designation","restore_any_designation","delete_designation","delete_any_designation","force_delete_designation","force_delete_any_designation","export_employee","view_employee","view_any_employee","create_employee","update_employee","delete_employee","delete_any_employee","force_delete_employee","force_delete_any_employee","view_outside_branch","approve_leave","reject_leave","override_leave","retroactive_leave","bulkApprove_leave","export_leave","view_leave","view_any_leave","create_leave","update_leave","delete_leave","delete_any_leave","export_leave::type","view_leave::type","view_any_leave::type","create_leave::type","update_leave::type","delete_leave::type","delete_any_leave::type","view_opening","view_any_opening","close_opening","create_opening","update_opening","delete_opening","delete_any_opening","activate_pay::scale","deactivate_pay::scale","linkDesignations_pay::scale","view_pay::scale","view_any_pay::scale","create_pay::scale","update_pay::scale","delete_pay::scale","delete_any_pay::scale","export_pay::scale","view_payroll","view_any_payroll","create_payroll","update_payroll","delete_any_payroll","generatePayslip_payroll","recalculate_payroll","view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","import_skill","view_skill","view_any_skill","create_skill","update_skill","delete_skill","delete_any_skill","view_user_user","view_any_user_user","update_user_user","delete_user_user","delete_any_user_user","view_bulk::action","view_any_bulk::action","view_outside_branch_employee","view_user","view_any_user","update_user","delete_user","delete_any_user","page_GeneralSettingsPage","page_Apply","page_EmployeeMediaList","page_MyAttendance","page_MyLeaves","page_MyPayrolls","page_Welcome","widget_AttendanceSummaryWidget","page_AuditLog","page_MyDocuments","view_own_attendance","view_outside_branch_leave","view_non_managed_leave","page_Dashboard","widget_OrganizationOverview","widget_PayrollSummaryChartWidget","widget_EmployeeDistributionWidget"]},{"name":"HR Manager","guard_name":"web","permissions":["import_allowance","export_allowance","view_allowance","create_allowance","update_allowance","delete_allowance","delete_any_allowance","shortlist_applicant","moveStage_applicant","viewAny_applicant","create_applicant","update_applicant","delete_applicant","import_attendance","export_attendance","view_attendance","view_any_attendance","create_attendance","update_attendance","delete_attendance","recordCheckIn_attendance","recordCheckOut_attendance","recordBreak_attendance","delete_any_attendance","view_bonus","view_any_bonus","create_bonus","update_bonus","delete_bonus","delete_any_bonus","view_branch","view_any_branch","create_branch","update_branch","delete_branch","delete_any_branch","view_bulk::action","view_deduction","view_any_deduction","create_deduction","update_deduction","delete_deduction","delete_any_deduction","import_department","export_department","view_department","view_any_department","create_department","update_department","restore_department","restore_any_department","delete_department","delete_any_department","force_delete_department","force_delete_any_department","view_designation","view_any_designation","create_designation","update_designation","restore_designation","restore_any_designation","delete_designation","delete_any_designation","force_delete_designation","force_delete_any_designation","export_employee","view_employee","view_any_employee","create_employee","update_employee","delete_employee","delete_any_employee","force_delete_employee","force_delete_any_employee","approve_leave","reject_leave","override_leave","retroactive_leave","bulkApprove_leave","export_leave","view_leave","view_any_leave","create_leave","update_leave","view_non_managed_leave","export_leave::type","view_leave::type","view_any_leave::type","create_leave::type","update_leave::type","delete_leave::type","close_opening","create_opening","update_opening","delete_opening","activate_pay::scale","deactivate_pay::scale","linkDesignations_pay::scale","view_pay::scale","view_any_pay::scale","create_pay::scale","update_pay::scale","delete_pay::scale","export_pay::scale","view_payroll","view_any_payroll","create_payroll","update_payroll","delete_any_payroll","generatePayslip_payroll","recalculate_payroll","view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","import_skill","view_skill","view_any_skill","create_skill","update_skill","delete_skill","delete_any_skill","view_user","update_user","page_MyAttendance","page_MyDocuments","page_MyLeaves","page_MyPayrolls","widget_AttendanceSummaryWidget"]},{"name":"Finance Manager","guard_name":"web","permissions":["import_allowance","export_allowance","view_allowance","create_allowance","update_allowance","delete_allowance","delete_any_allowance","view_bonus","view_any_bonus","create_bonus","update_bonus","delete_bonus","delete_any_bonus","view_deduction","view_any_deduction","create_deduction","update_deduction","delete_deduction","delete_any_deduction","view_department","view_any_department","view_designation","view_any_designation","view_employee","view_any_employee","view_leave","view_any_leave","activate_pay::scale","deactivate_pay::scale","linkDesignations_pay::scale","view_pay::scale","view_any_pay::scale","create_pay::scale","update_pay::scale","delete_pay::scale","delete_any_pay::scale","export_pay::scale","view_payroll","view_any_payroll","create_payroll","update_payroll","delete_any_payroll","generatePayslip_payroll","recalculate_payroll","page_MyDocuments","page_MyAttendance","page_AuditLog","page_MyLeaves","page_MyPayrolls"]},{"name":"Department Head","guard_name":"web","permissions":["view_allowance","view_own_attendance","view_attendance","view_bonus","view_branch","view_deduction","import_department","export_department","view_department","view_any_department","create_department","update_department","restore_department","restore_any_department","delete_department","delete_any_department","force_delete_department","force_delete_any_department","view_designation","view_any_designation","create_designation","update_designation","restore_designation","restore_any_designation","delete_designation","delete_any_designation","force_delete_designation","force_delete_any_designation","export_employee","view_employee","view_any_employee","create_employee","update_employee","delete_employee","delete_any_employee","force_delete_employee","force_delete_any_employee","approve_leave","reject_leave","override_leave","retroactive_leave","bulkApprove_leave","export_leave","view_leave","view_any_leave","create_leave","update_leave","delete_leave","delete_any_leave","view_payroll","view_skill","page_MyAttendance","page_MyDocuments","page_MyLeaves","page_MyPayrolls","widget_AttendanceSummaryWidget"]},{"name":"Employee","guard_name":"web","permissions":["view_allowance","import_attendance","export_attendance","view_attendance","view_any_attendance","create_attendance","update_attendance","delete_attendance","recordCheckIn_attendance","recordCheckOut_attendance","recordBreak_attendance","delete_any_attendance","view_bonus","view_branch","view_deduction","view_department","view_designation","view_employee","view_leave","view_pay::scale","view_payroll","view_role","view_skill","page_MyDocuments","page_MyLeaves","page_MyAttendance","page_MyPayrolls"]},{"name":"IT Admin","guard_name":"web","permissions":["view_allowance","import_attendance","export_attendance","view_attendance","view_any_attendance","create_attendance","update_attendance","delete_attendance","recordCheckIn_attendance","recordCheckOut_attendance","recordBreak_attendance","delete_any_attendance","view_bonus","view_branch","view_deduction","view_department","view_designation","view_employee","view_leave","view_pay::scale","view_payroll","view_role","view_skill","page_MyDocuments","page_MyLeaves","page_MyPayrolls","page_MyAttendance"]}]';
        $directPermissions = '[]';
        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);
        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();
            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);
                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();
                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();
            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
