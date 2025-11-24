<?php

namespace Modules\CustomProfileTabs\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldDefinition extends Model
{
    protected $table = 'nexopos_custom_field_definitions';

    protected $fillable = [
        'name',
        'label',
        'type',
        'applies_to',
        'description',
        'options',
        'validation',
        'order',
        'active',
    ];

    protected $casts = [
        'options' => 'array',
        'active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get values for this field definition
     */
    public function values()
    {
        return $this->hasMany(CustomFieldValue::class, 'field_id');
    }

    /**
     * Get active fields for a specific entity type
     */
    public static function getActiveFields($appliesTo)
    {
        return self::where('applies_to', $appliesTo)
            ->where('active', true)
            ->orderBy('order')
            ->get();
    }
}
