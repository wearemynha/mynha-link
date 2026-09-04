@extends('layouts.installing')

@php
$queryString = request()->server('QUERY_STRING', '');
@endphp

@Push('installer-body')
<div class="container">

@if($queryString === '')
{{-- Landing page --}}

@if(\Illuminate\Support\Facades\Schema::hasTable('users') && DB::table('users')->exists())
    @php
    if(file_exists(storage_path("app/INSTALLING"))){unlink(storage_path("app/INSTALLING"));}
    header("Refresh:0");
    @endphp
@else
        <div class="logo-container fadein">
           <img class="logo-img" src="{{ asset('assets/mynha-assets/mynha-logo-installer.svg') }}" alt="Mynha">
        </div>
        <h1>{{__('messages.Setup LinkStack')}}</h1>
        <div class="left-txt glass-container mynha-panel installer-welcome">
         {{__('messages.Welcome to the setup for LinkStack!')}}<br><br>
        <b>{{__('messages.This setup will:')}}</b><br>
        {{__('messages.Check the server dependencies')}}<br>
        {{__('messages.Setup the database')}}<br>
        {{__('messages.Create the admin user')}}<br>
        {{__('messages.Configure the app')}}<br>
        </div>
        
{{-- start language --}}
<?php $configValue2 = config('app.locale'); ?>
<form id="language-form" action="{{route('editConfigInstaller')}}" enctype="multipart/form-data" method="post">
    <div class="form-group col-lg-8">
        <input value="homeurl" name="type" type="hidden">
        <input value="LOCALE" name="entry" type="hidden">
        <label for="installer-language" class="text-muted">{{__('messages.Choose a language')}}</label>
        <div class="input-group">
            <x-mynha.select id="installer-language" class="mynha-field-surface" name="value">
                @if($configValue2 != '')
                    <option>{{$configValue2}}</option>
                @endif
                <?php
                try {
                    $langFolders = array_filter(glob(base_path('resources/lang') . '/*'), 'is_dir');
                } catch (\Exception $e) {
                    $langFolders = [];
                }

                foreach($langFolders as $folder) {
                    $folderName = basename($folder);
                    if ($folderName != $configValue2) {
                        echo '<option>' . $folderName . '</option>';
                    }
                }
                ?>
            </x-mynha.select>
        </div>
    </div>
    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <script type="text/javascript">
        document.getElementById("language-form").addEventListener("change", function() {
            this.submit();
        });
    </script>
</form>
{{-- end language --}}

<p class="installer-disclaimer">{{__('messages.setup.disclaimer')}} <a href="https://linkstack.org/terms-and-conditions/" target="_blank">{{__('messages.Terms and Conditions')}}</a>.</p>

        <x-mynha.button :href="url('?2')">{{__('messages.Next')}}</x-mynha.button>
@endif
      
@endif

@if($queryString === 'error')
{{-- Landing page --}}
        
        <div class="logo-container fadein">
           <img class="logo-img" src="{{ asset('assets/mynha-assets/mynha-logo-installer.svg') }}" alt="Mynha">
        </div>
        <h1>{{__('messages.Setup failed')}}</h1>
        <p class="inst-txt">{{__('messages.An error has occured. Please try again')}}</p>
        <div class="row">
        <x-mynha.button :href="url('?3')">{{__('messages.Try again')}}</x-mynha.button>
        </div>
      
@endif

