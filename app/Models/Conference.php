<?php

namespace App\Models;

use App\Enums\Region;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conference extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'region' => Region::class,
        'venue_id' => 'integer',
    ];

    public static function getForm(): array
    {
        return [
            Tabs::make()
                ->columnSpanFull()
                ->tabs([
                    Tabs\Tab::make('Conference Details')
                        ->schema([
                            Section::make('Conference Details')
                                // ->aside() description left and form right side
                                ->description('Some description about conference section')
                                ->icon('heroicon-o-information-circle')
                                ->columns(2) // ['md' => 2, 'lg' => 3] responsive layout
                                ->collapsible()
                                ->schema([
                                    TextInput::make('name')
                                        ->columnSpanFull()
                                        ->label('Conference Name')
                                        ->default('My Conference')
                                        ->helperText('The name of the conference.')
                                        ->required()
                                        ->maxLength(60),
                                    RichEditor::make('description')
                                        ->columnSpanFull()
                                        ->disableToolbarButtons(['italic'])
                                        ->required(),
                                    DateTimePicker::make('start_date')
                                        ->displayFormat('d.m.Y H:i:s')
                                        ->native(false)
                                        ->required(),
                                    DateTimePicker::make('end_date')
                                        ->displayFormat('d.m.Y H:i:s')
                                        ->native(false)
                                        ->required(),
                                    Fieldset::make('Status')
                                        ->columns(2)
                                        ->schema([
                                            Select::make('status')
                                                ->placeholder('Select Status')
                                                ->label('')
                                                ->options([
                                                    'draft' => 'Draft',
                                                    'published' => 'Published',
                                                    'archived' => 'Archived'
                                                ])
                                                ->required(),
                                            Toggle::make('is_published')
                                                ->default(true)
                                        ])
                                ])
                        ]),
                    Tabs\Tab::make('Location')
                        ->schema([
                            Section::make('Location')
                                ->collapsible()
                                ->columns(2)
                                ->schema([
                                    Select::make('region')
                                        ->live()
                                        ->enum(Region::class)
                                        ->options(Region::class)
                                        ->required(),
                                    Select::make('venue_id')
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm(Venue::getForm())
                                        ->editOptionForm(Venue::getForm())
                                        ->relationship('venue', 'name', modifyQueryUsing: function (Builder $query, Get $get) {
                                            return $query->where('region', $get('region'));
                                        }),
                                ])
                        ]),
                    Tabs\Tab::make('Speakers')
                        ->schema([
                            CheckboxList::make('speakers')
                                ->searchable()
                                ->bulkToggleable()
                                ->columns(3)
                                ->relationship('speakers', 'name')
                                ->options(
                                    Speaker::all()->pluck('name', 'id')
                                )
                                ->required()
                        ])
                ]),
                // Fake Data Generator
                Actions::make([
                    Actions\Action::make('star')
                        ->label('Fill with Factory Data')
                        ->icon('heroicon-o-star')
                        ->visible(function (string $operation) {
                            if ($operation !== 'create') {
                                return false;
                            }

                            if (! app()->environment('local')) {
                                return false;
                            }

                            return true;
                        })
                        ->action(function ($livewire) {
                            $data = Conference::factory()->make()->toArray();
                            $livewire->form->fill($data);
                        })
                ])

        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class);
    }

    public function talks(): BelongsToMany
    {
        return $this->belongsToMany(Talk::class);
    }
}
