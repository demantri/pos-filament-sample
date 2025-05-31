@php
    use App\Models\Product;
    $store_id = auth()->user()->store_id;
    $produk = Product::where('store_id', $store_id)->get();
@endphp

<x-filament::page>
    <form wire:submit.prevent="submit" class="space-y-4 bg-white p-6 shadow rounded-md">

        <h2 class="text-lg font-bold">Input Stok Masuk / Keluar</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="product_id" class="block font-medium text-sm text-gray-700">Produk</label>
                <select wire:model="product_id" id="product_id" class="form-select w-full mt-1">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($produk as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
                @error('product_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="tipe" class="block font-medium text-sm text-gray-700">Tipe</label>
                <select wire:model="tipe" id="tipe" class="form-select w-full mt-1">
                    <option value="">-- Pilih Tipe --</option>
                    <option value="masuk">Stok Masuk</option>
                    <option value="keluar">Stok Keluar</option>
                </select>
                @error('tipe') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="jumlah" class="block font-medium text-sm text-gray-700">Jumlah</label>
                <input type="number" id="jumlah" wire:model="jumlah" class="form-input w-full mt-1" min="1">
                @error('jumlah') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="pt-4">
            <button type="submit" class="filament-button bg-primary-600 text-white px-4 py-2 rounded hover:bg-primary-700">
                Simpan
            </button>
        </div>
    </form>

    <div class="pt-6">
        {{ $this->table }}
    </div>
</x-filament::page>
