<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DomainCheckResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        if (is_array($this->resource)) {
            return [
                'name' => $this->resource['domain'],
                'is_available' => $this->resource['is_available'],
                'expires_at' => $this->resource['expires_at'],
            ];
        }

        return [
            'name' => $this->domain,
            'is_available' => $this->is_available,
            'expires_at' => $this->expires_at?->format('Y-m-d'),
        ];
    }
}
