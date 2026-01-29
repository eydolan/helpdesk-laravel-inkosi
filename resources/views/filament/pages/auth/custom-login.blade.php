<x-filament-panels::page.simple>
    <x-slot name="subheading">
        <div class="flex flex-col items-center gap-2">
            <a href="{{ route('home') }}" class="fi-link group/link relative inline-flex items-center justify-center outline-none fi-size-md gap-1.5 fi-color-custom fi-color-primary">
                <span class="font-semibold text-sm group-hover/link:underline group-focus-visible/link:underline">
                    ← {{ __('Back to Home') }}
                </span>
            </a>
            @if (filament()->hasRegistration())
                <div class="mt-2">
                    {{ __('filament-panels::pages/auth/login.actions.register.before') }}
                    {{ $this->registerAction }}
                </div>
            @endif
        </div>
    </x-slot>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>
