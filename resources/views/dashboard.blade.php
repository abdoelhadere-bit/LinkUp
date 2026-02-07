<x-app-layout>
    <x-slot name="header">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">LinkUp</h2>
            <p class="text-sm text-gray-500">Fil d’actualité</p>
        </div>

</x-slot>



    <div class="bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-12 gap-6">

                </aside>

               {{-- FEED --}}
                <main class="col-span-12 lg:col-span-8 space-y-6">
                    <!-- create post -->
                    {{-- Composer --}}
                    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 p-5">
                        <div class="flex items-start gap-3">
                            <div class="h-11 w-11 rounded-full bg-slate-200"></div>

                            <div class="flex-1">

                                <livewire:create-post />

                            </div>
                        </div>
                    </div>
                    <!-- fil d'actualite -->
                    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 p-5">
                        <h3 class="font-semibold text-slate-900 mb-2">Fil d’actualité</h3>

                        <livewire:feed />

                    </div>

                </main>

            </div>
        </div>
    </div>
</x-app-layout>
