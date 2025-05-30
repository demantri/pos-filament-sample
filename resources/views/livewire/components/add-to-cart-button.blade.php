{{-- @props(['record']) --}}
@php
    $product = $getRecord();
@endphp

<div class="flex items-center gap-2">
    <button 
        wire:click="addToCart('{{ $product->id }}')" 
        class="inline-flex items-center justify-center w-8 h-8 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
        @if($product->qty <= 0) disabled @endif
        title="@if($product->qty <= 0) Stok Habis @else Tambah ke Cart @endif"
    >
        @if($product->qty <= 0)
            <x-heroicon-o-x-mark class="w-4 h-4" />
        @else
            <x-heroicon-o-plus class="w-4 h-4" />
        @endif
    </button>
    
    {{-- @if($product->qty <= 0)
        <span class="text-xs text-red-500 font-medium">Habis</span>
    @elseif($product->qty < 10)
        <span class="text-xs text-orange-500 font-medium">Sedikit</span>
    @endif --}}
</div>