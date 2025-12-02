<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\RegisterMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class RegisterMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(private User $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user->email)->send(new RegisterMail($this->user->name));
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $exception->getMessage();
        Log::error($exception->getMessage());
    }
}
