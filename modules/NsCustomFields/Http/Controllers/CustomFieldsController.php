<?php

namespace Modules\NsCustomFields\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Services\Options;

class CustomFieldsController extends Controller
{
    protected $options;

    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    public function getConfig()
    {
        $config = $this->options->get('ns_custom_fields_config', '[]');
        
        return response()->json([
            'value' => $config
        ]);
    }

    public function saveConfig(Request $request)
    {
        $config = $request->input('ns_custom_fields_config');
        
        // Validate JSON
        $decoded = json_decode($config, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'status' => 'error',
                'message' => __('Invalid JSON format')
            ], 400);
        }

        $this->options->set('ns_custom_fields_config', $config);

        return response()->json([
            'status' => 'success',
            'message' => __('Custom fields configuration saved successfully')
        ]);
    }
}
