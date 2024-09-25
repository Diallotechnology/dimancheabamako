<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteUserNotVerify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-user-not-verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'user email not verified';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        User::whereNull('email_verified_at')
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    DB::beginTransaction();

                    try {
                        // Parse the createDateTime in the correct format (adjust if necessary)
                        $createDateTime = Carbon::createFromFormat('d/m/Y', $user->created_at);

                        // Get the current time
                        $currentTime = Carbon::now();

                        // Calculate the time difference in hours
                        $hoursDifference = $currentTime->diffInHours($createDateTime);

                        if ($hoursDifference >= 1) {
                            $user->delete();
                        }

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Failed to delete user', ['user' => $user->email, 'error' => $e->getMessage()]);
                    }
                }
            });
    }
}
