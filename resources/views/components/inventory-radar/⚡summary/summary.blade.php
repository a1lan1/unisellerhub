<x-filament::grid
    default="1"
    md="2"
    xl="4"
    class="gap-6"
>
    @foreach ($this->stats as $title => $value)
        <x-filament::grid.column>
            <x-filament::section>
                <div class="text-sm text-gray-500">
                    {{ ucfirst($title) }}
                </div>

                <div class="mt-2 text-3xl font-bold">
                    {{ number_format($value) }}
                </div>
            </x-filament::section>
        </x-filament::grid.column>
    @endforeach
</x-filament::grid>
