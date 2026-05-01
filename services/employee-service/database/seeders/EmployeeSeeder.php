<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $positions = Position::all();
        for ($i = 1; $i <= 15; $i++) {
            Employee::create([
                'user_id' => $i,
                'position_id' => $positions->random()->id,
                'full_name' => $faker->name,
                'email' => "employee{$i}@example.com",
                'phone_number' => $faker->phoneNumber,
                'hire_date' => $faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
            ]);
        }
    }
}
