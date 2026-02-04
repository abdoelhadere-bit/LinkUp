<?php

use Livewire\Component;
use App\Models\Post;
use App\Models\Like;
use App\Models\Comment;


new class extends Component
{
    protected $listeners = ['post-created' => '$refresh'];
    public array $commentText = [];

    public function posts()
    {
        return Post::query()
            ->with(['user', 'likes', 'comments.user'])
            ->latest()
            ->limit(20)
            ->get();
    }

    public function toggleLike(int $postId)
    {
        $me = auth()->id();

        $isLiked = Like::query()
                    ->where('user_id', $me)->where('post_id', $postId)
                    ->first();

        if($isLiked) $isLiked->delete();

        if(!$isLiked) Like::create(['user_id'=>$me, 'post_id'=>$postId]);
        

        $this->dispatch('$refresh');
    }

    public function addComment(int $postId): void
    {
        $text = trim($this->commentText[$postId]) ?? '';
        
        if(!$text) return;

        Comment::create([
            'user_id'=>auth()->id(),
            'post_id'=>$postId,
            'content'=>$text 
        ]);

        $this->commentText[$postId] = '';
        $this->dispatch('$refresh');
    }
 
    public function deleteComment(int $commentId)
    {
        $comment = Comment::findOrFail($commentId);

        if($comment->user_id !== auth()->id()){
            abort(403);
        }
        $comment->delete();
        $this->dispatch('$refresh');
    }
};

?>

<div class="space-y-4">
    @forelse($this->posts() as $post)
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 p-4">
            @php 
                $liked = $post->likes->contains('user_id', auth()->id());
                $count = $post->likes->count();
            @endphp
            {{-- Header --}}
            <div class="flex items-center gap-3 mb-2">
                <img
                    class="h-10 w-10 rounded-full object-cover"
                    src="{{ $post->user->avatar_path
                        ? asset('storage/'.$post->user->avatar_path)
                        : 'https://ui-avatars.com/api/?name='.urlencode($post->user->username)
                    }}"
                    alt="{{ $post->user->username }}"
                >

                <div>
                    <div class="font-medium text-slate-900">{{ $post->user->username }}</div>
                    <div class="text-xs text-slate-500">{{ $post->created_at->diffForHumans() }}</div>
                </div>
            </div>

            {{-- Content --}}
            <p class="text-slate-800 whitespace-pre-line">
                {{ $post->content}}
            </p>

            {{-- Image --}}
            @if($post->image_path)
                <img
                    src="{{ asset('storage/'.$post->image_path) }}"
                    class="mt-3 w-full max-h-[520px] rounded-xl object-cover"
                    alt="post image"
                >
            @endif
            <button wire:click="toggleLike({{$post->id}})">
                {{$liked ? '❤️ ' : '🤍 Like' }}
            </button>
            <span>{{$count}} Like{{$count==1 ? '':'s'}}</span>
            {{-- Comments --}}
            <div class="mt-4 border-t pt-3">

            <!-- Comment -->
            {{-- Add comment --}}
                <div class="flex items-center gap-2">
                    <input
                        type="text"
                        wire:model.live="commentText.{{ $post->id }}"
                        class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-sm"
                        placeholder="Écrire un commentaire...">
            
                    <button
                        wire:click="addComment({{ $post->id }})"
                        class="rounded-xl bg-slate-900 px-3 py-2 text-sm text-white">
                        Ajouter
                    </button>
                </div>
            
                {{-- List comments --}}
                <div class="mt-3 space-y-2">
                    @forelse($post->comments as $c)
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-2">
                                <img
                                    class="h-7 w-7 rounded-full object-cover"
                                    src="{{ $c->user->avatar_path
                                        ? asset('storage/'.$c->user->avatar_path)
                                        : 'https://ui-avatars.com/api/?name='.urlencode($c->user->username)
                                    }}"
                                    alt="{{ $c->user->username }}">
            
                                <div class="bg-slate-50 border border-slate-200 rounded-2xl px-3 py-2">
                                    <div class="text-xs font-semibold text-slate-900">
                                        {{ $c->user->username }}
                                        <span class="ml-2 font-normal text-slate-400">
                                            {{ $c->created_at->diffForHumans() }}
                                        </span>
                                    </div>
            
                                    <div class="text-sm text-slate-800 whitespace-pre-line">
                                        {{ $c->content }}
                                    </div>
                                </div>
                            </div>
            
                            @if($c->user_id === auth()->id())
                                <button
                                    wire:click="deleteComment({{ $c->id }})"
                                    class="text-xs text-red-600 hover:underline"
                                >
                                    Supprimer
                                </button>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-500">Aucun commentaire.</p>
                    @endforelse
                </div>
            </div>
        </div>

    @empty
        <p class="text-sm text-slate-500">Aucun post pour le moment.</p>
    @endforelse
</div>
