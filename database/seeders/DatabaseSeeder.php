<?php

namespace Database\Seeders;

use App\Models\{Client, Shift, Target, Team, User, Worker, WorkAssignment};
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@qss.test'], [
            'name' => 'QSS Admin', 'password' => 'password', 'role' => 'admin',
        ]);

        $client = Client::firstOrCreate(['name' => 'Demo Client Ltd'], [
            'contact_person' => 'Jane Client', 'email' => 'client@qss.test', 'phone' => '+256700000000',
        ]);

        User::updateOrCreate(['email' => 'client@qss.test'], [
            'name' => 'Jane Client', 'password' => 'password', 'role' => 'client', 'client_id' => $client->id,
        ]);

        $sup = User::updateOrCreate(['email' => 'supervisor@qss.test'], [
            'name' => 'Sam Supervisor', 'password' => 'password', 'role' => 'supervisor',
        ]);

        $team = Team::firstOrCreate(['name' => 'Team Alpha'], ['client_id' => $client->id, 'supervisor_id' => $sup->id]);

        foreach (['Worker One', 'Worker Two', 'Worker Three'] as $i => $n) {
            Worker::firstOrCreate(['name' => $n], ['team_id' => $team->id, 'employee_number' => 'W'.($i + 1)]);
        }

        Shift::firstOrCreate(
            ['team_id' => $team->id, 'date' => today()->toDateString(), 'shift_type' => 'morning'],
            ['client_id' => $client->id, 'supervisor_id' => $sup->id, 'start_time' => '06:00', 'end_time' => '14:00', 'workers_deployed' => 3]
        );

        $a = WorkAssignment::firstOrCreate(['title' => 'Carton packing'], [
            'client_id' => $client->id, 'team_id' => $team->id, 'activity' => 'Packing', 'unit' => 'cartons',
            'expected_output' => 5000, 'start_date' => today()->toDateString(), 'status' => 'pending',
        ]);

        Target::firstOrCreate(['work_assignment_id' => $a->id, 'date' => today()->toDateString(), 'shift_type' => 'morning'], [
            'client_id' => $client->id, 'quantity' => 500,
        ]);
    }
}