@if($queryString === '2')
{{-- Landing page --}}
        
        <div class="logo-container fadein">
           <img class="logo-img" src="{{ asset('assets/mynha-assets/mynha-logo-installer.svg') }}" alt="Mynha">
        </div>
        <h1>{{__('messages.Dependency check')}}</h1>
        <p class="inst-txt">{{__('messages.Required PHP modules:')}}</p>
        <div class="left-txt glass-container mynha-panel">
        <table>
        <tr><td>BCMath: </td><td>@if(extension_loaded('bcmath'))<i class="bi bi-check-lg"></i>@else<i class="bi bi-x-lg"></i>@endif</td></tr>
        <tr><td>Ctype: </td><td>@if(extension_loaded('Ctype'))<i class="bi bi-check-lg"></i>@else<i class="bi bi-x-lg"></i>@endif</td></tr>
        <tr><td>cURL: </td><td>@if(extension_loaded('cURL'))<i class="bi bi-check-lg"></i>@else<i class="bi bi-x-lg"></i>@endif</td></tr>
        <tr><td>DOM: </td><td>@if(extension_loaded('DOM'))<i class="bi bi-check-lg"></i>@else<i class="bi bi-x-lg"></i>@endif</td></tr>
        <tr><td>Fileinfo: </td><td>@if(extension_loaded('Fileinfo'))<i class="bi bi-check-lg"></i>@else<i class="bi bi-x-lg"></i>@endif</td></tr>
        <tr><td>JSON: </td><td>@if(extension_loaded('JSON'))<i class="bi bi-check-lg"></i>@else<i class="bi bi-x-lg"></i>@endif</td></tr>
        <tr><td>iconv: </td><td>@if(extension_loaded('iconv'))<i class="bi bi-check-lg"></i>@else<i class="bi bi-x-lg"></i>@endif</td></tr>
        <tr><td>Mbstring: </td><td>@if(extension_loaded('Mbstring'))<i class="bi bi-check-lg"></i>@else<i class="bi bi-x-lg"></i>@endif</td></tr>
        <tr><td>OpenSSL: </td><td>@if(extension_loaded('OpenSSL'))<i class="bi bi-check-lg"></i>@else<i class="bi bi-x-lg"></i>@endif</td></tr>
        <tr><td>PCRE: </td><td>@if(extension_loaded('PCRE'))<i class="bi bi-check-lg"></i>@else<i class="bi bi-x-lg"></i>@endif</td></tr>
        <tr><td>PDO: </td><td>@if(extension_loaded('PDO'))<i class="bi bi-check-lg"></i>@else<i class="bi bi-x-lg"></i>@endif</td></tr>
        <tr><td>Tokenizer: </td><td>@if(extension_loaded('Tokenizer'))<i class="bi bi-check-lg"></i>@else<i class="bi bi-x-lg"></i>@endif</td></tr>
        <tr><td>XML: </td><td>@if(extension_loaded('XML'))<i class="bi bi-check-lg"></i>@else<i class="bi bi-x-lg"></i>@endif</td></tr>
        </table>
        <br>
        <b class="installer-dependency-note">{{__('messages.Depending on your database type:')}}</b>
        <table>
        <tr><td>PostgreSQL: </td><td>@if(extension_loaded('pdo_pgsql'))<i class="bi bi-check-lg"></i>@else<i class="bi bi-x-lg"></i>@endif</td></tr>
        </table>
        </div><br>
        <div class="row">
        <x-mynha.button href="?3">{{__('messages.Next')}}</x-mynha.button>
        </div>
      
@endif

@if($queryString === '3')
{{-- Database preparation --}}

        <div class="logo-container fadein">
           <img class="logo-img" src="{{ asset('assets/mynha-assets/mynha-logo-installer.svg') }}" alt="Mynha">
        </div>
        <h1>{{__('messages.Setup LinkStack')}}</h1>
        <p class="inst-txt">{{__('messages.Setup the database')}}</p>
        <p>{{__('messages.PostgreSQL configuration is provided through environment variables.')}}</p>
        <form id="prepare-database-form" action="{{route('prepareDatabase')}}" method="post">
            <input type="hidden" name="_token" value="{{csrf_token()}}">
            <x-mynha.button type="submit">{{__('messages.Next')}}</x-mynha.button>
        </form>

@endif

@if($queryString === '4')
{{-- Landing page --}}
        
        <div class="logo-container fadein">
           <img class="logo-img" src="{{ asset('assets/mynha-assets/mynha-logo-installer.svg') }}" alt="Mynha">
        </div>
        <h1>{{__('messages.Setup LinkStack')}}</h1>
        <p class="inst-txt">{{__('messages.Create an admin account')}}</p>

