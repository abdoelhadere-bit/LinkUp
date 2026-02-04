<x-app-layout>
    <x-slot name="header">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">LinkUp</h2>
            <p class="text-sm text-gray-500">Fil d’actualité</p>
        </div>

        {{-- SEARCH --}}
        <div class="relative w-full max-w-md">
            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    class="w-full border-gray-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-gray-900/10 focus:border-gray-300"
                    placeholder="Rechercher (username, email, nom)..."
                >
                <button class="px-4 py-2 bg-gray-900 text-white rounded-xl hover:bg-gray-800">
                    Search
                </button>
            </form>

            {{-- DROPDOWN RESULTS --}}
            @if($q)
                <div class="absolute left-0 right-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-lg overflow-hidden z-50">
                    <div class="px-4 py-3 flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-900">Résultats</span>
                        <span class="text-xs text-gray-500">{{ $users->count() }}</span>
                    </div>

                    <div class="max-h-80 overflow-auto">
                        @forelse($users as $u)
                            <div class="px-4 py-3 flex items-center justify-between hover:bg-gray-50">
                                <div class="min-w-0">
                                    <div class="font-medium text-gray-900 truncate">
                                        {{ $u->name }} (&#64;{{ $u->username }})
                                    </div>
                                    <div class="text-xs text-gray-500 truncate">{{ $u->email }}</div>
                                </div>

                                <form method="POST" action="{{ route('friend-requests.send') }}" class="ml-3 shrink-0">
                                    @csrf
                                    <input type="hidden" name="receiver_id" value="{{ $u->id }}">
                                    <button class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-500">
                                        Envoyer
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="px-4 py-4 text-sm text-gray-500">
                                Aucun résultat.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
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
