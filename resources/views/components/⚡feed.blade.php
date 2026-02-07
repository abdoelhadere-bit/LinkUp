<?php
use Livewire\WithFileUploads;

use Livewire\Component;
use App\Models\Post;
use App\Models\Like;
use App\Models\Comment;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;
    protected $listeners = ['post-created' => 'refreshComponent'];

    public array $commentText = [];
    public $showComments = [];
    public $expandedComments = [];

    // Edition
    public ?int $editingPostId = null;
    public string $editContent = '';
    public $newPhoto;

    public function toggleComments($postId)
    {
        if (in_array($postId, $this->showComments)) {
            $this->showComments = array_diff($this->showComments, [$postId]);
        } else {
            $this->showComments[] = $postId;
        }
    }

    public function toggleExpandComments($postId)
    {
        if (in_array($postId, $this->expandedComments)) {
            $this->expandedComments = array_diff($this->expandedComments, [$postId]);
        } else {
            $this->expandedComments[] = $postId;
        }
    }

    public function addComment($postId)
    {
        $content = $this->commentText[$postId] ?? null;

        if (empty(trim($content))) {
            return;
        }

        Comment::create([
            'post_id' => $postId,
            'user_id' => auth()->id(),
            'content' => $content,
        ]);

        $this->commentText[$postId] = '';
        
        if (!in_array($postId, $this->showComments)) {
            $this->showComments[] = $postId;
        }
    }

    public function deleteComment($commentId)
    {
        $comment = Comment::findOrFail($commentId);
        
        if ($comment->user_id === auth()->id()) {
            $comment->delete();
        }
    }

    public function posts()
    {
        return Post::query()
            ->with(['user', 'likes', 'comments.user'])
            ->latest()
            ->limit(20)
            ->get();
    }

    public function toggleLike(int $postId): void
    {
        $me = auth()->id();

        $like = Like::query()
            ->where('user_id', $me)
            ->where('post_id', $postId)
            ->first();

        if ($like) {
            $like->delete();
        } else {
            Like::create([
                'user_id' => $me,
                'post_id' => $postId,
            ]);
        }

    }


    public function deletePost(int $postId): void
    {
        $post = Post::findOrFail($postId);

        if ($post->user_id !== auth()->id()) {
            abort(403);
        }

        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }

        $post->delete();

        if ($this->editingPostId === $postId) {
            $this->cancelEdit();
        }
    }

    public function startEdit(int $postId): void
    {
        $post = Post::findOrFail($postId);

        if ($post->user_id !== auth()->id()) {
            abort(403);
        }

        $this->editingPostId = $postId;
        $this->editContent = $post->content;
        $this->newPhoto = null;
        // $this->resetErrorBag('editContent');
    }

    public function cancelEdit(): void
    {
        $this->editingPostId = null;
        $this->editContent = '';
        $this->resetErrorBag('editContent');
    }

    public function saveEdit(): void
    {
       if(!$this->editingPostId) return;

       $post = Post::findOrFail($this->editingPostId);
       
       $this->validate([
            'editContent' => 'required|string',
            'newPhoto' => 'nullable|image|max:2048',
       ]);

       $data = ['content' => $this->editContent];

       if($this->newPhoto){
        if($post->image_path) Storage::disk('public')->delete($post->image_path);
       }
       $data['image_path'] = $this->newPhoto->store('posts', 'public');

       $post->update($data);
       $this->cancelEdit();

       $this->dispatch('$refresh');

    }
};

