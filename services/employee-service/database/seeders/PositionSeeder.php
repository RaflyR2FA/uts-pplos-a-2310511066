<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['name' => 'Human Resources Manager', 'description' => 'Oversees all HR functions and employee relations.'],
            ['name' => 'Software Engineer', 'description' => 'Develops and maintains internal applications.'],
            ['name' => 'System Administrator', 'description' => 'Manages IT infrastructure and security.'],
            ['name' => 'Marketing Specialist', 'description' => 'Handles company branding and campaigns.'],
        ];
        foreach ($positions as $position) {
            Position::create($position);
        }
    }
}
