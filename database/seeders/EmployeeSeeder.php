<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class EmployeeSeeder extends Seeder
{
    protected $roleHierarchy = [
        'Admin' => 1,
        'HR Manager' => 2,
        'Finance Manager' => 3,
        'Department Head' => 4,
        'IT Admin' => 4,
        'Employee' => 5,
    ];

    public function run()
    {
        $branches = Branch::all();
        $designations = Designation::all();
        if ($branches->isEmpty()) {
            $this->command->error('No branches found. Please seed branches before running EmployeeSeeder.');

            return;
        }
        if ($designations->isEmpty()) {
            $this->command->error('No designations found. Please seed designations before running EmployeeSeeder.');

            return;
        }
        $departments = Department::where('status', true)
            ->whereIn('name', ['Human Resources', 'Finance', 'IT', 'Sales', 'Operations'])
            ->get();
        if ($departments->isEmpty()) {
            $this->command->error('No departments found. Please seed departments with the required list before running EmployeeSeeder.');

            return;
        }
        $branchIndex = 0;
        $users = User::with('roles')->get()->sortBy(function ($user) {
            return $this->getUserHierarchyLevel($user);
        });
        foreach ($users as $user) {
            $userLevel = $this->getUserHierarchyLevel($user);
            if ($userLevel <= 3) {
                $branch = $branches[$branchIndex % $branches->count()];
                $branchIndex++;
            } else {
                $branch = $branches->random();
            }
            $roleName = $user->roles->first() ? $user->roles->first()->name : null;
            $designation = $this->getDesignationForRole($roleName, $designations);
            if (! $designation) {
                $this->command->error("No designation found for role: {$roleName} for user: {$user->name}");

                continue;
            }
            $departmentMapping = [
                'HR Manager' => 'Human Resources',
                'Finance Manager' => 'Finance',
                'IT Admin' => 'IT',
            ];
            if (isset($departmentMapping[$roleName])) {
                $department = $departments->firstWhere('name', $departmentMapping[$roleName]);
            } else {
                $department = $departments->random();
            }
            $employee = Employee::create([
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '080'.rand(10000000, 99999999),
                'employment_start_date' => now()->subYears(rand(1, 5)),
                'active' => true,
                'user_id' => $user->id,
                'department_id' => $department->id,
                'designation_id' => $designation->id,
                'branch_id' => $branch->id,
                'manager_id' => null,
            ]);
            if ($userLevel > 1) {
                $this->assignManager($employee, $userLevel);
            }
        }
    }

    /**
     * Get the user hierarchy level based on their roles.
     */
    protected function getUserHierarchyLevel($user)
    {
        return min(
            $user->roles->map(function ($role) {
                return $this->roleHierarchy[$role->name] ?? PHP_INT_MAX;
            })->toArray()
        );
    }

    /**
     * Get an appropriate designation for the given role name.
     */
    protected function getDesignationForRole($roleName, $designations)
    {
        if (! $roleName) {
            return null;
        }
        $designation = $designations->firstWhere('name', $roleName);
        if (! $designation) {
            $designation = $designations->first(function ($item) use ($roleName) {
                return stripos($item->name, $roleName) !== false;
            });
        }

        return $designation ?? $designations->random();
    }

    /**
     * Assign a manager to the employee based on hierarchy and branch.
     */
    protected function assignManager($employee, $userLevel)
    {
        $possibleManagers = Employee::where('branch_id', $employee->branch_id)
            ->whereHas('user.roles', function ($query) use ($userLevel) {
                $query->whereIn('name', array_keys(
                    array_filter($this->roleHierarchy, fn ($level) => $level < $userLevel)
                ));
            })
            ->get();
        if ($possibleManagers->isNotEmpty()) {
            $employee->update(['manager_id' => $possibleManagers->random()->id]);
        }
    }
}
