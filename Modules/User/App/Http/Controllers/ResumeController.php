<?php

declare(strict_types=1);

namespace Modules\User\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\User\App\Models\Profile;
use Modules\User\App\Models\ResumeView;
use Modules\User\App\Services\ResumeCheckoutService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ResumeController extends Controller
{
    public function __construct(private readonly ResumeCheckoutService $checkout)
    {
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validateWithBag('resume', [
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'resume_searchable' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $profile = $user->profile()->firstOrCreate(['user_id' => $user->getKey()]);
        $file = $request->file('resume');
        $feeCents = max(0, (int) config('resume.upload_fee_cents'));
        $requiresPayment = $feeCents > 0 && $profile->resume_paid_at === null;

        if (! $file && ! $profile->hasResume()) {
            return back()->withErrors(['resume' => 'Choose a PDF, DOC, or DOCX resume to upload.'], 'resume');
        }

        if ($requiresPayment && ! $this->checkout->configured()) {
            return back()->withErrors(['resume' => 'Resume payment is not configured yet. Please try again later.'], 'resume');
        }

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

        $profile->resume_searchable = $request->boolean('resume_searchable');
        $profile->save();

        if ($requiresPayment) {
            try {
                $session = $this->checkout->create($profile);
            } catch (Throwable $exception) {
                report($exception);

                return back()->withErrors([
                    'resume' => 'The secure payment page could not be started. Your resume remains private. Please try again.',
                ], 'resume');
            }

            return redirect()->away((string) $session->url);
        }

        return back()->with('status', 'resume-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $profile = $request->user()->profile;

        if ($profile?->hasResume()) {
            Storage::disk('local')->delete($profile->resume_path);
            $profile->forceFill([
                'resume_path' => null,
                'resume_original_name' => null,
                'resume_mime' => null,
                'resume_size' => null,
                'resume_uploaded_at' => null,
                'resume_searchable' => false,
                'resume_fee_cents' => 0,
                'resume_paid_at' => null,
                'resume_checkout_session_id' => null,
            ])->save();
        }

        return back()->with('status', 'resume-deleted');
    }

    public function index(Request $request): View
    {
        $this->assertResumeAccess($request);
        $search = trim((string) $request->query('q', ''));

        $profiles = Profile::query()
            ->with('user:id,name')
            ->where('resume_searchable', true)
            ->whereNotNull('resume_path')
            ->where(function ($query): void {
                $query->where('resume_fee_cents', 0)->orWhereNotNull('resume_paid_at');
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('city', 'like', '%'.$search.'%')
                        ->orWhere('bio', 'like', '%'.$search.'%')
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderByDesc('resume_uploaded_at')
            ->paginate(18)
            ->withQueryString();

        return view('user::resumes.index', compact('profiles', 'search'));
    }

    public function show(Request $request, Profile $profile): View
    {
        $this->assertDiscoverable($profile);
        $this->assertResumeAccess($request);
        $profile->loadMissing('user:id,name');
        $this->recordEmployerActivity($request, $profile, 'profile_view');

        return view('user::resumes.show', compact('profile'));
    }

    public function download(Request $request, Profile $profile): StreamedResponse
    {
        $isOwner = (int) $request->user()->getKey() === (int) $profile->user_id;

        if (! $isOwner) {
            $this->assertDiscoverable($profile);
            $this->assertResumeAccess($request);
            $this->recordEmployerActivity($request, $profile, 'download');
        }

        abort_unless($profile->hasResume() && Storage::disk('local')->exists($profile->resume_path), 404);

        return Storage::disk('local')->download(
            $profile->resume_path,
            basename((string) ($profile->resume_original_name ?: 'resume'))
        );
    }

    public function paymentSuccess(Request $request): RedirectResponse
    {
        $sessionId = trim((string) $request->query('session_id', ''));

        if ($sessionId === '' || ! $this->checkout->fulfill($sessionId)) {
            return redirect()->route('panel.profile.edit')->withErrors([
                'resume' => 'We could not confirm the resume payment. No employer access was granted.',
            ], 'resume');
        }

        return redirect()->route('panel.profile.edit')->with('status', 'resume-payment-complete');
    }

    private function assertResumeAccess(Request $request): void
    {
        $profile = $request->user()->profile;

        abort_unless($profile?->canBrowseResumes(), 403, 'This employer account does not have resume-bank access.');
    }

    private function assertDiscoverable(Profile $profile): void
    {
        abort_unless($profile->isDiscoverable(), 404);
    }

    private function recordEmployerActivity(Request $request, Profile $profile, string $action): void
    {
        ResumeView::query()->create([
            'profile_id' => $profile->getKey(),
            'employer_user_id' => $request->user()->getKey(),
            'action' => $action,
        ]);
    }
}
