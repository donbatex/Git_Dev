<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromptGenerationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'id' => $this->id,
            'image_url' => $this->img_path ? asset('storage/' . $this->img_path) : null,
            'generated_prompt' => $this->generated_prompt,
            'original_filename' => $this->original_filename,
            'image_size' => $this->image_size,
            'mime_type' => $this->mime_type,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
