<?php

namespace App\Models;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Speaker extends Model
{
    use HasFactory;

    protected $casts = [
        'id'             => 'integer',
        'qualifications' => 'array'
    ];

    public static function getForm(): array
    {
        return [
            TextInput::make('name')
                ->required(),
            TextInput::make('email')
                ->email()
                ->required(),
            RichEditor::make('bio')
                ->required()
                ->columnSpanFull(),
            TextInput::make('twitter_handle')
                ->required(),
            CheckboxList::make('qualifications')
                ->columnSpanFull()
                ->searchable()
                ->bulkToggleable() // Select all
                ->options([
                        'business-leader'       => 'Business Leader',
                        'charisma'              => 'Charismatic Speaker',
                        'first-time'            => 'First Time Speaker',
                        'hometown-hero'         => 'Hometown Hero',
                        'humanitarian'          => 'Works in Humanitarian Field',
                        'laracasts-contributor' => 'Laracasts Contributor',
                        'twitter-influencer'    => 'Large Twitter Following',
                        'youtube-influencer'    => 'Large Youtube Following',
                        'open-source'           => 'Open Source Creator / Maintainer',
                        'unique-perspective'    => 'Unique Perspective',
                    ]
                )
                ->descriptions([
                        'business-leader'       => 'Business Leader Description',
                        'charisma'              => 'Charismatic Speaker Description',
                        'first-time'            => 'First Time Speaker Description',
                        'hometown-hero'         => 'Hometown Hero Description',
                        'humanitarian'          => 'Works in Humanitarian Field Description',
                        'laracasts-contributor' => 'Laracasts Contributor Description',
                        'twitter-influencer'    => 'Large Twitter Following Description',
                        'youtube-influencer'    => 'Large Youtube Following Description',
                        'open-source'           => 'Open Source Creator / Maintainer Description',
                        'unique-perspective'    => 'Unique Perspective Description',
                    ]
                )
                ->columns(3)
        ];
    }

    public function conferences(): BelongsToMany
    {
        return $this->belongsToMany(Conference::class);
    }
}
