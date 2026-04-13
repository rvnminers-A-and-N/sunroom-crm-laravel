<?php

namespace Database\Seeders;

use App\Enums\ActivityType;
use App\Enums\DealStage;
use App\Enums\UserRole;
use App\Models\Activity;
use App\Models\AiInsight;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        $admin = User::create([
            'name' => 'Austin Sunroom',
            'email' => 'admin@sunroomcrm.com',
            'password' => 'password123',
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);

        $manager = User::create([
            'name' => 'Sarah Manager',
            'email' => 'sarah@sunroomcrm.com',
            'password' => 'password123',
            'role' => UserRole::Manager,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'Jake Sales',
            'email' => 'jake@sunroomcrm.com',
            'password' => 'password123',
            'role' => UserRole::User,
            'email_verified_at' => now(),
        ]);

        // Tags
        $tags = collect([
            ['name' => 'VIP', 'color' => '#F76C6C'],
            ['name' => 'Hot Lead', 'color' => '#F9A66C'],
            ['name' => 'Decision Maker', 'color' => '#F4C95D'],
            ['name' => 'Referral', 'color' => '#02795F'],
            ['name' => 'Follow Up', 'color' => '#3B82F6'],
            ['name' => 'Cold', 'color' => '#6B7280'],
        ])->map(fn ($t) => Tag::create($t));

        // Companies
        $companies = collect([
            ['user_id' => $admin->id, 'name' => 'Acme Corporation', 'industry' => 'Technology', 'website' => 'https://acme.example.com', 'phone' => '(555) 100-1000', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78701', 'address' => '100 Congress Ave'],
            ['user_id' => $admin->id, 'name' => 'Global Dynamics', 'industry' => 'Consulting', 'website' => 'https://globaldyn.example.com', 'phone' => '(555) 200-2000', 'city' => 'Dallas', 'state' => 'TX', 'zip' => '75201', 'address' => '200 Main St'],
            ['user_id' => $admin->id, 'name' => 'Initech Solutions', 'industry' => 'Software', 'website' => 'https://initech.example.com', 'phone' => '(555) 300-3000', 'city' => 'San Antonio', 'state' => 'TX', 'zip' => '78205', 'address' => '300 River Walk'],
            ['user_id' => $admin->id, 'name' => 'Stark Industries', 'industry' => 'Manufacturing', 'website' => 'https://stark.example.com', 'phone' => '(555) 400-4000', 'city' => 'Houston', 'state' => 'TX', 'zip' => '77002', 'address' => '400 Market Square'],
            ['user_id' => $manager->id, 'name' => 'Wayne Enterprises', 'industry' => 'Finance', 'website' => 'https://wayne.example.com', 'phone' => '(555) 500-5000', 'city' => 'Fort Worth', 'state' => 'TX', 'zip' => '76102', 'address' => '500 Sundance Square'],
        ])->map(fn ($c) => Company::create($c));

        // Contacts
        $contacts = collect([
            ['user_id' => $admin->id, 'company_id' => $companies[0]->id, 'first_name' => 'John', 'last_name' => 'Smith', 'email' => 'john@acme.example.com', 'phone' => '(555) 101-0001', 'title' => 'CTO', 'notes' => 'Key technical decision maker'],
            ['user_id' => $admin->id, 'company_id' => $companies[0]->id, 'first_name' => 'Emily', 'last_name' => 'Chen', 'email' => 'emily@acme.example.com', 'phone' => '(555) 101-0002', 'title' => 'VP Engineering'],
            ['user_id' => $admin->id, 'company_id' => $companies[1]->id, 'first_name' => 'Michael', 'last_name' => 'Johnson', 'email' => 'michael@globaldyn.example.com', 'phone' => '(555) 201-0001', 'title' => 'Managing Partner', 'notes' => 'Met at conference'],
            ['user_id' => $admin->id, 'company_id' => $companies[2]->id, 'first_name' => 'Sarah', 'last_name' => 'Williams', 'email' => 'sarah@initech.example.com', 'phone' => '(555) 301-0001', 'title' => 'Head of Product'],
            ['user_id' => $admin->id, 'company_id' => $companies[3]->id, 'first_name' => 'David', 'last_name' => 'Brown', 'email' => 'david@stark.example.com', 'phone' => '(555) 401-0001', 'title' => 'Procurement Director'],
            ['user_id' => $admin->id, 'first_name' => 'Lisa', 'last_name' => 'Davis', 'email' => 'lisa@freelance.example.com', 'phone' => '(555) 601-0001', 'title' => 'Independent Consultant', 'notes' => 'No company affiliation'],
            ['user_id' => $manager->id, 'company_id' => $companies[4]->id, 'first_name' => 'Robert', 'last_name' => 'Wilson', 'email' => 'robert@wayne.example.com', 'phone' => '(555) 501-0001', 'title' => 'CFO'],
            ['user_id' => $manager->id, 'company_id' => $companies[4]->id, 'first_name' => 'Jennifer', 'last_name' => 'Taylor', 'email' => 'jennifer@wayne.example.com', 'phone' => '(555) 501-0002', 'title' => 'VP Operations'],
        ])->map(fn ($c) => Contact::create($c));

        // Tag assignments (matching .NET SeedData exactly)
        $contacts[0]->tags()->attach([$tags[0]->id, $tags[2]->id]); // John: VIP, Decision Maker
        $contacts[1]->tags()->attach([$tags[1]->id]);                // Emily: Hot Lead
        $contacts[2]->tags()->attach([$tags[3]->id]);                // Michael: Referral
        $contacts[3]->tags()->attach([$tags[1]->id, $tags[4]->id]); // Sarah: Hot Lead, Follow Up
        $contacts[4]->tags()->attach([$tags[2]->id]);                // David: Decision Maker
        $contacts[5]->tags()->attach([$tags[5]->id]);                // Lisa: Cold
        $contacts[6]->tags()->attach([$tags[0]->id]);                // Robert: VIP
        $contacts[7]->tags()->attach([$tags[4]->id]);                // Jennifer: Follow Up

        // Deals
        $deals = collect([
            ['user_id' => $admin->id, 'contact_id' => $contacts[0]->id, 'company_id' => $companies[0]->id, 'title' => 'Acme Platform License', 'value' => 85000, 'stage' => DealStage::Negotiation, 'expected_close_date' => now()->addDays(30), 'notes' => 'Enterprise license deal, 3-year contract'],
            ['user_id' => $admin->id, 'contact_id' => $contacts[1]->id, 'company_id' => $companies[0]->id, 'title' => 'Acme Support Package', 'value' => 24000, 'stage' => DealStage::Proposal, 'expected_close_date' => now()->addDays(45)],
            ['user_id' => $admin->id, 'contact_id' => $contacts[2]->id, 'company_id' => $companies[1]->id, 'title' => 'Global Dynamics Consulting', 'value' => 120000, 'stage' => DealStage::Qualified, 'expected_close_date' => now()->addDays(60)],
            ['user_id' => $admin->id, 'contact_id' => $contacts[3]->id, 'company_id' => $companies[2]->id, 'title' => 'Initech Integration Project', 'value' => 45000, 'stage' => DealStage::Lead, 'expected_close_date' => now()->addDays(90)],
            ['user_id' => $admin->id, 'contact_id' => $contacts[4]->id, 'company_id' => $companies[3]->id, 'title' => 'Stark Manufacturing Suite', 'value' => 250000, 'stage' => DealStage::Won, 'closed_at' => now()->subDays(15), 'notes' => 'Largest deal this quarter!'],
            ['user_id' => $admin->id, 'contact_id' => $contacts[5]->id, 'title' => 'Freelance Advisory', 'value' => 15000, 'stage' => DealStage::Lost, 'closed_at' => now()->subDays(7), 'notes' => 'Lost to competitor pricing'],
            ['user_id' => $manager->id, 'contact_id' => $contacts[6]->id, 'company_id' => $companies[4]->id, 'title' => 'Wayne Financial Suite', 'value' => 175000, 'stage' => DealStage::Proposal, 'expected_close_date' => now()->addDays(30)],
        ])->map(fn ($d) => Deal::create($d));

        // Activities
        $activities = [
            ['user_id' => $admin->id, 'contact_id' => $contacts[0]->id, 'deal_id' => $deals[0]->id, 'type' => ActivityType::Meeting, 'subject' => 'Initial Discovery Meeting', 'body' => 'Discussed pain points with current platform. They need better reporting and API integration. Budget approved for Q2.', 'occurred_at' => now()->subDays(14)],
            ['user_id' => $admin->id, 'contact_id' => $contacts[0]->id, 'deal_id' => $deals[0]->id, 'type' => ActivityType::Call, 'subject' => 'Follow-up Call', 'body' => 'Reviewed proposal draft. John requested additional security compliance documentation.', 'occurred_at' => now()->subDays(7)],
            ['user_id' => $admin->id, 'contact_id' => $contacts[0]->id, 'deal_id' => $deals[0]->id, 'type' => ActivityType::Email, 'subject' => 'Sent Security Compliance Docs', 'body' => 'Emailed SOC2 and GDPR compliance documentation as requested.', 'occurred_at' => now()->subDays(5)],
            ['user_id' => $admin->id, 'contact_id' => $contacts[1]->id, 'deal_id' => $deals[1]->id, 'type' => ActivityType::Meeting, 'subject' => 'Support Package Demo', 'body' => 'Walked through premium support SLA and response time guarantees.', 'occurred_at' => now()->subDays(3)],
            ['user_id' => $admin->id, 'contact_id' => $contacts[2]->id, 'deal_id' => $deals[2]->id, 'type' => ActivityType::Call, 'subject' => 'Qualification Call', 'body' => 'Michael confirmed budget range and decision timeline. Need to schedule technical deep-dive.', 'occurred_at' => now()->subDays(10)],
            ['user_id' => $admin->id, 'contact_id' => $contacts[3]->id, 'type' => ActivityType::Note, 'subject' => 'Research Notes', 'body' => 'Initech is evaluating three vendors. Our differentiator is the integration API.', 'occurred_at' => now()->subDays(8)],
            ['user_id' => $admin->id, 'contact_id' => $contacts[4]->id, 'deal_id' => $deals[4]->id, 'type' => ActivityType::Meeting, 'subject' => 'Contract Signing', 'body' => 'Finalized contract terms. 3-year deal signed. Implementation starts next month.', 'occurred_at' => now()->subDays(15)],
            ['user_id' => $admin->id, 'type' => ActivityType::Task, 'subject' => 'Prepare Q2 Pipeline Report', 'body' => 'Compile pipeline metrics and forecast for leadership review.', 'occurred_at' => now()->subDays(1)],
            ['user_id' => $manager->id, 'contact_id' => $contacts[6]->id, 'deal_id' => $deals[6]->id, 'type' => ActivityType::Meeting, 'subject' => 'Financial Suite Requirements', 'body' => 'Robert outlined the requirements for their financial reporting modernization project.', 'occurred_at' => now()->subDays(5)],
            ['user_id' => $manager->id, 'contact_id' => $contacts[7]->id, 'type' => ActivityType::Call, 'subject' => 'Operations Check-in', 'body' => 'Jennifer is interested in the operations module as an add-on.', 'occurred_at' => now()->subDays(2)],
        ];
        foreach ($activities as $a) {
            Activity::create($a);
        }

        // AI Insights
        AiInsight::create([
            'deal_id' => $deals[0]->id,
            'insight' => 'This deal is in the negotiation phase with strong buying signals. Recommend: 1) Send revised pricing with volume discount, 2) Schedule executive-level meeting, 3) Prepare implementation timeline.',
            'generated_at' => now()->subDays(2),
        ]);
        AiInsight::create([
            'deal_id' => $deals[6]->id,
            'insight' => 'Wayne Enterprises has a large budget and clear requirements. Recommend: 1) Fast-track the proposal, 2) Offer pilot program, 3) Arrange reference call with similar financial institution.',
            'generated_at' => now()->subDays(1),
        ]);
    }
}
