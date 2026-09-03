<?php

namespace App\Console\Commands;

use GeoSot\EnvEditor\Facades\EnvEditor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

class SyncRuntimeEnvironment extends Command
{
    protected $signature = 'runtime:sync-environment
        {--cache : Rebuild the Laravel configuration cache after synchronization}
        {--clear-missing : Remove deployment-managed keys that are absent from the process environment}
        {--process-keys= : Comma-separated manifest of keys present before Laravel loads the .env file}';

    protected $description = 'Synchronize deployment-managed process variables into the persistent application environment';

    private const MANAGED_KEYS = [
        'APP_ENV', 'APP_DEBUG', 'APP_URL', 'APP_KEY',
        'LOG_CHANNEL', 'LOG_LEVEL', 'TZ',
        'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'DB_SSLMODE',
        'CACHE_DRIVER', 'QUEUE_CONNECTION', 'SESSION_DRIVER', 'SESSION_SECURE_COOKIE',
        'MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_ENCRYPTION',
        'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME',
        'FORCE_HTTPS', 'FORCE_ROUTE_HTTPS',
        'ALLOW_CUSTOM_CODE_IN_THEMES',
    ];

    private const BOOLEAN_KEYS = [
        'APP_DEBUG', 'SESSION_SECURE_COOKIE', 'FORCE_HTTPS', 'FORCE_ROUTE_HTTPS',
        'ALLOW_CUSTOM_CODE_IN_THEMES',
    ];

    private const PRESERVE_WHEN_MISSING_KEYS = [
        'APP_KEY',
    ];

    /** @var array<string, true>|null */
    private ?array $processKeys = null;

    public function handle(): int
    {
        $this->processKeys = $this->parseProcessKeys($this->option('process-keys'));

        $environment = $this->processValue('APP_ENV') ?? 'production';
        $debug = $this->processValue('APP_DEBUG');

        if ($environment === 'production' && $debug !== null && $this->toBoolean($debug)) {
            throw new RuntimeException('APP_DEBUG must be false when APP_ENV=production.');
        }

        foreach (self::MANAGED_KEYS as $key) {
            $value = $this->processValue($key);
            if ($value === null) {
                if (
                    $this->option('clear-missing')
                    && !in_array($key, self::PRESERVE_WHEN_MISSING_KEYS, true)
                    && EnvEditor::keyExists($key)
                ) {
                    EnvEditor::deleteKey($key);
                }

                continue;
            }

            if ($key === 'APP_KEY' && $value === '') {
                continue;
            }

            $encoded = in_array($key, self::BOOLEAN_KEYS, true)
                ? ($this->toBoolean($value) ? 'true' : 'false')
                : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

            if (EnvEditor::keyExists($key)) {
                EnvEditor::editKey($key, $encoded);
            } else {
                EnvEditor::addKey($key, $encoded);
            }
        }

        if ($this->option('cache')) {
            Artisan::call('config:clear', ['--no-interaction' => true]);
            Artisan::call('config:cache', ['--no-interaction' => true]);
        }

        $this->info('Deployment environment synchronized.');

        return self::SUCCESS;
    }

    private function processValue(string $key): ?string
    {
        if ($this->processKeys !== null && !isset($this->processKeys[$key])) {
            return null;
        }

        $value = getenv($key);

        return $value === false ? null : $value;
    }

    /**
     * @return array<string, true>|null
     */
    private function parseProcessKeys(mixed $manifest): ?array
    {
        if (!is_string($manifest)) {
            return null;
        }

        $keys = array_filter(array_map('trim', explode(',', $manifest)));

        return array_fill_keys($keys, true);
    }

    private function toBoolean(string $value): bool
    {
        $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($parsed === null) {
            throw new RuntimeException("{$value} is not a valid boolean value.");
        }

        return $parsed;
    }
}
