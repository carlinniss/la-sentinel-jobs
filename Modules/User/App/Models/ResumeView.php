<?php

declare(strict_types=1);

namespace Modules\User\App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeView extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['profile_id', 'employer_user_id', 'action'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_user_id');
    }
}
