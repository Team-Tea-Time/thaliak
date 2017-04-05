<?php

namespace Thaliak\Console\Commands;

use Illuminate\Console\Command;
use Thaliak\Models\Role;
use Thaliak\Models\User;
use Thaliak\Support\User as UserSupport;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->promptName();
        $email = $this->promptEmail();
        $password = $this->promptPassword();

        $this->info('Assign a role? Available roles are');
        $this->line("0: None");

        $roles = Role::all()->keyBy('id');

        foreach ($roles as $role) {
            $this->line("{$role->id}: {$role->name}");
        }

        $roleId = $this->ask('Select option');

        $user = UserSupport::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'verified' => 1,
            'active' => 1
        ]);

        $this->info("User {$name} created!");

        if ($roleId) {
            $role = $roles->get($roleId);
            $user->roles()->save($role);
            $this->info("Role {$role->name} added to user!");
        }
    }

    protected function promptName(): String
    {
        $name = $this->ask('Username');
        $user = User::whereName($name)->first();

        if ($user) {
            $this->error('A user with that name already exists. Please choose a different one.');
            return $this->promptName();
        }

        return $name;
    }

    protected function promptEmail(): String
    {
        $email = $this->ask('Email address');
        $user = User::whereEmail($email)->first();

        if ($user) {
            $this->error('A user with that email address already exists. Please choose a different one.');
            return $this->promptEmail();
        }

        return $email;
    }

    protected function promptPassword(): String
    {
        $password = $this->secret('Password');
        $password_confirm = $this->secret('Password (confirm)');

        if ($password != $password_confirm) {
            $this->error("Password doesn't match. Please try again.");
            return $this->promptPassword();
        }

        return $password;
    }
}
