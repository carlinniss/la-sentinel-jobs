<?php

declare(strict_types=1);

namespace Modules\User\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Modules\User\App\Models\Profile;
use Modules\User\App\Models\User;
use Modules\User\App\Notifications\ResumeSignupNotification;
use Modules\User\App\Services\ResumeCheckoutService;
use Modules\User\App\Services\ResumeUploadService;
use Throwable;

class PostResumeController extends Controller
{
    private const SOURCE_SESSION_KEY = 'resume_signup_source';

    public function __construct(
        private readonly ResumeUploadService $uploads,
        private readonly ResumeCheckoutService $checkout,
    ) {}

    public function show(Request $request): View
    {
        $source = Profile::normalizeSource($request->query('src'));

        if ($source !== null) {
            $request->session()->put(self::SOURCE_SESSION_KEY, $source);
        }

        $user = $request->user();
        $profile = $user?->profile;

        if ($user && $profile) {
            $profile->recordSignupSource($request->session()->get(self::SOURCE_SESSION_KEY));
        }

        return view('user::resumes.post', [
            'user' => $user,
            'profile' => $profile,
            'feeCents' => $this->uploads->feeCents(),
        ]);
    }

    public function alias(Request $request): RedirectResponse
    {
        return redirect()->route('resume.post', $request->query());
    }

    public function store(Request $request): RedirectResponse
    {
        return $request->user()
            ? $this->storeForMember($request, $request->user())
            : $this->storeForGuest($request);
    }

    public function done(Request $request): View
    {
        return view('user::resumes.done', [
            'user' => $request->user(),
            'profile' => $request->user()->profile,
        ]);
    }

    private function storeForGuest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique((new User)->getTable(), 'email')],
            'password' => ['required', Password::defaults()],
            'terms' => ['accepted'],
            'resume' => ResumeUploadService::FILE_RULES,
            'resume_searchable' => ['nullable', 'boolean'],
            'phone' => ['nullable', 'string', 'max:40'],
        ], [
            'email.unique' => 'An account already uses this email. Log in below to add your resume.',
            'terms.accepted' => 'Please accept the terms to create your account.',
        ]);

        $user = User::registerFrom([
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        event(new Registered($user));

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        $profile = $this->uploads->profileFor($user);

        if (filled($validated['phone'] ?? null)) {
            $profile->phone = $validated['phone'];
        }

        $profile->recordSignupSource($request->session()->get(self::SOURCE_SESSION_KEY));

        return $this->saveResume($request, $user, $profile, notify: true);
    }

    private function storeForMember(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'resume' => ResumeUploadService::FILE_RULES,
            'resume_searchable' => ['nullable', 'boolean'],
        ]);

        $profile = $this->uploads->profileFor($user);

        if (! $request->hasFile('resume') && ! $profile->hasResume()) {
            return back()->withErrors(['resume' => 'Choose a PDF, DOC, or DOCX file to upload.']);
        }

        $profile->recordSignupSource($request->session()->get(self::SOURCE_SESSION_KEY));

        return $this->saveResume($request, $user, $profile, notify: false);
    }

    private function saveResume(Request $request, User $user, Profile $profile, bool $notify): RedirectResponse
    {
        $file = $request->file('resume');
        $requiresPayment = $file !== null && $this->uploads->requiresPayment($profile);

        if ($requiresPayment && ! $this->checkout->configured()) {
            $file = null;
            $requiresPayment = false;
            $request->session()->flash('resume_notice', 'Resume payment is not available right now, so your file was not saved. Please try again later.');
        }

        $this->uploads->store($user, $profile, $file, $request->boolean('resume_searchable'));

        if ($notify) {
            $this->sendConfirmation($user, $profile->hasResume());
        }

        if ($requiresPayment) {
            try {
                return redirect()->away((string) $this->checkout->create($profile)->url);
            } catch (Throwable $exception) {
                report($exception);
                $request->session()->flash('resume_notice', 'The secure payment page could not be started. Your resume remains private.');
            }
        }

        return redirect()->route('resume.done');
    }

    private function sendConfirmation(User $user, bool $hasResume): void
    {
        try {
            $user->notify(new ResumeSignupNotification($hasResume));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
