<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
            'name' => $this->name,
            'directory_id' => $this->directory_id,
            'directory_label' => $this->whenLoaded('directory', fn () => $this->directory?->name),
            'directory_slug' => $this->whenLoaded('directory', fn () => $this->directory?->slug),
            'created_at' => $this->created_at,
            'profile_data' => [
                'id' => $this->id,
                'firstname' => $this->whenLoaded('user', fn () => $this->user?->firstname),
                'lastname' => $this->whenLoaded('user', fn () => $this->user?->lastname),
                'email' => $this->whenLoaded('user', fn () => $this->user?->email),
                'email_verified_at' => $this->whenLoaded('user', fn () => $this->user?->email_verified_at),
                'joined' => Carbon::parse($this->created_at)->format('F Y'),
                'avatar' => $this->avatar ?? $this->user?->userDetail?->avatar,
                'profession' => $this->whenLoaded('jobCategory', fn () => $this->jobCategory?->name) ?? $this->user?->userDetail?->profession,
                'status_text' => $this->bio ?? $this->user?->userDetail?->status_text,
                'status_emoji' => $this->user?->userDetail?->status_emoji,
                'location' => $this->address,

                'verified' => $this->whenLoaded(
                    'user',
                    fn () => !is_null($this->user?->email_verified_at)
                ),

                'rating' => null, // todo: add reviews system
                'reviews' => null, // todo: add reviews table

                'hourly_rate' => $this->hourly_rate,
                'completed_jobs' => $this->completed_jobs,
                'response_time' => $this->response_time,
            ],
        ];
    }
}
