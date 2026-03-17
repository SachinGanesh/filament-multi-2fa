<?php

namespace MixCode\FilamentMulti2fa\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TwoFactorAuthType: string implements HasColor, HasIcon, HasLabel
{
    case Email = 'email';
    case Totp = 'totp'; // (Authenticator Apps)
    case None = 'none';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Returns only the cases that are enabled via the `enabled_2fa_types` config key.
     * Falls back to all cases when the config key is not set.
     * Accepts either enum instances or string values in the config array.
     */
    public static function enabledCases(): array
    {
        $configuredTypes = config('filament-multi-2fa.enabled_2fa_types');

        if ($configuredTypes === null) {
            return self::cases();
        }

        $enabledValues = array_map(
            fn ($type) => $type instanceof self ? $type->value : $type,
            $configuredTypes
        );

        return array_values(array_filter(
            self::cases(),
            fn ($case) => in_array($case->value, $enabledValues)
        ));
    }

    public function getLabel(): ?string
    {
        return trans('filament-multi-2fa::filament-multi-2fa.' . str($this->name)->snake()->lower()->toString());
    }

    public function getColor(): string | array | null
    {
        return match ($this->name) {
            self::Email->name => 'primary',
            self::Totp->name => 'success',
            self::None->name => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this->name) {
            self::Email->name => 'heroicon-o-envelope',
            self::Totp->name => 'heroicon-o-device-phone-mobile',
            self::None->name => 'heroicon-o-lock-open',
        };
    }

    public function isEmailType(): bool
    {
        return $this->value === self::Email->value;
    }

    public function isTotpType(): bool
    {
        return $this->value === self::Totp->value;
    }

    public function isNoneType(): bool
    {
        return $this->value === self::None->value;
    }
}
