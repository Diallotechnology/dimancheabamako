<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

use function Flasher\Prime\flash;

final class ContactController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function sendEmail(Request $request)
    {
        // validate fields
        Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string',
            'captcha' => 'required|captcha',
        ], [
            'captcha.captcha' => __('messages.captcha'),
        ])->validate();
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ];
        Mail::mailer('contact')->to('contact@dimancheabamako.com')->send(new ContactMail($data));
        flash()->success('Votre message a été envoyer avec success!');

        return back();
    }

    // public function refreshCaptcha(): JsonResponse
    // {
    //     return response()->json(['captcha' => captcha_img()]);
    // }
}
