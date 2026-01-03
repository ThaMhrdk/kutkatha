<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PsikologResource extends JsonResource
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
            'user' => new UserResource($this->whenLoaded('user')),
            'specialization' => $this->spesialisasi,
            'license_number' => $this->nomor_lisensi,
            'experience_years' => $this->pengalaman_tahun,
            'education' => $this->pendidikan,
            'bio' => $this->bio,
            'consultation_fee' => $this->tarif_konsultasi,
            'rating' => $this->rating,
            'total_consultations' => $this->total_konsultasi,
            'is_verified' => $this->is_verified,
            'is_available' => $this->is_available,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
