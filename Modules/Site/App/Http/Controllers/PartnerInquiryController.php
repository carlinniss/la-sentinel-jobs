<?php

declare(strict_types=1);

namespace Modules\Site\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PartnerInquiryController extends Controller
{
    public function create(): View
    {
        return view('site::partner-inquiry');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:160'],
            'contact_name' => ['required', 'string', 'max:160'],
            'contact_information' => ['required', 'string', 'max:1000'],
            'comments' => ['nullable', 'string', 'max:3000'],
        ]);

        Mail::send('site::emails.partner-inquiry', ['inquiry' => $validated], function ($message) use ($validated): void {
            $message
                ->to(['pamela@lasentinel.net', 'carl@lasentinel.net'])
                ->subject('LA Sentinel Jobs partner inquiry: '.$validated['company_name']);
        });

        return redirect()
            ->route('partners.inquiry')
            ->with('success', 'Thanks. Your partner inquiry was sent to the LA Sentinel team.');
    }
}
