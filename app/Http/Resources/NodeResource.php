<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NodeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'relationship_to_parent' => $this->relationship_to_parent,
            'parent_id' => $this->parent_id,
            'height' => (int) $this->height,
            'zip_code' => $this->zip_code,
            'tenancy_active' => $this->tenancy_active,
            'moved_in_at' => $this->moved_in_at ? $this->moved_in_at->toDateString() : null,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
