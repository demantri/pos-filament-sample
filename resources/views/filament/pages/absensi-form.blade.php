<x-filament::page>
    <form wire:submit.prevent="submit">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-4">
            Simpan Absensi
        </x-filament::button>
    </form>

    {{-- <script>
        navigator.geolocation.getCurrentPosition(function(position) {
            Livewire.emit('setFormData', {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            });
        });
    </script> --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        Livewire.emit('setFormData', {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        });
                    },
                    (error) => {
                        alert('Gagal mengambil lokasi: ' + error.message);
                    }
                );
            } else {
                alert('Geolocation tidak didukung browser ini.');
            }
        });
    </script>
</x-filament::page>