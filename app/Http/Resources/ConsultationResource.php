<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationResource extends JsonResource
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
            'booking' => new BookingResource($this->whenLoaded('booking')),
            'consultation_date' => $this->tanggal_konsultasi,
            'start_time' => $this->waktu_mulai,
            'end_time' => $this->waktu_selesai,
            'notes' => $this->catatan,
            'diagnosis' => $this->diagnosis,
            'recommendations' => $this->rekomendasi,
            'status' => $this->status,
            'meeting_link' => $this->link_meeting,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
