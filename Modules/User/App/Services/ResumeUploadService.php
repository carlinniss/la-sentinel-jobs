<?php

declare(strict_types=1);

namespace Modules\User\App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\User\App\Models\Profile;
use Modules\User\App\Models\User;

class ResumeUploadService
{
    public const FILE_RULES = ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'];

    public function feeCents(): int
    {
        return max(0, (int) config('resume.upload_fee_cents'));
    }

    public function profileFor(User $user): Profile
    {
        return $user->profile()->firstOrCreate(['user_id' => $user->getKey()]);
    }

    public function requiresPayment(Profile $profile): bool
    {
        return $this->feeCents() > 0 && $profile->resume_paid_at === null;
    }

    public function store(User $user, Profile $profile, ?UploadedFile $file, bool $searchable): bool
    {
        $feeCents = $this->feeCents();
        $requiresPayment = $this->requiresPayment($profile);

        if ($file) {
            $oldPath = $profile->resume_path;
            $path = $file->store('resumes/'.$user->getKey(), 'local');

            $profile->fill([
                'resume_path' => $path,
                'resume_original_name' => $file->getClientOriginalName(),
                'resume_mime' => $file->getMimeType(),
                'resume_size' => $file->getSize(),
                'resume_uploaded_at' => now(),
                'resume_fee_cents' => $feeCents,
            ]);

            if ($feeCents === 0) {
                $profile->resume_paid_at = now();
                $profile->resume_checkout_session_id = null;
            }

            if (is_string($oldPath) && $oldPath !== '' && $oldPath !== $path) {
                Storage::disk('local')->delete($oldPath);
            }
        }

        $profile->resume_searchable = $searchable;
        $profile->save();

        return $requiresPayment;
    }
}
