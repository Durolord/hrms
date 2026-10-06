<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Services\PayrollProcessingService;
use DomainException;
use Illuminate\Console\Command;
use Throwable;

class DispatchGeneratePayrollsBatch extends Command
{
    protected $signature = 'app:dispatch-generate-payrolls-batch {--month= : Month to generate (Y-m); defaults to the current month}';

    protected $description = 'Generate draft (Pending) payrolls for every active employee. Finalised payrolls are skipped.';

    public function handle(PayrollProcessingService $service): int
    {
        $month = $this->option('month') ?: now()->format('Y-m');
        $generated = $skipped = $failed = 0;

        Employee::where('active', true)->with('designation.pay_scale')->each(function (Employee $employee) use ($service, $month, &$generated, &$skipped, &$failed) {
            try {
                $service->generate($employee, $month);
                $generated++;
            } catch (DomainException $e) {
                $skipped++;
            } catch (Throwable $e) {
                $failed++;
                report($e);
            }
        });

        $this->info("Payrolls for {$month}: {$generated} generated, {$skipped} skipped, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
