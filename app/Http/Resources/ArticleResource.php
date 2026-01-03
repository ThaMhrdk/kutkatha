<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->judul,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->konten,
            'image_url' => $this->gambar ? asset('storage/' . $this->gambar) : null,
            'author' => new UserResource($this->whenLoaded('author')),
            'category' => $this->kategori,
            'tags' => $this->tags,
            'views_count' => $this->jumlah_views,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
