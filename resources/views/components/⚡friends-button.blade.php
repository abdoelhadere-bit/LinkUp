<?php

use Livewire\Component;
use App\services\FriendService;
use App\Models\FriendRequest;
new class extends Component
{
    public int $userId;

    public function status()
    {
        return app(FriendService::class)->status($this->userId, auth()->id()) ;
    }

    public function send()
    {
        return app(FriendService::class)->send($this->userId, auth()->id()) ;
    }

    public function accept()
    {
        return app(FriendService::class)->accept($this->userId, auth()->id()) ;
    }

    public function decline()
    {
        return app(FriendService::class)->decline($this->userId, auth()->id()) ;
    }
};
?>

<div>
    @php $s = $this->status(); @endphp

    @if($s === 'me')
        <span class="text-sm text-gray-400">C’est vous</span>

    @elseif($s === 'none')
        <button wire:click="send"
                class="px-4 py-2 rounded-xl bg-gray-900 text-white text-sm">
            Ajouter
        </button>

    @elseif($s === 'outgoing_pending')
        <span class="px-4 py-2 rounded-xl bg-yellow-100 text-yellow-800 text-sm">
            Demande envoyée
        </span>

    @elseif($s === 'incoming_pending')
        <div class="flex items-center gap-2">
            <button wire:click="accept"
                    class="px-3 py-2 rounded-xl bg-green-600 text-white text-sm">
                Accepter
            </button>
            <button wire:click="decline"
                    class="px-3 py-2 rounded-xl bg-red-600 text-white text-sm">
                Refuser
            </button>
        </div>

    @elseif($s === 'friends')
        <span class="px-4 py-2 rounded-xl bg-green-100 text-green-800 text-sm">
            Amis
        </span>
    @endif
</div>
