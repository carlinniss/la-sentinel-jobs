<?php

declare(strict_types=1);

namespace Modules\User\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Profile extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id',
        'avatar',
        'bio',
        'phone',
        'city',
        'country',
        'website',
        'is_verified',
        'resume_path',
        'resume_original_name',
        'resume_mime',
        'resume_size',
        'resume_uploaded_at',
        'resume_searchable',
        'resume_access_enabled',
        'resume_fee_cents',
        'resume_paid_at',
        'resume_checkout_session_id',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'resume_uploaded_at' => 'datetime',
        'resume_searchable' => 'boolean',
        'resume_access_enabled' => 'boolean',
        'resume_fee_cents' => 'integer',
        'resume_paid_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resumeViews()
    {
        return $this->hasMany(ResumeView::class);
    }

    public function hasResume(): bool
    {
        return is_string($this->resume_path) && $this->resume_path !== '';
    }

    public function isDiscoverable(): bool
    {
        $paymentSatisfied = $this->resume_fee_cents === 0 || $this->resume_paid_at !== null;

        return $this->hasResume() && $this->resume_searchable && $paymentSatisfied;
    }

    public function canBrowseResumes(): bool
    {
        return $this->is_verified && $this->resume_access_enabled;
    }

    public static function detailsForUser(User $user): ?self
    {
        return static::query()
            ->where('user_id', $user->getKey())
            ->first();
    }

    public static function phoneForUser(User $user): ?string
    {
        return static::query()
            ->where('user_id', $user->getKey())
            ->value('phone');
    }
}
