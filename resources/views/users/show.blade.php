<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>
<div class="mt-6 space-y-4">
    <h2 class="text-sm font-semibold text-slate-900">
        Posts de {{ $user->username }}
    </h2>
    <livewire:friends-button :userId="$user->id"/>

    @forelse($posts as $post)
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 p-4">
            <div class="flex items-center gap-3 mb-2">
                <img class="h-10 w-10 rounded-full object-cover"
                     src="{{ $post->user->avatar_path ? asset('storage/'.$post->user->avatar_path) : 'https://ui-avatars.com/api/?name='.urlencode($post->user->username) }}"
                     alt="{{ $post->user->username }}">

                <div>
                    <div class="font-medium text-slate-900">{{ $post->user->username }}</div>
                    <div class="text-xs text-slate-500">{{ $post->created_at->diffForHumans() }}</div>
                </div>
            </div>

            <p class="text-slate-800 whitespace-pre-line">
                {{ $post->content }}
            </p>

            @if($post->image_path)
                <img src="{{ asset('storage/'.$post->image_path) }}"
                     class="mt-3 w-full max-h-[520px] rounded-xl object-cover"
                     alt="post image">
            @endif
        </div>
    @empty
        <p class="text-sm text-slate-500">Aucun post pour le moment.</p>
    @endforelse

    <div class="mt-4">
        {{ $posts->links() }}
    </div>
</div>
</x-app-layout>
