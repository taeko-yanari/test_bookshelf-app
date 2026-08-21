<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'book_id' => $this->book_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'user_id' => $this->user_id,
            'user_name' => $this->user->name,
            'posted_at' => $this->created_at,
        ];
    }
}