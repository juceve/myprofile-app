<x-layouts.app title="Mi perfil" eyebrow="Cuenta">
    @php($user = auth()->user())
    <div class="mx-auto max-w-5xl space-y-6">
        <div>
            <h2 class="text-2xl font-bold tracking-tight dark:text-white">Mi perfil y preferencias</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Administra tus datos personales, seguridad y apariencia del panel.</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <x-card class="h-fit p-6 text-center">
                <span class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-emerald-100 text-2xl font-bold text-emerald-700">
                    {{ str($user->name)->explode(' ')->map(fn ($name) => str($name)->substr(0, 1))->take(2)->implode('') }}
                </span>
                <h3 class="mt-4 text-lg font-bold dark:text-white">{{ $user->name }}</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $user->nickname }}</p>
                <x-badge class="mt-4">Activo</x-badge>
            </x-card>

            <div class="space-y-6 lg:col-span-2">
                <x-card class="p-6 sm:p-8">
                    <livewire:profile.update-profile-information-form />
                </x-card>
                <x-card class="p-6 sm:p-8">
                    <livewire:profile.update-password-form />
                </x-card>
                <x-card class="border-red-200 p-6 sm:p-8 dark:border-red-900/60">
                    <livewire:profile.delete-user-form />
                </x-card>
            </div>
        </div>

        <x-card>
            <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-800">
                <h3 class="font-bold dark:text-white">Modo de visualización</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">La selección se conserva en este equipo.</p>
            </div>
            <div class="grid gap-4 p-6 sm:grid-cols-3">
                <button data-theme-choice="light" class="theme-choice rounded-xl border border-slate-200 p-4 text-left hover:border-emerald-400 dark:border-slate-700"><span class="text-xl text-amber-500">☼</span><strong class="mt-2 block text-sm dark:text-white">Modo claro</strong><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Para entornos iluminados</span></button>
                <button data-theme-choice="dark" class="theme-choice rounded-xl border border-slate-200 p-4 text-left hover:border-emerald-400 dark:border-slate-700"><span class="text-xl text-emerald-500">☾</span><strong class="mt-2 block text-sm dark:text-white">Modo oscuro</strong><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Reduce la fatiga visual</span></button>
                <button data-theme-choice="system" class="theme-choice rounded-xl border border-slate-200 p-4 text-left hover:border-emerald-400 dark:border-slate-700"><span class="text-xl text-sky-500">◐</span><strong class="mt-2 block text-sm dark:text-white">Automático</strong><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Usa el tema del sistema</span></button>
            </div>
        </x-card>
    </div>
</x-layouts.app>
