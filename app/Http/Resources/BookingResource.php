<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'booking_code' => $this->kode_booking,
            'user' => new UserResource($this->whenLoaded('user')),
            'schedule' => new ScheduleResource($this->whenLoaded('schedule')),
            'consultation_type' => $this->tipe_konsultasi,
            'notes' => $this->catatan,
            'status' => $this->status,
            'booking_date' => $this->tanggal_booking,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