<form id="home-url-form" action="{{route('createAdmin')}}" enctype="multipart/form-data" method="post">
<div class="form-group col-lg-8">
<x-mynha.field for="admin-email" :label="__('messages.Admin email:')">
    <x-mynha.input id="admin-email" placeholder="admin@example.com" name="email" type="email" autocomplete="email" required />
</x-mynha.field>
<x-mynha.field for="admin-password" :label="__('messages.Admin password:')">
    <x-password-field id="admin-password" name="password" minlength="12" autocomplete="new-password" required />
</x-mynha.field>
<x-mynha.field for="admin-password-confirmation" :label="__('messages.Confirm Password').':'">
    <x-password-field id="admin-password-confirmation" name="password_confirmation" minlength="12" autocomplete="new-password" required />
</x-mynha.field>
<x-mynha.field for="admin-handle" :label="__('messages.Handle:')">
    <div class="mynha-input-group">
        <span class="mynha-input-group__prefix" aria-hidden="true">@</span>
        <x-mynha.input id="admin-handle" name="handle" type="text" required />
    </div>
</x-mynha.field>
<x-mynha.field for="admin-name" :label="__('messages.Name:')">
    <x-mynha.input id="admin-name" name="name" type="text" required />
</x-mynha.field>
</div>
<input type="hidden" name="_token" value="{{csrf_token()}}">
<x-mynha.button type="submit">{{__('messages.Next')}}</x-mynha.button>
</form>
      
@endif

@if($queryString === '5')
{{-- Landing page --}}
        
        <div class="logo-container fadein">
           <img class="logo-img" src="{{ asset('assets/mynha-assets/mynha-logo-installer.svg') }}" alt="Mynha">
        </div>
        <h1>{{__('messages.Setup LinkStack')}}</h1>
        <p class="inst-txt">{{__('messages.Configure your page')}}</p>
<form id="home-url-form" action="{{route('options')}}" enctype="multipart/form-data" method="post">
<div class="form-group col-lg-8">
<x-mynha.field for="registration-option" :label="__('messages.Enable registration:')">
    <x-mynha.select id="registration-option" name="register">
        <option value="Yes">{{__('messages.Yes')}}</option>
        <option value="No">{{__('messages.No')}}</option>
    </x-mynha.select>
</x-mynha.field>

<x-mynha.field for="verification-option" :label="__('messages.Enable email verification:')">
    <x-mynha.select id="verification-option" name="verify">
        <option value="Yes">{{__('messages.Yes')}}</option>
        <option value="No">{{__('messages.No')}}</option>
    </x-mynha.select>
</x-mynha.field>

<x-mynha.field for="select" :label="__('messages.Set your page as Home Page')">
    <x-mynha.select id="select" name="page">
        <option value="No">{{__('messages.No')}}</option>
        <option value="Yes">{{__('messages.Yes')}}</option>
    </x-mynha.select>
</x-mynha.field>
<script src="{{ asset('assets/external-dependencies/jquery-3.4.1.min.js') }}"></script>
<script src="{{ asset('assets/external-dependencies/sweetalert2.min.js') }}"></script>
<script>
$("#select").change(function(){
    if($(this).val() == "Yes") {
        $('.container').hide();

        Swal.fire({
            title: "{{__('messages.Set your page as Home Page')}}",
            text: "{{__('messages.This will move the Home Page to /home')}}",
            icon: 'info',
            confirmButtonText: "{{__('messages.Confirm')}}",
        }).then((result) => {
            $('.container').show();
        });
    }
});
</script>

<x-mynha.field for="app-name" :label="__('messages.App Name:')">
    <x-mynha.input id="app-name" value="Mynha Link" name="app" type="text" required />
</x-mynha.field>
</div>
<input type="hidden" name="_token" value="{{csrf_token()}}">
<x-mynha.button type="submit">{{__('messages.Finish setup')}}</x-mynha.button>
</form>
      
@endif


</div>
@endpush
