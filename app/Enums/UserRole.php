<?php

namespace App\Enums;

enum UserRole: string
{
    case Head = 'head';
    case ProvincialLead = 'provincial-lead';
    case HubCoordinator = 'hub-coordinator';
    case Lawyer = 'lawyer';
    case CourtClerk = 'court-clerk';
    case OperationsOfficer = 'operations-officer';
    case DataEntry = 'data-entry';
    case MELead = 'me-lead';
    case ComplaintInvestigator = 'complaint-investigator';
    case LitigationManager = 'litigation-manager';
    case MediationManager = 'mediation-manager';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Head                 => 'Head (Super Admin)',
            self::ProvincialLead       => 'Provincial Lead',
            self::HubCoordinator       => 'Hub Coordinator',
            self::Lawyer               => 'Lawyer',
            self::CourtClerk           => 'Court Clerk',
            self::OperationsOfficer    => 'Operations Officer',
            self::DataEntry            => 'Data Entry',
            self::MELead               => 'M&E Lead',
            self::ComplaintInvestigator => 'Complaint Investigator',
            self::LitigationManager    => 'Litigation Manager',
            self::MediationManager     => 'Mediation Manager',
            self::Viewer               => 'Viewer (Read Only)',
        };
    }

    public function isGlobalScope(): ?bool
    {
        return match ($this) {
            self::Head, self::ProvincialLead, self::MELead,
            self::LitigationManager, self::MediationManager => true,
            self::Viewer => null,
            default => false,
        };
    }

    public function canWrite(): bool
    {
        return match ($this) {
            self::Viewer => false,
            default      => true,
        };
    }

    public function permissions(): array
    {
        return match ($this) {
            self::Head => [
                'cases.view', 'cases.create', 'cases.edit', 'cases.delete', 'cases.approve',
                'complaints.view', 'complaints.create', 'complaints.resolve', 'complaints.escalate',
                'feedback.view', 'feedback.create',
                'outreach.view', 'outreach.create',
                'indicators.view', 'indicators.edit',
                'evidence.view', 'evidence.create', 'evidence.verify',
                'staff.view', 'staff.edit', 'staff.training.log',
                'settings.view', 'settings.edit',
                'reports.view', 'reports.export',
                'lookups.manage',
                'users.manage',
            ],
            self::ProvincialLead => [
                'cases.view', 'cases.create', 'cases.edit', 'cases.delete', 'cases.approve',
                'complaints.view', 'complaints.create', 'complaints.resolve', 'complaints.escalate',
                'feedback.view', 'feedback.create',
                'outreach.view', 'outreach.create',
                'indicators.view', 'indicators.edit',
                'evidence.view', 'evidence.create', 'evidence.verify',
                'staff.view', 'staff.edit', 'staff.training.log',
                'settings.view', 'settings.edit',
                'reports.view', 'reports.export',
                'lookups.manage',
                'users.manage',
            ],
            self::HubCoordinator => [
                'cases.view', 'cases.create', 'cases.edit', 'cases.approve',
                'complaints.view', 'complaints.create', 'complaints.resolve',
                'feedback.view', 'feedback.create',
                'outreach.view', 'outreach.create',
                'indicators.view',
                'evidence.view', 'evidence.create',
                'staff.view', 'staff.edit', 'staff.training.log',
                'settings.view', 'settings.edit',
                'reports.view', 'reports.export',
            ],
            self::Lawyer => [
                'cases.view', 'cases.create', 'cases.edit',
                'complaints.view',
                'feedback.view', 'feedback.create',
                'staff.view',
            ],
            self::CourtClerk => [
                'cases.view',
                'complaints.view',
                'staff.view',
            ],
            self::OperationsOfficer => [
                'cases.view', 'cases.create',
                'complaints.view', 'complaints.create',
                'feedback.view', 'feedback.create',
                'outreach.view', 'outreach.create',
                'indicators.view',
                'staff.view',
                'reports.view',
            ],
            self::DataEntry => [
                'cases.view', 'cases.create', 'cases.edit',
                'complaints.view', 'complaints.create',
                'feedback.view', 'feedback.create',
                'outreach.view', 'outreach.create',
            ],
            self::MELead => [
                'cases.view',
                'complaints.view',
                'feedback.view', 'feedback.create',
                'outreach.view', 'outreach.create',
                'indicators.view', 'indicators.edit',
                'evidence.view', 'evidence.create', 'evidence.verify',
                'staff.view',
                'reports.view', 'reports.export',
            ],
            self::ComplaintInvestigator => [
                'cases.view',
                'complaints.view', 'complaints.create', 'complaints.resolve', 'complaints.escalate',
                'feedback.view',
                'outreach.view',
                'indicators.view',
                'staff.view',
                'reports.view',
            ],
            self::LitigationManager => [
                'cases.view', 'cases.edit',
                'complaints.view',
                'feedback.view',
                'staff.view',
                'reports.view',
            ],
            self::MediationManager => [
                'cases.view', 'cases.edit',
                'complaints.view',
                'feedback.view',
                'staff.view',
                'reports.view',
            ],
            self::Viewer => [
                'cases.view',
                'complaints.view',
                'feedback.view',
                'outreach.view',
                'indicators.view',
                'evidence.view',
                'staff.view',
                'reports.view',
            ],
        };
    }
}
