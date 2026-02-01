<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Friends') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-3 bg-green-100 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-3 bg-red-100 text-red-800 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Recherche --}}
            <div class="p-6 bg-white shadow rounded-lg">
                <h3 class="font-semibold mb-3">Rechercher un utilisateur</h3>

                <form method="GET" action="{{ route('friends.index') }}" class="flex gap-2">
                    <input
                        type="text"
                        name="q"
                        value="{{ $q }}"
                        class="w-full border rounded px-3 py-2"
                        placeholder="username, email ou name..."
                    >
                    <button class="px-4 py-2 bg-gray-900 text-white rounded">Search</button>
                </form>

                {{-- Résultats --}}
                @if($q)
                    <div class="mt-4 space-y-3">
                        @forelse($users as $u)
                            <div class="flex items-center justify-between border rounded p-3">
                                <div>
                                    <div class="font-medium">{{ $u->name }} (&#64;{{ $u->username }})</div>
                                    <div class="text-sm text-gray-600">{{ $u->email }}</div>
                                </div>

                                <form method="POST" action="{{ route('friend-requests.send') }}">
                                    @csrf
                                    <input type="hidden" name="receiver_id" value="{{ $u->id }}">
                                    <x-primary-button>
                                        Envoyer demande
                                    </x-primary-button>
                                </form>
                            </div>
                        @empty
                            <p class="text-sm text-gray-600 mt-3">Aucun résultat.</p>
                        @endforelse
                    </div>
                @endif
            </div>

            {{-- Demandes reçues --}}
            <div class="p-6 bg-white shadow rounded-lg">
                <h3 class="font-semibold mb-3">Demandes reçues (pending)</h3>

                <div class="space-y-3">
                    @forelse($receivedPending as $req)
                        <div class="flex items-center justify-between border rounded p-3">
                            <div>
                                <div class="font-medium">
                                    {{ $req->sender->name }} (&#64;{{ $req->sender->username }})
                                </div>
                                <div class="text-sm text-gray-600">Status: {{ $req->status }}</div>
                            </div>

                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('friend-requests.accept', $req) }}">
                                    @csrf
                                    @method('PATCH')
                                    <x-primary-button>Accepter</x-primary-button>
                                </form>

                                <form method="POST" action="{{ route('friend-requests.decline', $req) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="px-3 py-2 bg-red-600 text-white rounded">Refuser</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600">Aucune demande reçue.</p>
                    @endforelse
                </div>
            </div>

            {{-- Demandes envoyées --}}
            <div class="p-6 bg-white shadow rounded-lg">
                <h3 class="font-semibold mb-3">Demandes envoyées (pending)</h3>

                <div class="space-y-3">
                    @forelse($sentPending as $req)
                        <div class="flex items-center justify-between border rounded p-3">
                            <div>
                                <div class="font-medium">
                                    Vers: {{ $req->receiver->name }} (&#64;{{ $req->receiver->username }})
                                </div>
                                <div class="text-sm text-gray-600">Status: {{ $req->status }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600">Aucune demande envoyée.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
