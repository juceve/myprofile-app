<x-layouts.app title="Formularios y Controles" eyebrow="Librería UI">
    <div class="mx-auto max-w-4xl space-y-6">
        <div><h2 class="text-2xl font-bold tracking-tight">Componentes de formulario</h2><p class="mt-2 text-sm text-slate-500">Campos consistentes, accesibles y listos para reutilizar en cualquier vista Blade.</p></div>
        <x-card>
            <form method="POST" action="{{ route('forms') }}" class="space-y-5 p-6 sm:p-8">
                @csrf
                <div class="grid gap-5 sm:grid-cols-2"><x-input name="subject" label="Asunto" placeholder="Nombre del trámite" /><x-select name="department" label="Departamento" :options="['finance' => 'Finanzas y Presupuesto', 'systems' => 'Tecnología y Sistemas', 'legal' => 'Dirección Jurídica']" /></div>
                <x-textarea name="description" label="Descripción" placeholder="Describe brevemente el trámite..." />
                <div class="flex justify-end gap-3"><x-button href="{{ route('dashboard') }}" variant="secondary">Cancelar</x-button><x-button type="submit">Guardar trámite</x-button></div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
