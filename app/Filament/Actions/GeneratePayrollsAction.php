<?php

namespace App\Filament\Actions;

use App\Jobs\Payrolls;
use App\Models\Employee;
use Bytexr\QueueableBulkActions\Enums\BulkActions\TypeEnum;
use Bytexr\QueueableBulkActions\Jobs\BulkActionSetupJob;
use Bytexr\QueueableBulkActions\Support\Config;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class GeneratePayrollsAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();
        $this
            ->label('Generate Payrolls')
            ->modalHeading('Generate Payrolls')
            ->modalButton('Start Processing')
            ->icon('heroicon-o-currency-dollar')
            ->form([
                Flatpickr::make('month')
                    ->default(now()->format('Y-m'))
                    ->helperText('Select the payroll month')
                    ->maxDate(now()->format('Y-m'))
                    ->monthSelect()
                    ->required(),
            ])
            ->action(fn (array $data) => $this->processPayrollJobs($data['month']));
    }

    /**
     * Process payroll generation jobs for all active employees.
     */
    protected function processPayrollJobs(string $month): void
    {
        $employees = Employee::where('active', true);
        if (! auth()->user()->can('view_outside_branch_employee')) {
            $employees->where('branch_id', auth()->user()->employee->branch->id);
        }
        $employees = $employees->get();
        $bulkAction = Config::bulkActionModel()::query()->create([
            'name' => 'Generate Payrolls',
            'type' => TypeEnum::TABLE,
            'identifier' => 'App\Filament\Resources\PayrollResource\Pages\ListPayrolls',
            'job' => Payrolls::class,
            'user_id' => Auth::id(),
            'total_records' => $employees->count(),
            'data' => ['month' => $month],
        ]);
        BulkActionSetupJob::dispatch($bulkAction, $employees);
    }
}
