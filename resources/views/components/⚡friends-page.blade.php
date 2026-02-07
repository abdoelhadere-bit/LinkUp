<?php

use Livewire\Component;
use App\Models\User;
use App\Models\FriendRequest;
use App\services\FriendService;

new class extends Component
{
    public string $q = '';

    public function searchUsers()
    {
        $q = trim($this->q);
        if($q == '') return collect();
     
            return User::query()
            ->where('id', '!=', Auth::id())
            ->where(function($query) use ($q){
                $query->where('username', 'ilike', "%{$q}%");
    
            })
            ->orderBy('username')
            ->limit(10)->get();
    }
    
    public function relationsStatus($userId):string
    {
        $me = Auth::id();
        return app(FriendService::class)->status($userId, auth()->id()) ;
 

    }

    public function sendRequest($userId)
    {
        $me = Auth::id();
        return app(FriendService::class)->send($userId, auth()->id()) ;
    }

    public function incomingRequests()
        {
            return FriendRequest::query()
                    ->where('receiver_id', auth()->id())
                    ->where('status', 'pending')
                    ->latest()->get();
        }

    public function outgoingRequests()
        {
            return FriendRequest::query()
                    ->where('sender_id', auth()->id())
                    ->where('status', 'pending')
                    ->latest()->get();
        }

    public function friendsList()
        {
            $me = auth()->id();

            $friendIds = FriendRequest::query()
                ->where('status', 'accepted')
                ->where(function ($q) use ($me) {
                    $q->where('receiver_id', $me)
                      ->orWhere('sender_id', $me);
                })
                ->get()
                ->map(function ($fr) use ($me) {
                    return $fr->sender_id === $me ? $fr->receiver_id : $fr->sender_id; })
                ->unique()
                ->values();

            return User::whereIn('id', $friendIds)->get();
        }


    public function acceptRequest($userId)
        {
            $me = auth()->id();
            return app(FriendService::class)->accept($userId, auth()->id()) ;
        }

    public function declineRequest($userId)
        {
            $me = auth()->id();
            return app(FriendService::class)->decline($userId, auth()->id()) ;
        }
    }
?>

<div class="p-6 space-y-2">
    <input
        type="text"
        wire:model.live.debounce.300ms="q"        class="border rounded px-3 py-2 w-full"
        placeholder="Rechercher un utilisateur..."
    >


    @if(trim($q) !== '')
    <div class="space-y-2">
        @foreach($this->searchUsers() as $user)
            <div class="flex items-center justify-between border rounded-xl p-3">
                <div class="flex items-center gap-3">
                    <!-- avatar -->
                    <img class="h-10 w-10 rounded-full object-cover"
                                 src="{{$user->avatar_path ? asset('storage/'.$user->avatar_path)
                                 : 'https://ui-avatars.com/api/?name='.urlencode($user->username).'&background=0D1B2A&color=fff'}}"
                                 alt="{{$user->username}}">

                    <div>
                        <div class="font-medium">{{ $user->username }}</div>
                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                    </div>
                </div>

                <!-- action -->
                @php $status = $this->relationsStatus($user->id); @endphp
                @if($status === 'none')
                    <button class="px-3 py-1.5 rounded-lg bg-gray-900 text-white text-sm" wire:click="sendRequest({{$user->id}})">
                        ajouter 
                    </button>
                @elseif($status === 'outgoing_pending')
                    <span class="px-3 py-1.5 rounded-lg bg-yellow-100 text-yellow-800 text-sm">
                        Demande envoyée
                    </span>
                @elseif($status === 'incoming_pending')
                    <span class="px-3 py-1.5 rounded-lg bg-blue-100 text-blue-800 text-sm">
                        Demande reçue
                    </span>
                @elseif($status === 'friends')
                    <span class="px-3 py-1.5 rounded-lg bg-green-100 text-green-800 text-sm">
                        Amis
                    </span>
                @endif
            </div>

        @endforeach
    </div>
    @endif

    <div class="mt-6">
    <h3 class="font-semibold mb-2">Demandes reçues</h3>

    <div class="space-y-2">
        @forelse($this->incomingRequests() as $req)
            @php $u = \App\Models\User::find($req->sender_id); @endphp

            <div class="flex items-center justify-between border rounded-xl p-3">
                <div class="flex items-center gap-3">
                    <img class="h-9 w-9 rounded-full object-cover"
                         src="{{ $u->avatar_path ? asset('storage/'.$u->avatar_path) : 'https://ui-avatars.com/api/?name='.urlencode($u->username) }}"
                         alt="{{ $u->username }}">

                    <div>
                        <div class="font-medium">{{ $u->username }}</div>
                        <div class="text-sm text-gray-500">{{ $u->email }}</div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button wire:click="acceptRequest({{ $req->id }})"
                            class="px-3 py-1.5 rounded-lg bg-green-600 text-white text-sm">
                        Accepter
                    </button>

                    <button wire:click="declineRequest({{ $req->id }})"
                            class="px-3 py-1.5 rounded-lg bg-gray-200 text-gray-900 text-sm">
                        Refuser
                    </button>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500">Aucune demande reçue.</p>
        @endforelse
    </div>
</div>

<div class="mt-6">
    <h3 class="font-semibold mb-2">Demandes envoyées</h3>

    <div class="space-y-2">
        @forelse($this->outgoingRequests() as $req)
            @php $u = \App\Models\User::find($req->receiver_id); @endphp

            <div class="flex items-center justify-between border rounded-xl p-3">
                <div class="flex items-center gap-3">
                    <img class="h-9 w-9 rounded-full object-cover"
                         src="{{ $u->avatar_path ? asset('storage/'.$u->avatar_path) : 'https://ui-avatars.com/api/?name='.urlencode($u->username) }}"
                         alt="{{ $u->username }}">

                    <div>
                        <div class="font-medium">{{ $u->username }}</div>
                        <div class="text-sm text-gray-500">{{ $u->email }}</div>
                    </div>
                </div>

                <span class="px-3 py-1.5 rounded-lg bg-yellow-100 text-yellow-800 text-sm">
                    En attente
                </span>
            </div>
        @empty
            <p class="text-sm text-gray-500">Aucune demande envoyée.</p>
        @endforelse
    </div>
</div>


<div class="mt-6">
    <h3 class="font-semibold mb-2">Mes amis</h3>

    <div class="space-y-2">
        @forelse($this->friendsList() as $friend)
            <div class="flex items-center gap-3 border rounded-xl p-3">
                <img class="h-9 w-9 rounded-full object-cover"
                     src="{{ $friend->avatar_path ? asset('storage/'.$friend->avatar_path) : 'https://ui-avatars.com/api/?name='.urlencode($friend->username) }}"
                     alt="{{ $friend->username }}">

                <div>
                    <div class="font-medium">{{ $friend->username }}</div>
                    <div class="text-sm text-gray-500">{{ $friend->email }}</div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500">Aucun ami pour le moment.</p>
        @endforelse
    </div>
</div>


</div>
