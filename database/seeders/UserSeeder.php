<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $pw = Hash::make('password');

        // Ghost admin — invisible to everyone else
        User::updateOrCreate(
            ['email' => 'head@justicehub.org'],
            ['name' => 'System Admin', 'password' => $pw, 'role' => UserRole::Head, 'hub_id' => null, 'is_active' => true, 'is_ghost' => true]
        );

        $users = [
            // ── Head (Super Admin) ──
            ['name' => 'Ubaid Rasheed',          'email' => 'ubaid.rasheed@las.org.pk',       'role' => UserRole::Head,              'hub_id' => null],

            // ── Provincial Lead ──
            ['name' => 'Danish Ahmed Soomro',    'email' => 'danish.soomro@las.org.pk',       'role' => UserRole::ProvincialLead,    'hub_id' => null],

            // ── M&E Lead ──
            ['name' => 'Bilal Ahmed',            'email' => 'me@justicehub.org',              'role' => UserRole::MELead,            'hub_id' => null],

            // ── Hub Coordinators ──
            ['name' => 'Zahid Ali Messo',        'email' => 'zahid.ali.messo@las.org.pk',     'role' => UserRole::HubCoordinator,   'hub_id' => 'JH-HYD-01'],
            ['name' => 'Faisal Memon',           'email' => 'faisal.memon@las.org.pk',        'role' => UserRole::HubCoordinator,   'hub_id' => 'JH-DAD-01'],
            ['name' => 'Muhammad Azeem',         'email' => 'muhammad.azeem@las.org.pk',      'role' => UserRole::HubCoordinator,   'hub_id' => 'JH-SAN-01'],
            ['name' => 'Moazam Ali Jatoi',       'email' => 'moazam.ali@las.org.pk',          'role' => UserRole::HubCoordinator,   'hub_id' => 'JH-SBA-01'],
            ['name' => 'Furrah Kashif',          'email' => 'furrah.kashif@las.org.pk',        'role' => UserRole::HubCoordinator,   'hub_id' => 'JH-ISB-01'],

            // ── Operations Officers ──
            ['name' => 'Hallar Bhatti',          'email' => 'hallar.bhatti@las.org.pk',        'role' => UserRole::OperationsOfficer, 'hub_id' => 'JH-HYD-01'],
            ['name' => 'Sajjad Ali',             'email' => 'sajjad.ali@las.org.pk',           'role' => UserRole::OperationsOfficer, 'hub_id' => 'JH-DAD-01'],
            ['name' => 'Asif Mirani',            'email' => 'asif.mirani@las.org.pk',          'role' => UserRole::OperationsOfficer, 'hub_id' => 'JH-SAN-01'],
            ['name' => 'Ayaz Hussain Dahiri',    'email' => 'ayaz.hussain@las.org.pk',         'role' => UserRole::OperationsOfficer, 'hub_id' => 'JH-SBA-01'],
            ['name' => 'Naimat Hussain Khan',    'email' => 'naimat.hussain@las.org.pk',       'role' => UserRole::OperationsOfficer, 'hub_id' => 'JH-ISB-01'],

            // ── Lawyers ──
            ['name' => 'Daniyal Ali',            'email' => 'danial.ali@las.org.pk',           'role' => UserRole::Lawyer,           'hub_id' => 'JH-HYD-01'],
            ['name' => 'Babar Khan Chandio',     'email' => 'babar.chandio@las.org.pk',        'role' => UserRole::Lawyer,           'hub_id' => 'JH-SBA-01'],
            ['name' => 'Muhammad Tasleem',       'email' => 'muhammad.tasleem@las.org.pk',     'role' => UserRole::Lawyer,           'hub_id' => 'JH-DAD-01'],
            ['name' => 'Noman Sahotra',          'email' => 'noman.maqbool@las.org.pk',        'role' => UserRole::Lawyer,           'hub_id' => 'JH-SAN-01'],
            ['name' => 'Komal Amjad',            'email' => 'komal.amjad@las.org.pk',          'role' => UserRole::Lawyer,           'hub_id' => 'JH-ISB-01'],

            // ── Court Clerk ──
            ['name' => 'Saif Ali',               'email' => 'saif.ali@las.org.pk',             'role' => UserRole::CourtClerk,       'hub_id' => 'JH-HYD-01'],

            // ── Other existing roles ──
            ['name' => 'N. Memon',               'email' => 'entry@justicehub.org',             'role' => UserRole::DataEntry,         'hub_id' => 'JH-SAN-01'],
            ['name' => 'Sara Khan',              'email' => 'complaints@justicehub.org',         'role' => UserRole::ComplaintInvestigator, 'hub_id' => 'JH-SAN-01'],
            ['name' => 'Guest Viewer',           'email' => 'viewer@justicehub.org',             'role' => UserRole::Viewer,           'hub_id' => null],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'      => $data['name'],
                    'password'  => $pw,
                    'role'      => $data['role'],
                    'hub_id'    => $data['hub_id'],
                    'is_active' => true,
                ]
            );
            if (method_exists($user, 'assignRole')) {
                $user->assignRole($data['role']->value);
            }
        }

        $this->command->info('Seeded ' . count($users) . ' users.');
    }
}
