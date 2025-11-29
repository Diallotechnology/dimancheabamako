<?php

namespace App\Providers;

use App\Service\CategoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (session('devise') === 'EUR') {
            if (session()->missing('taux_eur')) {
                $taux = Cache::rememberForever('taux_eur', function () {
                    return DB::table('devises')->where('type', 'EUR')->value('taux');
                });

                session(['taux_eur' => $taux]);
            }
        }

        View::share('categories', app(CategoryService::class)->all());

        // VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
        //     return (new MailMessage)
        //         ->subject(__('messages.verify_email_address'))
        //         ->line(__('messages.click_to_verify_email'))
        //         ->action(__('messages.verify_email_address'), $url);
        // });
    }
}
