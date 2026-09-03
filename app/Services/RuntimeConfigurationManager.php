<?php

namespace App\Services;

use GeoSot\EnvEditor\Facades\EnvEditor;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class RuntimeConfigurationManager
{
    private const BOOLEAN_KEYS = [
        'ALLOW_CUSTOM_BACKGROUNDS',
        'ALLOW_REGISTRATION',
        'ALLOW_USER_EXPORT',
        'ALLOW_USER_HTML',
        'ALLOW_USER_IMPORT',
        'CUSTOM_META_TAGS',
        'DISPLAY_CREDIT',
        'DISPLAY_CREDIT_FOOTER',
        'DISPLAY_FOOTER',
        'DISPLAY_FOOTER_CONTACT',
        'DISPLAY_FOOTER_HOME',
        'DISPLAY_FOOTER_PRIVACY',
        'DISPLAY_FOOTER_TERMS',
        'ENABLE_ADMIN_BAR',
        'ENABLE_ADMIN_BAR_USERS',
        'ENABLE_BUTTON_EDITOR',
        'ENABLE_REPORT_ICON',
        'ENABLE_SOCIAL_LOGIN',
        'HIDE_VERIFICATION_CHECKMARK',
        'MANUAL_USER_VERIFICATION',
        'NOTIFY_EVENTS',
        'USE_THEME_PREVIEW_IFRAME',
    ];

    private const TEXT_KEYS = [
        'ADMIN_EMAIL',
        'APP_NAME',
        'HOME_FOOTER_LINK',
        'TITLE_FOOTER_CONTACT',
        'TITLE_FOOTER_HOME',
        'TITLE_FOOTER_PRIVACY',
        'TITLE_FOOTER_TERMS',
    ];

    public function setBoolean(string $key, bool $value): void
    {
        $this->assertAllowed($key, self::BOOLEAN_KEYS);
        $this->write($key, $value ? 'true' : 'false');
    }

    public function setText(string $key, string $value): void
    {
        $this->assertAllowed($key, self::TEXT_KEYS);
        $this->write($key, $this->quote($value));
    }

    public function setRegistrationMiddleware(string $value): void
    {
        if (!in_array($value, ['auth', 'verified'], true)) {
            throw ValidationException::withMessages(['value' => 'Invalid registration authentication mode.']);
        }

        $this->write('REGISTER_AUTH', $value);
    }

    public function setHomeUrl(string $value): void
    {
        if ($value === 'default' || $value === '') {
            $this->write('HOME_URL', '');

            return;
        }

        if (!preg_match('/^[\p{L}\p{N}_-]+$/u', $value)) {
            throw ValidationException::withMessages(['value' => 'Invalid home page handle.']);
        }

        $this->write('HOME_URL', $this->quote($value));
    }

    public function setLocale(string $locale): void
    {
        if (!in_array($locale, config('app.supported_locales'), true)) {
            throw ValidationException::withMessages(['value' => 'Unsupported locale.']);
        }

        $this->write('LOCALE', $this->quote($locale));
    }

    public function setMaintenanceMode(bool $enabled): void
    {
        if (File::exists(storage_path('MAINTENANCE'))) {
            File::delete(storage_path('MAINTENANCE'));
        }

        $this->write('MAINTENANCE_MODE', $enabled ? 'true' : 'false');
    }

    public function rebuildCache(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        Artisan::call('route:clear', ['--no-interaction' => true]);
        Artisan::call('config:clear', ['--no-interaction' => true]);
        Artisan::call('config:cache', ['--no-interaction' => true]);
    }

    private function write(string $key, string $value): void
    {
        if (EnvEditor::keyExists($key)) {
            EnvEditor::editKey($key, $value);
        } else {
            EnvEditor::addKey($key, $value);
        }
    }

    private function assertAllowed(string $key, array $allowed): void
    {
        if (!in_array($key, $allowed, true)) {
            throw ValidationException::withMessages(['entry' => 'This setting is controlled by the deployment.']);
        }
    }

    private function quote(string $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
