<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // استدعاء Seeder بتاع الأدوار
        $this->call(RoleSeeder::class);

        // إنشاء أو تحديث مستخدم Admin
        User::updateOrCreate(
            ['email' => 'test@example.com'], // لو موجود نفس الإيميل مش هيكرره
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'role_id' => 1,
            ]
        );
    }
}
