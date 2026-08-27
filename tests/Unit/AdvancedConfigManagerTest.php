<?php

namespace Tests\Unit;

use App\Services\AdvancedConfigManager;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

class AdvancedConfigManagerTest extends TestCase
{
    private string $temporaryDirectory;

    private Filesystem $files;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = new Filesystem();
        $this->temporaryDirectory = sys_get_temp_dir().'/mynha-advanced-config-'.bin2hex(random_bytes(8));
        $this->files->makeDirectory($this->temporaryDirectory.'/storage/templates', 0775, true);
        $this->files->makeDirectory($this->temporaryDirectory.'/config', 0775, true);
        $this->files->makeDirectory($this->temporaryDirectory.'/storage/app', 0775, true);
    }

    protected function tearDown(): void
    {
        $this->files->deleteDirectory($this->temporaryDirectory);

        parent::tearDown();
    }

    public function test_it_creates_the_runtime_config_and_installation_marker_without_overwriting_existing_config(): void
    {
        $templatePath = $this->temporaryDirectory.'/storage/templates/advanced-config.php';
        $configPath = $this->temporaryDirectory.'/config/advanced-config.php';
        $markerPath = $this->temporaryDirectory.'/storage/app/ISINSTALLED';

        file_put_contents($templatePath, "<?php\nreturn ['custom_url_prefix' => ''];\n");

        $manager = new AdvancedConfigManager($templatePath, $configPath, $markerPath);
        $manager->finalizeInstallation();

        $this->assertFileExists($configPath);
        $this->assertFileExists($markerPath);
        $this->assertSame(file_get_contents($templatePath), file_get_contents($configPath));

        file_put_contents($configPath, 'customized');
        $manager->ensureExists();

        $this->assertSame('customized', file_get_contents($configPath));
    }
}