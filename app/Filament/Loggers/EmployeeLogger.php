<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class EmployeeLogger extends Logger
{
    public static ?string $model = Employee::class;

    public static function getLabel(): string|Htmlable|null
    {
        return EmployeeResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('name'),
                Field::make('email'),
                Field::make('phone'),
                Field::make('employment_start_date'),
                Field::make('active')
                    ->boolean(),
                Field::make('department.name'),
                Field::make('designation.name'),
                Field::make('bank.name'),
                Field::make('account_number'),
                Field::make('pay_scale.name'),
                Field::make('branch.name'),
                Field::make('manager.name'),
            ])
            ->relationManagers([
            ]);
    }

    public function transaction_payroll_error($record, \Throwable $e): void
    {
        $attributes = [
            'employee_name' => $record->name,
            'error_message' => $e->getMessage(),
        ];
        $this->log(
            event: 'payroll_generation_error'
        );
        \Log::error("Error processing payroll for Employee {$record->name}: {$e->getMessage()}");
    }
}
