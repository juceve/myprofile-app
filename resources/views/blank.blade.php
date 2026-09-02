<x-layouts.app :title="$title ?? 'Vista en blanco'" :eyebrow="$eyebrow ?? 'Espacio de trabajo'">
    <div class="mx-auto max-w-5xl space-y-6">
        <div><h2 class="text-2xl font-bold tracking-tight">{{ $title ?? 'Vista en blanco' }}</h2><p class="mt-2 text-sm text-slate-500">{{ $description ?? 'Usa esta vista como punto de partida para una nueva sección del sistema.' }}</p></div>
        <x-card class="min-h-[420px] p-6 sm:p-8"><div class="flex h-full min-h-[350px] flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 text-center"><span class="text-4xl text-emerald-500">✦</span><h3 class="mt-4 text-lg font-bold">Área de trabajo</h3><p class="mt-2 max-w-md text-sm text-slate-500">Agrega aquí tu contenido, tablas o formularios usando los componentes Blade reutilizables.</p><div class="mt-5 flex gap-3"><x-button href="{{ route('dashboard') }}" variant="secondary">Volver al panel</x-button><x-button href="{{ route('forms') }}">Crear formulario</x-button></div></div></x-card>
    </div>
</x-layouts.app>
