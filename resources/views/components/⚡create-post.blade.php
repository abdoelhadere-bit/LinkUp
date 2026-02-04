<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Post;

new class extends Component
{
    use WithFileUploads;

    public string $content = '';
    public $image = null;

    public function store(): void
    {
        // au moins un des deux
        if (trim($this->content) === '' && !$this->image) {
            $this->addError('content', 'Écris quelque chose ou ajoute une image.');
            return;
        }

        // validation
        $this->validate([
            'content' => ['nullable', 'string', 'max:2000'],
            'image'   => ['nullable', 'image', 'max:2048'], 
        ]);

        // upload image 
        $path = null;
        if ($this->image) {
            $path = $this->image->store('posts', 'public');
        }

        // create post
        Post::create([
            'user_id'    => auth()->id(),
            'content'    => $this->content,
            'image_path' => $path,
        ]);

        // reset form
        $this->reset(['content', 'image']);
        $this->resetErrorBag();

        // notifier le feed
        $this->dispatch('post-created');
    }
};

?>

<div>
    <form wire:submit.prevent="store" class="space-y-3">
        {{-- textarea --}}
        <textarea
            wire:model.live="content"
            rows="3"
            class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-gray-900/10 focus:border-slate-300"
            placeholder="Écrire un post..."
        ></textarea>

        @error('content')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror

        {{-- image upload --}}
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <input type="file" wire:model="image" class="text-sm" />

                {{-- preview (optionnel mais cool) --}}
                @if($image)
                    <img src="{{ $image->temporaryUrl() }}" class="h-12 w-12 rounded-xl object-cover" alt="preview">
                @endif
            </div>

            <button
                type="submit"
                class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
            >
                Publier
            </button>
        </div>

        @error('image')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </form>
</div>
