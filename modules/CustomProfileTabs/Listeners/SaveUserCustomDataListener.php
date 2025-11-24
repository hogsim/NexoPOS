<?php

namespace Modules\CustomProfileTabs\Listeners;

use App\Classes\Hook;
use Illuminate\Support\Facades\Auth;
use Modules\CustomProfileTabs\Models\UserCustomData;

class SaveUserCustomDataListener
{
    /**
     * Handle the event.
     */
    public function handle($event)
    {
        // This will be triggered when user profile form is saved
        $request = request();

        if ($request->has('custom')) {
            $customData = UserCustomData::firstOrNew([
                'user_id' => Auth::id(),
            ]);

            $customData->company_name = $request->input('custom.company_name');
            $customData->job_title = $request->input('custom.job_title');
            $customData->department = $request->input('custom.department');
            $customData->bio = $request->input('custom.bio');
            $customData->linkedin_url = $request->input('custom.linkedin_url');

            $customData->save();
        }
    }
}
