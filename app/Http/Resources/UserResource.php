<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'joined' => Carbon::parse($this->created_at)->format('F Y'),
            'verified' => false,
            'rating' => null,
            'reviews' => null,
            'avatar' => $this->user_detail?->avatar,
            'profession' => $this->user_detail?->profession,
            'status_emoji' => $this->user_detail?->status_emoji,
            'status_text' => $this->user_detail?->status_text,
            'location' => $this->user_detail?->location,
            'hourly_rate' => null,
            'completed_jobs' => null,
            'response_time' => $this->user_detail?->responseTime,
        ];
    }
}
