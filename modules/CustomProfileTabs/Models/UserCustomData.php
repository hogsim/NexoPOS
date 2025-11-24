<?php

namespace Modules\CustomProfileTabs\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserCustomData extends Model
{
    protected $table = 'nexopos_custom_profile_tabs_user_data';

    protected $fillable = [
        'user_id',
        'company_name',
        'job_title',
        'department',
        'bio',
        'linkedin_url',
    ];

    /**
     * Define relationship to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
