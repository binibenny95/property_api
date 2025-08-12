<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Node extends Model
{
    protected $fillable = [
        'name',
        'type',
        'parent_id',
        'relationship_to_parent',
        'height',
        'zip-code',
        'monthly_rent',
        'tenancy_active',
        'moved_in_at',
        'created_by'
    ];

    protected $casts = [
        'tenancy_active' => 'boolean',
        'moved_in_at' => 'date',
        'monthly_rent' => 'decimal:2',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    //  validate the parent Type
    public function validateParentType(): bool
    {
        if(!$this->parent_id) {
            return $this->type === 'Corporation';
        }

        $parent = $this->parent;
        return match ($this->type) {
            'Building' => $parent->type === 'Corporation',
            'Property' => $parent->type === 'Building',
            'Tenancy Period' => $parent->type === 'Property',
            'Tenant' => $parent->type === 'Tenancy Period',
            default => false,
        };
    }

    public function validateTenancyRules(): bool
    {
          // Check active tenancy limit
        if ($this->type === 'Tenancy Period' && $this->tenancy_active) {
            $activeExists = Node::where('parent_id', $this->parent_id)
                ->where('type', 'Tenancy Period')
                ->where('tenancy_active', true)
                ->where('id', '!=', $this->id)
                ->exists();

            return !$activeExists;
        }


        if ($this->type === 'Tenant') {
            $tenantCount = Node::where('parent_id', $this->parent_id)
                              ->where('type', 'Tenant')
                              ->count();
            return $tenantCount < 4;
        }

        return true;
    }

}
