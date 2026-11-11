<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * Jeton en clair du dernier e-mail de confirmation d'inscription (la base n'en garde que le hash).
 */
function sentConfirmationToken(): string
{
    return (string) Illuminate\Support\Facades\Mail::sent(App\Mail\ConfirmRegistrationMail::class)->last()->token;
}

/**
 * Reproduit le bug local : le fichier uploadé existe et se lit, mais `getRealPath()` échoue
 * (dossier temporaire de PHP illisible pour le compte du serveur web, ex. C:\Windows\Temp).
 */
function uploadWithoutRealPath(UploadedFile $source): UploadedFile
{
    // Copie dans un fichier propre : le temporaire de `fake()` est supprimé dès que `$source` est libéré.
    $path = tempnam(sys_get_temp_dir(), 'upl');
    copy($source->getPathname(), $path);
    register_shutdown_function(static fn () => @unlink($path));

    return new class($path, $source->getClientOriginalName(), $source->getMimeType(), null, true) extends UploadedFile
    {
        public function getRealPath(): string|false
        {
            return false;
        }
    };
}
