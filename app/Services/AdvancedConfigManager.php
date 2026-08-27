<?php

namespace App\Services;

use RuntimeException;

class AdvancedConfigManager
{
    public function __construct(
        private readonly ?string $templatePath = null,
        private readonly ?string $configPath = null,
        private readonly ?string $installedMarkerPath = null,
    ) {
    }

    public function ensureExists(): string
    {
        $configPath = $this->configPath ?? config_path('advanced-config.php');

        if (is_file($configPath)) {
            return $configPath;
        }

        $templatePath = $this->templatePath ?? storage_path('templates/advanced-config.php');

        if (!is_file($templatePath)) {
            throw new RuntimeException("Advanced configuration template not found: {$templatePath}");
        }

        $this->ensureDirectoryExists(dirname($configPath));

        if (!copy($templatePath, $configPath)) {
            throw new RuntimeException("Unable to create advanced configuration file: {$configPath}");
        }

        return $configPath;
    }

    public function finalizeInstallation(): void
    {
        $this->ensureExists();

        $markerPath = $this->installedMarkerPath ?? storage_path('app/ISINSTALLED');
        $this->ensureDirectoryExists(dirname($markerPath));

        if (!is_file($markerPath) && file_put_contents($markerPath, '') === false) {
            throw new RuntimeException("Unable to create installation marker: {$markerPath}");
        }
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException("Unable to create directory: {$directory}");
        }
    }
}