<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        //create User Admin
        // $admin = User::factory()->create([
        //     'name' => 'Admin',
        //     'email' => 'admin@test.com',
        //     'username' => 'admin',

        // ]);

        // $admin->createPrincipal();

        // $admin->assignJustRole('Admin');

        $admin = User::factory()->create([
            'name' => 'Christophe Leininger',
            'email' => 'cleininger@b2pweb.com',
            'username' => 'cleininger',

        ]);

        $admin->createPrincipal();

        $admin->assignJustRole('Admin');

        //Moderateurs et 1 teams par moderateur
        $moderateurs = User::factory(3)->create();
        foreach ($moderateurs as $moderateur) {
            $moderateur->createPrincipal();

            $moderateur->createTeamPrincipal('team '.$moderateur->username);

        }

        $teams = Team::all();
        foreach ($teams as $team) {
            User::factory(3)->create()->each(function ($user) use ($team) {
                $user->createPrincipal();
                $user->joinTeam('Utilisateur', $team->id);
            });
        }

        $this->call(EventSeeder::class);
    }
}
