<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use GeoSot\EnvEditor\Facades\EnvEditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class InstallerController extends Controller
{

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

        $user = User::find(1);
        $llName = $user->littlelink_name;

        if($request->register == 'Yes'){ 
            if(EnvEditor::keyExists('ALLOW_REGISTRATION')){EnvEditor::editKey('ALLOW_REGISTRATION', 'true');}else{EnvEditor::addKey('ALLOW_REGISTRATION', 'true');}
        } else {
            if(EnvEditor::keyExists('ALLOW_REGISTRATION')){EnvEditor::editKey('ALLOW_REGISTRATION', 'false');}else{EnvEditor::addKey('ALLOW_REGISTRATION', 'false');}
        }

        if($request->verify == 'Yes'){$value = "verified";}else{$value = "auth";}
        if(EnvEditor::keyExists('REGISTER_AUTH')){EnvEditor::editKey('REGISTER_AUTH', $value);}

        if($request->page == 'No'){$value = "";}else{$value = '"' . $llName . '"';}
        if(EnvEditor::keyExists('HOME_URL')){EnvEditor::editKey('HOME_URL', $value);}

        if(EnvEditor::keyExists('APP_NAME')){EnvEditor::editKey('APP_NAME', '"' . $request->app . '"');}

        if(file_exists(base_path("INSTALLING"))){unlink(base_path("INSTALLING"));}

        $file = base_path('INSTALLERLOCK');
        if (file_exists($file)) {
            unlink($file) or die('Cannot delete file: '.$file);
            sleep(1);
        }

        return redirect(url('dashboard'));
    }

    public function editConfigInstaller(Request $request)
    {

        $type = $request->type;
        $entry = $request->entry;
        $value = $request->value;
        $value = '"' . $request->value . '"';
        
        if(EnvEditor::keyExists($entry)){EnvEditor::editKey($entry, $value);}

        return redirect(url('dashboard'));
    }

}
