@props([
    'action',
    'method' => 'POST',
    'title' => '¿Deseas continuar?',
    'text' => null,
    'confirmText' => 'Confirmar',
    'cancelText' => 'Cancelar',
])

<form method="{{ $method === 'GET' ? 'GET' : 'POST' }}" action="{{ $action }}" data-confirm-form data-confirm-title="{{ $title }}" data-confirm-text="{{ $text }}" data-confirm-button="{{ $confirmText }}" data-cancel-button="{{ $cancelText }}" {{ $attributes }}>
    @csrf
    @if(! in_array(strtoupper($method), ['GET', 'POST'], true))
        @method($method)
    @endif
    {{ $slot }}
</form>
