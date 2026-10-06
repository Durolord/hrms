<?php

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
| The model factories are out of date with the schema, so tests build the minimum
| parent rows directly.
*/

function makeEmployee(array $attributes = [], float $basicSalary = 100000): App\Models\Employee
{
    static $n = 0;
    $n++;

    return Illuminate\Database\Eloquent\Model::unguarded(function () use ($attributes, $basicSalary, &$n) {
        $payScale = App\Models\PayScale::firstOrCreate(['name' => 'Test Scale '.$basicSalary], ['basic_salary' => $basicSalary]);
        $designation = App\Models\Designation::firstOrCreate(['name' => 'Test Designation '.$basicSalary], ['pay_scale_id' => $payScale->id]);
        $department = App\Models\Department::firstOrCreate(['name' => 'Test Department']);
        $branch = App\Models\Branch::firstOrCreate(['name' => 'Test Branch']);

        return App\Models\Employee::create(array_merge([
            'name' => "Test Employee {$n}",
            'email' => "employee{$n}@example.test",
            'employment_start_date' => '2020-01-01',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'branch_id' => $branch->id,
        ], $attributes));
    });
}

function makeLeaveType(array $attributes = []): App\Models\LeaveType
{
    static $n = 0;
    $n++;

    return Illuminate\Database\Eloquent\Model::unguarded(fn () => App\Models\LeaveType::create(array_merge(['name' => "Test Leave {$n}", 'max_days' => 20, 'deduction_amount' => 0], $attributes)));
}
