<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AdvancedConfigManager;
use App\Services\RuntimeConfigurationManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class InstallerController extends Controller
{
    public function __construct(
        private readonly AdvancedConfigManager $advancedConfigManager,
        private readonly RuntimeConfigurationManager $runtimeConfigurationManager,
    ) {
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

        $file = storage_path('app/INSTALLERLOCK');
        if (!file_exists($file)) {
            $handleFile = fopen($file, 'w') or die('Cannot create file:  '.$file);
            fclose($handleFile);
        }

        $this->runtimeConfigurationManager->setText('ADMIN_EMAIL', $validated['email']);

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

        $this->runtimeConfigurationManager->setBoolean('ALLOW_REGISTRATION', $validated['register'] === 'Yes');
        $this->runtimeConfigurationManager->setRegistrationMiddleware($validated['verify'] === 'Yes' ? 'verified' : 'auth');
        $this->runtimeConfigurationManager->setHomeUrl($validated['page'] === 'Yes' ? $llName : '');
        $this->runtimeConfigurationManager->setText('APP_NAME', $validated['app']);

        if (file_exists(storage_path('app/INSTALLING'))) {
            unlink(storage_path('app/INSTALLING'));
        }

        $file = storage_path('app/INSTALLERLOCK');
        if (file_exists($file)) {
            unlink($file) or die('Cannot delete file: '.$file);
        }

        $this->runtimeConfigurationManager->rebuildCache();

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

        $this->runtimeConfigurationManager->setLocale($validated['value']);
        $this->runtimeConfigurationManager->rebuildCache();

        return redirect(url(''));
    }

}
