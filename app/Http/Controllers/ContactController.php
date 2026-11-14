<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\ContactMail;
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
        Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'website' => 'nullable|string',
        ])->validate();

        // Piège à robots : champ invisible pour un humain. Rempli, on répond comme si tout s'était bien passé.
        if ($request->filled('website')) {
            return back();
        }

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
}
