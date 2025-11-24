<?php

namespace Modules\CustomProfileTabs\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model
{
    protected $table = 'nexopos_custom_field_values';

    protected $fillable = [
        'field_id',
        'entity_id',
        'entity_type',
        'value',
    ];

    /**
     * Get the field definition
     */
    public function field()
    {
        return $this->belongsTo(CustomFieldDefinition::class, 'field_id');
    }

    /**
     * Get values for a specific entity
     */
    public static function getEntityValues($entityId, $entityType)
    {
        return self::where('entity_id', $entityId)
            ->where('entity_type', $entityType)
            ->with('field')
            ->get()
            ->pluck('value', 'field.name');
    }

    /**
     * Save or update a field value
     */
    public static function saveValue($fieldId, $entityId, $entityType, $value)
    {
        return self::updateOrCreate(
            [
                'field_id' => $fieldId,
                'entity_id' => $entityId,
                'entity_type' => $entityType,
            ],
            [
                'value' => $value,
            ]
        );
    }
}
