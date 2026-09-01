<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Staff\Pages;

use BackedEnum;
use Filament\Infolists\Components\ImageEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Rimba\Who\Components\FaceAuth;
use UnitEnum;

class VerifyFace extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|UnitEnum|null $navigationGroup = 'Face';

    protected static string|BackedEnum|null $navigationIcon = 'bites-s-phone-call';

    protected static ?string $navigationLabel = 'Face Check';

    protected static ?int $navigationSort = 63;

    protected static ?string $title = 'Face Check';

    protected string $view = 'bites::pages.profile';

    public string $staffNo;

    public function mount(): void
    {
        $this->staffNo = Auth::user()->staff?->staff_no;
    }

    public function form(Schema $schema): Schema
    {
        // $staffNo = Auth::user()->staff->staff_no;
        return $schema
            ->schema([
                Section::make('Face Verification')
                    ->description('This page is sensitive and requires additional verification. Face vericiation is required.')
                    ->schema([
                        ImageEntry::make('header_image')
                            ->defaultImageUrl(url('/pic/'.$this->staffNo))
                            ->imageHeight(50)
                            ->circular(),
                        FaceAuth::make('face')
                            ->staffNo($this->staffNo)
                            ->live(),
                    ]),
            ]);
    }

    #[On('face-matched')]
    public function faceMatched(): void
    {
        Auth::user()?->userAuth?->markFaceVerified();

        $this->redirect(
            session()->pull(
                'face_auth.intended_url',
                filament()->getUrl()
            ),
            navigate: true,
        );
    }
}
