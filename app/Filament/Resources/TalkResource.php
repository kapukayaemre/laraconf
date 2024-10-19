<?php

namespace App\Filament\Resources;

use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use App\Filament\Resources\TalkResource\Pages;
use App\Filament\Resources\TalkResource\RelationManagers;
use App\Models\Talk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class TalkResource extends Resource
{
    protected static ?string $model = Talk::class;

//    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Second Group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Talk::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->persistFiltersInSession() // when page changed filters not removing
            ->filtersTriggerAction(function ($action) {
                return $action->button()->label('Filters');
            })
            ->toggleColumnsTriggerAction(function ($action) {
                return $action->button()->label('Columns');
            })
            ->columns([
                /*Tables\Columns\TextInputColumn::make('title')
                    ->sortable()
                    ->rules(['required', 'max:255'])
                    ->searchable(),*/
                TextColumn::make('title')
                    ->description(function (Talk $talk) {
                        return Str::of($talk->abstract)->words(8);
                    })
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('speaker.avatar')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Speaker Avatar')
                    ->alignCenter()
                    ->circular()
                    ->defaultImageUrl(function ($record) {
                        return 'https://ui-avatars.com/api/?background=0D8ABC&color=fff&name=' . urlencode($record->speaker->name);
                    }),

                /*Tables\Columns\TextColumn::make('abstract')
                    ->wrap()
                    ->sortable()
                    ->searchable(),*/
                TextColumn::make('speaker.name')
                    ->alignEnd()
                    ->searchable()
                    ->sortable(),
                IconColumn::make('new_talk')
                    ->alignCenter()
                    ->boolean(),
                /* Tables\Columns\ToggleColumn::make('new_talk'), for live update on table */
                TextColumn::make('status')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color(function ($state) {
                        return $state->getColor();
                    }),
                IconColumn::make('length')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon(function ($state) {
                        return match ($state) {
                            TalkLength::NORMAL => 'heroicon-o-megaphone',
                            TalkLength::LIGHTNING => 'heroicon-o-bolt',
                            TalkLength::KEYNOTE => 'heroicon-o-key'
                        };
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('new_talk'),
                SelectFilter::make('speaker')
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->relationship('speaker', 'name'),
                Filter::make('has_avatar')
                    ->label('Show Only Speakers with Avatar')
                    ->toggle()
                    ->query(function (Builder $query) {
                        return $query->whereHas('speaker', function (Builder $query) {
                            $query->whereNotNull('avatar');
                        });
                    })
            ])
            ->actions([
                EditAction::make()
                    ->button()
                    ->slideOver(),
                ActionGroup::make([
                    Action::make('approve')
                        ->disabled(function (Talk $record) {
                            return $record->status === TalkStatus::APPROVED;
                        })
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function (Talk $record) {
                            $record->approve();
                        })->after(function () {
                            Notification::make()
                                ->duration(5000)
                                ->success()
                                ->title('Approved!')
                                ->body('The talk has been approved.')
                                ->send();
                        }),
                    Action::make('reject')
                        ->disabled(function (Talk $record) {
                            return $record->status === TalkStatus::REJECTED;
                        })
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Talk $record) {
                            $record->reject();
                        })->after(function () {
                            Notification::make()
                                ->duration(5000)
                                ->info()
                                ->title('Rejected')
                                ->body('This talk has been rejected!')
                                ->send();
                        })
                ])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each->approve();
                        })->after(function () {
                            Notification::make()
                                ->duration(5000)
                                ->success()
                                ->title('Approved!')
                                ->body('The talks has been approved.')
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('export')
                    ->label(function ($livewire) {
                        return "Export ({$livewire->getFilteredTableQuery()->count()})";
                    })
                    ->action(function ($livewire) {
                        dd($livewire->getFilteredTableQuery()->get());
                    })
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTalks::route('/'),
            'create' => Pages\CreateTalk::route('/create'),
//            'edit'   => Pages\EditTalk::route('/{record}/edit'),
        ];
    }
}
