<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Auth;

use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Rimba\Who\Components\FaceAuth;
use Rimba\Who\Components\Webcam;

class LoginWizard extends BaseLogin
{
    public ?array $data = [];

    protected function getFormActions(): array
    {
        return [];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Wizard\Step::make('Password')
                        ->icon('bites-password')
                        ->afterValidation(function (): void {
                            $this->validatePasswordFactor();
                        })
                        ->schema([
                            $this->getEmailFormComponent(),
                            $this->getPasswordFormComponent(),
                            $this->getRememberFormComponent(),
                            // Webcam::make('captured_image'),
                        ]),

                    Wizard\Step::make('Face ID')
                        ->icon('bites-face-scan')
                        ->schema([
                            TextInput::make('staff_no')
                                ->hidden(),
                            ImageEntry::make('header_image')
                                ->hiddenLabel()
                                ->defaultImageUrl(url('/pic/153582'))
                                ->imageHeight(40)
                                ->circular(),
                            FaceAuth::make('face')
                                ->staffNo(fn ($livewire) => $livewire->data['staff_no'] ?? null)
                                ->live(),
                        ]),
                ])
                    ->skippable(false),
                // ->submitAction(new HtmlString('
                //     <button type="submit" class="fi-btn fi-btn-size-md fi-color-primary">
                //         Sign in
                //     </button>
                // ')),
            ])
            ->statePath('data');
    }

    protected function validatePasswordFactor(): void
    {
        $data = $this->data;

        $user = User::query()
            ->where('email', $data['email'] ?? null)
            ->first();

        if (! $user || ! Hash::check($data['password'] ?? '', $user->password)) {
            throw ValidationException::withMessages([
                'data.email' => 'Invalid credentials.',
            ]);
        }

        Auth::login(
            $user,
            (bool) ($data['remember'] ?? false),
        );
        /*
         * If user has no staff record or no staff number:
         * - login immediately
         * - redirect to user panel
         * - stop wizard from proceeding to Step 2
         */
        if (! $user->staff || blank($user->staff->staff_no)) {
            $this->redirectToUserPanel();
            throw new Halt;
        }

        /*
         * If user has staff + staff_no:
         * - do not login yet
         * - store pending auth session
         * - populate staff_no into Step 2
         * - wizard proceeds to Face ID step
         */
        session([
            'auth.pending_user_id' => $user->id,
            'auth.pending_remember' => (bool) ($data['remember'] ?? false),
        ]);

        $this->data['staff_no'] = $user->staff->staff_no;

        $this->sendOtpToUser($user);
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->data;

        $pendingUserId = session('auth.pending_user_id');

        if (! $pendingUserId) {
            throw ValidationException::withMessages([
                'data.otp' => 'Your login session has expired. Please start again.',
            ]);
        }

        $user = User::query()->find($pendingUserId);

        if (! $user) {
            throw ValidationException::withMessages([
                'data.otp' => 'Invalid login session.',
            ]);
        }

        if (! $this->validateOtpForUser($user, $data['otp'] ?? null)) {
            throw ValidationException::withMessages([
                'data.otp' => 'Invalid authentication code.',
            ]);
        }

        Auth::login(
            $user,
            (bool) session('auth.pending_remember', false),
        );

        session()->forget([
            'auth.pending_user_id',
            'auth.pending_remember',
        ]);

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function redirectToUserPanel(): void
    {
        session()->regenerate();
        $this->redirect(
            filament()->getPanel('lobby')->getUrl()
        );
    }

    protected function redirectToStaffPanel(): void
    {
        session()->regenerate();
        $this->redirect(
            filament()->getPanel('staff')->getUrl()
        );
    }

    protected function sendOtpToUser(User $user): void
    {
        /*
         * Replace this later with your real OTP / Face ID preparation.
         *
         * Example:
         *
         * $code = random_int(100000, 999999);
         *
         * cache()->put(
         *     "login_otp:{$user->id}",
         *     Hash::make((string) $code),
         *     now()->addMinutes(5),
         * );
         *
         * Mail::to($user)->send(new LoginOtpMail($code));
         */
    }

    protected function validateOtpForUser(User $user, ?string $otp): bool
    {
        /*
         * Demo only.
         * Replace this with your real OTP / Face ID validation.
         */

        return $otp === '123456';
    }

    public function faceMatched(): void
    {
        $this->redirectToStaffPanel();
    }
}
