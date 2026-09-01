<?php

namespace App\Models;

use App\Models\Concerns\IsSingleton;
use Illuminate\Database\Eloquent\Model;

/**
 * Single row holding non-stream app configuration (links, about text, the
 * minimum supported client version used for the force-update gate).
 */
class AppSetting extends Model
{
    use IsSingleton;

    protected $fillable = [
        'about_text',
        'website_url',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'tiktok_url',
        'contact_phone',
        'contact_email',
        'privacy_policy_url',
        'android_store_url',
        'min_supported_version',
    ];

    protected $attributes = [
        'min_supported_version' => '1.0.0',
    ];
}
