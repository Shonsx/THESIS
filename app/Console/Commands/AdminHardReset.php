<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AdminHardReset extends Command
{
    protected $signature = 'admin:hard-reset';
    protected $description = 'Reset admin to first-login state with default master password';

    public function handle()
    {
        /** @var \App\Models\User|null $admin */
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->error('No admin user found.');
            return self::FAILURE;
        }

        $admin->first_login = true;
        $admin->password = Hash::make('admiN123456789');
        $admin->tel = null;
        $admin->save();

        if ($admin->email) {
            DB::table('password_reset_tokens')->where('email', $admin->email)->delete();
        }

        $this->info('Admin has been reset to first-login. Use default credentials to sign in.');
        return self::SUCCESS;
    }
}