<?php

namespace Database\Seeders;

use App\Enums\DealStage;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Deterministic seeder that prepares the database for the Playwright golden-path
 * smoke suite. Lives in test infrastructure (not app behaviour): the credentials
 * here are well-known and only valid against the throwaway sunroom_crm_test DB.
 *
 * Run with `php artisan db:seed --class=PlaywrightSeeder` after a fresh migrate.
 */
class PlaywrightSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'playwright-admin@sunroomcrm.test';

    public const USER_EMAIL = 'playwright-user@sunroomcrm.test';

    public const PASSWORD = 'playwright-password';

    public function run(): void
    {
        $admin = User::create([
            'name' => 'Playwright Admin',
            'email' => self::ADMIN_EMAIL,
            'password' => self::PASSWORD,
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'Playwright User',
            'email' => self::USER_EMAIL,
            'password' => self::PASSWORD,
            'role' => UserRole::User,
            'email_verified_at' => now(),
        ]);

        Tag::create(['name' => 'Playwright Tag', 'color' => '#02795F']);

        $company = Company::create([
            'user_id' => $user->id,
            'name' => 'Playwright Test Co',
            'industry' => 'Quality Assurance',
            'website' => 'https://playwright.test',
            'phone' => '(555) 010-1010',
            'city' => 'Austin',
            'state' => 'TX',
            'zip' => '78701',
            'address' => '1 Test Way',
        ]);

        $contact = Contact::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'first_name' => 'Riley',
            'last_name' => 'Tester',
            'email' => 'riley@playwright.test',
            'phone' => '(555) 010-1011',
            'title' => 'Director of Testing',
        ]);

        Deal::create([
            'user_id' => $user->id,
            'contact_id' => $contact->id,
            'company_id' => $company->id,
            'title' => 'Seeded Pipeline Deal',
            'value' => 12500,
            'stage' => DealStage::Lead,
            'expected_close_date' => now()->addDays(30),
        ]);
    }
}
