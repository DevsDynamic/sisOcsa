<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetSystemOwner extends Command
{
    protected $signature = 'system:set-owner {username : Correo o nombre de usuario existente}';

    protected $description = 'Designa al unico usuario propietario del sistema';

    public function handle(): int
    {
        $user = User::where('username', $this->argument('username'))->first();

        if (!$user) {
            $this->error('No existe un usuario con ese nombre.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($user) {
            User::where('is_system_owner', true)->update(['is_system_owner' => false]);
            $user->forceFill([
                'is_system_owner' => true,
                'access' => true,
                'status' => true,
            ])->save();
        });

        $this->info("{$user->username} es ahora el propietario del sistema.");

        return self::SUCCESS;
    }
}