?>
<div class="space-y-4">
    @forelse($this->posts() as $post)
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 p-4" wire:key="post-{{ $post->id }}">
            @php
                $liked = $post->likes->contains('user_id', auth()->id());
                $likeCount = $post->likes->count();
                $commentCount = $post->comments->count();
                $showAllComments = in_array($post->id, $this->expandedComments ?? []);
                $displayedComments = $showAllComments ? $post->comments : $post->comments->take(2);
                $remainingComments = $commentCount - 2;
            @endphp

            {{-- Header --}}
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-3">
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

                {{-- Actions --}}
                @if($post->user_id === auth()->id())
                    <div class="flex gap-3">
                        <button
                            wire:click="startEdit({{ $post->id }})"
                            class="text-sm text-blue-600 hover:underline"
                        >
                            Modifier
                        </button>

                        <button
                            wire:click="deletePost({{ $post->id }})"
                            onclick="return confirm('Vraiment supprimer ce post ?')"
                            class="text-sm text-red-600 hover:underline"
                        >
                            Supprimer
                        </button>
                    </div>
                @endif
            </div>

            {{-- Content / Edit mode --}}
            @if($editingPostId === $post->id)
                <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-xl space-y-2">
                    <textarea
                        wire:model.live="editContent"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                        rows="3"
                    ></textarea>

                    @error('editContent')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="flex justify-end gap-2">
                        <button
                            wire:click="cancelEdit"
                            class="px-3 py-2 rounded-xl bg-slate-200 text-slate-700 text-sm"
                        >
                            Annuler
                        </button>

                        <button
                            wire:click="saveEdit"
                            class="px-3 py-2 rounded-xl bg-slate-900 text-white text-sm"
                        >
                            Enregistrer
                        </button>
                    </div>
                </div>
            @else
                <p class="text-slate-800 whitespace-pre-line">
                    {{ $post->content }}
                </p>

                @if($post->image_path)
                    <img
                        src="{{ asset('storage/'.$post->image_path) }}"
                        class="mt-3 w-full max-h-[520px] rounded-xl object-cover"
                        alt="post image"
                    >
                @endif
            @endif

            {{-- Like & Comment Stats --}}
            @if($likeCount > 0 || $commentCount > 0)
                <div class="mt-3 flex items-center justify-between text-sm text-slate-600">
                    @if($likeCount > 0)
                        <span>{{ $likeCount }} Like{{ $likeCount == 1 ? '' : 's' }}</span>
                    @else
                        <span></span>
                    @endif
                    
                    @if($commentCount > 0)
                        <button 
                            wire:click="toggleComments({{ $post->id }})"
                            class="hover:underline"
                        >
                            {{ $commentCount }} Commentaire{{ $commentCount == 1 ? '' : 's' }}
                        </button>
                    @endif
                </div>
            @endif

            {{-- Action Buttons (Like & Comment) --}}
            <div class="mt-2 flex items-center border-t pt-2">
                <button 
                    wire:click="toggleLike({{ $post->id }})" 
                    class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-slate-100 transition text-sm font-medium {{ $liked ? 'text-red-600' : 'text-slate-700' }}"
                >
                    <span class="text-lg">{{ $liked ? '❤️' : '🤍' }}</span>
                    <span>J'aime</span>
                </button>

                <button 
                    wire:click="toggleComments({{ $post->id }})"
                    class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-slate-100 transition text-sm font-medium text-slate-700"
                >
                    <span class="text-lg">💬</span>
                    <span>Commenter</span>
                </button>
            </div>

            {{-- Comments Section (Toggle) --}}
            @if(in_array($post->id, $showComments ?? []))
                <div class="mt-3 border-t pt-3 space-y-3">
                    {{-- Show more comments button --}}
                    @if($commentCount > 2 && !$showAllComments)
                        <button 
                            wire:click="toggleExpandComments({{ $post->id }})"
                            class="text-sm text-slate-600 hover:underline font-medium"
                        >
                            Voir les {{ $remainingComments }} commentaires précédents
                        </button>
                    @endif

                    {{-- List comments --}}
                    <div class="space-y-2">
                        @forelse($displayedComments as $c)
                            <div class="flex items-start gap-2">
                                <img
                                    class="h-8 w-8 rounded-full object-cover flex-shrink-0"
                                    src="{{ $c->user->avatar_path
                                        ? asset('storage/'.$c->user->avatar_path)
                                        : 'https://ui-avatars.com/api/?name='.urlencode($c->user->username)
                                    }}"
                                    alt="{{ $c->user->username }}"
                                >

                                <div class="flex-1 min-w-0">
                                    <div class="bg-slate-100 rounded-2xl px-3 py-2 inline-block">
                                        <div class="text-sm font-semibold text-slate-900">
                                            {{ $c->user->username }}
                                        </div>
                                        <div class="text-sm text-slate-800 break-words">
                                            {{ $c->content }}
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 mt-1 px-3 text-xs text-slate-500">
                                        <span>{{ $c->created_at->diffForHumans() }}</span>
                                        
                                        @if($c->user_id === auth()->id())
                                            <button
                                                wire:click="deleteComment({{ $c->id }})"
                                                class="text-red-600 hover:underline"
                                            >
                                                Supprimer
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 text-center py-2">Soyez le premier à commenter.</p>
                        @endforelse
                    </div>

                    {{-- Show less button --}}
                    @if($showAllComments && $commentCount > 2)
                        <button 
                            wire:click="toggleExpandComments({{ $post->id }})"
                            class="text-sm text-slate-600 hover:underline font-medium"
                        >
                            Voir moins de commentaires
                        </button>
                    @endif

                    {{-- Add comment --}}
                    <div class="flex items-start gap-2">
                        <img
                            class="h-8 w-8 rounded-full object-cover flex-shrink-0"
                            src="{{ auth()->user()->avatar_path
                                ? asset('storage/'.auth()->user()->avatar_path)
                                : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->username)
                            }}"
                            alt="{{ auth()->user()->username }}"
                        >

                        <div class="flex-1 flex items-center gap-2">
                            <input
                                type="text"
                                wire:model="commentText.{{ $post->id }}"
                                class="flex-1 rounded-full border border-slate-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                                placeholder="Écrire un commentaire..."
                                wire:keydown.enter="addComment({{ $post->id }})"
                            >

                            <button
                                wire:click="addComment({{ $post->id }})"
                                class="rounded-full bg-slate-900 p-2 text-white hover:bg-slate-800 transition w-8 h-8 flex items-center justify-center"
                                title="Envoyer"
                            >
                                ➤
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @empty
        <p class="text-sm text-slate-500">Aucun post pour le moment.</p>
    @endforelse
</div>