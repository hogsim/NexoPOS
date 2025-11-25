<?php

namespace Modules\NsCustomFields\Settings;

use App\Services\SettingsPage;

class CustomFieldsSettings extends SettingsPage
{
    const IDENTIFIER = 'ns.custom-fields';
    const AUTOLOAD = true;

    public function getView()
    {
        return view('NsCustomFields::settings');
    }
}
