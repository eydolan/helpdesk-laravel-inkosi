@php
    use Filament\Support\Facades\FilamentView;
@endphp

<x-filament-panels::layout.simple>
    <x-filament-panels::header.simple
        :heading="__('Update Password')"
        :subheading="__('Enter your new password below.')"
        :logo="true"
    />

    @if (session('success'))
        <div class="fi-alert fi-color-success fi-size-md rounded-lg bg-success-50 p-4 text-sm text-success-600 ring-1 ring-inset ring-success-600/10 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/20 mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="fi-alert fi-color-danger fi-size-md rounded-lg bg-danger-50 p-4 text-sm text-danger-600 ring-1 ring-inset ring-danger-600/10 dark:bg-danger-400/10 dark:text-danger-400 dark:ring-danger-400/20 mb-6">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
        @csrf

        <div class="fi-fo-field-wrp" data-field-wrapper>
            <div class="grid gap-y-2">
                <label for="password" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                        {{ __('New Password') }} <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                    </span>
                </label>
            </div>
            <div class="grid auto-cols-fr gap-y-2">
                <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 @error('password') fi-invalid ring-danger-600 dark:ring-danger-500 @enderror [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500">
                    <div class="fi-input-wrp-input min-w-0 flex-1 ps-3">
                        <input type="password" 
                               class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 dark:text-white dark:placeholder:text-gray-500 sm:text-sm sm:leading-6 bg-white/0 ps-0 pe-3" 
                               id="password" 
                               name="password" 
                               placeholder="{{ __('Enter new password') }}"
                               minlength="8"
                               required>
                    </div>
                </div>
                @error('password')
                    <p class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400" data-validation-error>{{ $message }}</p>
                @enderror
                <p class="fi-fo-field-wrp-hint text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Password must be at least 8 characters long.') }}
                </p>
            </div>
        </div>

        <div class="fi-fo-field-wrp" data-field-wrapper>
            <div class="grid gap-y-2">
                <label for="password_confirmation" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                        {{ __('Confirm Password') }} <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                    </span>
                </label>
            </div>
            <div class="grid auto-cols-fr gap-y-2">
                <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500">
                    <div class="fi-input-wrp-input min-w-0 flex-1 ps-3">
                        <input type="password" 
                               class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 dark:text-white dark:placeholder:text-gray-500 sm:text-sm sm:leading-6 bg-white/0 ps-0 pe-3" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               placeholder="{{ __('Confirm new password') }}"
                               minlength="8"
                               required>
                    </div>
                </div>
            </div>
        </div>

        <x-filament::button type="submit" size="lg" class="w-full">
            {{ __('Update Password') }}
        </x-filament::button>

        <div class="flex flex-col items-center gap-2 text-center">
            <a href="{{ route('filament.admin.auth.login') }}" class="fi-link group/link relative inline-flex items-center justify-center outline-none fi-size-md gap-1.5 fi-color-custom fi-color-primary">
                <span class="font-semibold text-sm group-hover/link:underline group-focus-visible/link:underline">
                    {{ __('Back to Login') }}
                </span>
            </a>
            <a href="{{ route('home') }}" class="fi-link group/link relative inline-flex items-center justify-center outline-none fi-size-md gap-1.5 fi-color-custom fi-color-primary">
                <span class="font-semibold text-sm group-hover/link:underline group-focus-visible/link:underline">
                    ← {{ __('Back to Home') }}
                </span>
            </a>
        </div>
    </form>
</x-filament-panels::layout.simple>
