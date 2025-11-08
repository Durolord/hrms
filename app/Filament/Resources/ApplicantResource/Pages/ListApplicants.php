<?php

namespace App\Filament\Resources\ApplicantResource\Pages;

use App\Filament\Resources\ApplicantResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListApplicants extends ListRecords
{
    protected static string $resource = ApplicantResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    public function getTabs(): array
    {
        return [
            'applied' => Tab::make()
                ->label('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Applied')->orderBy('created_at', 'desc')),
            'interviewed' => Tab::make()
                ->label('Interviewed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Interviewed')->orderBy('created_at', 'desc')),
            'shortlisted' => Tab::make()
                ->label('Shortlisted')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Shortlisted')->orderBy('created_at', 'desc')),
            'hired' => Tab::make()
                ->label('Hired')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Hired')->orderBy('created_at', 'desc')),
            'rejected' => Tab::make()
                ->label('Rejected')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Rejected')->orderBy('created_at', 'desc')),
        ];
    }
}
