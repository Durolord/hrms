<?php

namespace App\Jobs;

use App\Services\PayrollProcessingService;
use Bytexr\QueueableBulkActions\Filament\Actions\ActionResponse;
use Bytexr\QueueableBulkActions\Jobs\BulkActionJob;
use DomainException;
use Illuminate\Support\Facades\Log;
use Throwable;

class Payrolls extends BulkActionJob
{
    protected function action($record, ?array $data): ActionResponse
    {
        try {
            app(PayrollProcessingService::class)->generate($record, $data['month'] ?? now()->format('Y-m'));

            return ActionResponse::make()
                ->success()
                ->message("Payroll processed successfully for Employee {$record->name}.");
        } catch (DomainException $e) {
            return ActionResponse::make()->failure()->message($e->getMessage());
        } catch (Throwable $e) {
            Log::error("Error processing payroll for Employee {$record->name}: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");

            return ActionResponse::make()
                ->failure()
                ->message("Error processing payroll for Employee {$record->name}: {$e->getMessage()}");
        }
    }
}
