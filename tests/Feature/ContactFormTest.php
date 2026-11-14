<?php

declare(strict_types=1);

use App\Mail\ContactMail;
use Illuminate\Support\Facades\Mail;

function contactPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Awa Traoré',
        'email' => 'awa@example.com',
        'subject' => 'Question sur un bazin',
        'message' => 'Bonjour, ce bazin existe-t-il en 6 mètres ?',
    ], $overrides);
}

it('sends the visitor message to the shop', function () {
    Mail::fake();

    $this->post(route('contact.email'), contactPayload())
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    Mail::assertSent(ContactMail::class, fn (ContactMail $mail): bool => $mail->hasTo('contact@dimancheabamako.com')
        && $mail->data['subject'] === 'Question sur un bazin'
        && $mail->data['email'] === 'awa@example.com');
});

it('requires every field', function () {
    Mail::fake();

    $this->post(route('contact.email'), [])
        ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

    Mail::assertNothingSent();
});

it('silently drops submissions that fill the honeypot', function () {
    Mail::fake();

    $this->post(route('contact.email'), contactPayload(['website' => 'http://spam.example']))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    Mail::assertNothingSent();
});

it('throttles the contact form', function () {
    Mail::fake();

    foreach (range(1, 5) as $_) {
        $this->post(route('contact.email'), contactPayload())->assertRedirect();
    }

    $this->post(route('contact.email'), contactPayload())->assertStatus(429);
    Mail::assertSentCount(5);
});
