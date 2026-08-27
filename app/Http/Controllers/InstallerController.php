<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AdvancedConfigManager;
use Exception;
use GeoSot\EnvEditor\Facades\EnvEditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class InstallerController extends Controller
{
    public function __construct(private readonly AdvancedConfigManager $advancedConfigManager)
    {
    }

    public function showInstaller()
    {
        return view('installer/installer');
    }

    public function createAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'handle' => ['required', 'string', 'max:50', 'unique:users,littlelink_name', 'regex:/^[\p{L}\p{N}_-]+$/u'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        $file = base_path('INSTALLERLOCK');
        if (!file_exists($file)) {
            $handleFile = fopen($file, 'w') or die('Cannot create file:  '.$file);
            fclose($handleFile);
        }

        try {
            if (EnvEditor::keyExists('ADMIN_EMAIL')) {
                EnvEditor::editKey('ADMIN_EMAIL', $validated['email']);
            } else {
                EnvEditor::addKey('ADMIN_EMAIL', $validated['email']);
            }
        } catch (Exception $exception) {
            report($exception);
        }

        if (User::query()->doesntExist()) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'email_verified_at' => now(),
                'password' => Hash::make($validated['password']),
                'littlelink_name' => $validated['handle'],
            ]);

            $user->forceFill([
                'littlelink_description' => 'admin page',
                'role' => 'admin',
                'block' => 'no',
            ])->save();
        }

        return redirect(url('?5'));
    }

    public function prepareDatabase()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);

            if (!DB::table('pages')->exists()) {
                Artisan::call('db:seed', ['--class' => 'PageSeeder', '--force' => true]);
            }

            if (!DB::table('buttons')->exists()) {
                Artisan::call('db:seed', ['--class' => 'ButtonSeeder', '--force' => true]);
            }

            if (!DB::table('pages')->exists() || !DB::table('buttons')->exists()) {
                throw new Exception('The PostgreSQL database is missing required seed data.');
            }
        } catch (\Throwable $exception) {
            report($exception);

            return redirect(url('?error'));
        }

        return redirect(url('?4'));
    }

    public function options(Request $request)
    {
        $validated = $request->validate([
            'register' => ['required', Rule::in(['Yes', 'No'])],
            'verify' => ['required', Rule::in(['Yes', 'No'])],
            'page' => ['required', Rule::in(['Yes', 'No'])],
            'app' => ['required', 'string', 'max:255'],
        ]);

        $user = User::findOrFail(1);
        $llName = $user->littlelink_name;

        $this->advancedConfigManager->finalizeInstallation();

        $this->setEnvironmentValue('ALLOW_REGISTRATION', $validated['register'] === 'Yes' ? 'true' : 'false');
        $this->setEnvironmentValue('REGISTER_AUTH', $validated['verify'] === 'Yes' ? 'verified' : 'auth');
        $this->setEnvironmentValue('HOME_URL', $validated['page'] === 'Yes' ? '"'.$llName.'"' : '');
        $this->setEnvironmentValue('APP_NAME', '"'.$validated['app'].'"');

        if (file_exists(base_path('INSTALLING'))) {
            unlink(base_path('INSTALLING'));
        }

        $file = base_path('INSTALLERLOCK');
        if (file_exists($file)) {
            unlink($file) or die('Cannot delete file: '.$file);
        }

        return redirect(url('dashboard'));
    }

    public function editConfigInstaller(Request $request)
    {
        $availableLocales = collect(glob(resource_path('lang/*'), GLOB_ONLYDIR))
            ->map(fn (string $path) => basename($path))
            ->values()
            ->all();

        $validated = $request->validate([
            'value' => ['required', 'string', Rule::in($availableLocales)],
        ]);

        $value = '"'.$validated['value'].'"';

        $this->setEnvironmentValue('LOCALE', $value);

        return redirect(url(''));
    }

    private function setEnvironmentValue(string $key, string $value): void
    {
        if (EnvEditor::keyExists($key)) {
            EnvEditor::editKey($key, $value);
        } else {
            EnvEditor::addKey($key, $value);
        }
    }

}
