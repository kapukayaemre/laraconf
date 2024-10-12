<?php

namespace App\Filament\Resources\TalkResource\Pages;

use App\Enums\TalkStatus;
use App\Filament\Resources\TalkResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListTalks extends ListRecords
{
    protected static string $resource = TalkResource::class;

    // This only available full panel package installed (so just table package not enough)
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Talks'),
            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', TalkStatus::APPROVED);
                }),
            'submitted' => Tab::make('Submitted')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', TalkStatus::SUBMITTED);
                }),
            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', TalkStatus::REJECTED);
            })
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
