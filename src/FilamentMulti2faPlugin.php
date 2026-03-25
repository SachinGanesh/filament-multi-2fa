<?php

namespace MixCode\FilamentMulti2fa;

use Filament\Contracts\Plugin;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use MixCode\FilamentMulti2fa\Middleware\CheckTrustedDevice;
use MixCode\FilamentMulti2fa\Pages\OTPVerify;
use MixCode\FilamentMulti2fa\Pages\TwoFactorySetup;

class FilamentMulti2faPlugin implements Plugin
{
    protected bool $isForcedToSetup = false;

    protected bool $requireTwoFactorOnEveryLogin = false;

    public function getId(): string
    {
        return 'filament-multi-2fa';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                OTPVerify::class,
                TwoFactorySetup::class,
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label(fn (): string => trans('filament-multi-2fa::filament-multi-2fa.2fa_setup'))
                    ->url(fn (): string => TwoFactorySetup::getUrl())
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger'),
            ])
            ->authMiddleware([
                CheckTrustedDevice::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        Event::listen(Logout::class, function ($event) {
            session()->forget('2fa_passed');
        });
    }

    public function forceSetup2fa(bool $state = true): static
    {
        $this->isForcedToSetup = $state;

        return $this;
    }

    public function getForceSetup2fa(): bool
    {
        return $this->isForcedToSetup;
    }

    public function requireTwoFactorOnEveryLogin(bool $state = true): static
    {
        $this->requireTwoFactorOnEveryLogin = $state;

        return $this;
    }

    public function getRequireTwoFactorOnEveryLogin(): bool
    {
        return $this->requireTwoFactorOnEveryLogin
            || config('filament-multi-2fa.require_2fa_on_every_login', false);
    }

    public function redirectAfterVerifyUrl(): string
    {
        return filament()->getCurrentPanel()->getUrl();
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(static::make()->getId());
    }
}
