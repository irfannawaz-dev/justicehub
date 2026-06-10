<?php

/**
 * Justice Hub CRM — Application Settings
 *
 * Centralized configuration for business rules, SLA thresholds,
 * ID formats, and operational defaults.
 */

return [

    // ─────────────────────────────────────────────────────────────────────
    // ID PREFIXES & FORMATS
    // ─────────────────────────────────────────────────────────────────────

    'prefixes' => [
        'case'             => 'CL',    // Client case ID:       CL-02471
        'case_ref'         => 'CA',    // Case reference:       CA-02471
        'encounter'        => 'SE',    // Service encounter:    SE-09841
        'feedback'         => 'FB',    // Feedback:             FB-016
        'complaint'        => 'CMP',   // Complaint:            CMP-021
        'staff'            => 'STF',   // Staff:                STF-001
        'evidence'         => 'EV',    // Evidence:             EV-001
        'outreach'         => 'OR',    // Outreach activity:    OR-0701
        'document'         => 'DOC',   // Document:             DOC-0241
        'partner'          => 'P',     // Partner:              P-001
        'pulse_survey'     => 'PS',    // Pulse survey:         PS-001
    ],

    // Zero-padded width for numeric part of IDs (e.g., 5 → CL-02471)
    'id_pad_width' => 5,

    // ─────────────────────────────────────────────────────────────────────
    // SLA THRESHOLDS — Case urgency
    // ─────────────────────────────────────────────────────────────────────
    // Maximum hours from intake to first substantive service encounter
    // before the case is flagged as SLA-breached.

    'sla' => [
        'urgency_hours' => [
            'Critical'  => 4,
            'High'      => 24,
            'Medium'    => 72,
            'Low'       => 168,   // 7 days
        ],
    ],

    // ─────────────────────────────────────────────────────────────────────
    // COMPLAINT SLA — Days by severity
    // ─────────────────────────────────────────────────────────────────────
    // Maximum business days from submission to resolution.

    'complaint_sla_days' => [
        'critical' => 3,
        'high'     => 7,
        'medium'   => 14,
        'low'      => 30,
    ],

    // ─────────────────────────────────────────────────────────────────────
    // TRAINING EXPIRY DEFAULTS
    // ─────────────────────────────────────────────────────────────────────
    // Default validity period in months when expiry is not explicitly set.

    'training_expiry_months' => [
        'annual'   => 12,
        'biennial' => 24,
        'one-off'  => null,   // No expiry
    ],

    // ─────────────────────────────────────────────────────────────────────
    // DEFAULT HUB
    // ─────────────────────────────────────────────────────────────────────

    'default_hub' => 'all',   // 'all' = no hub filter; or a hub ID like 'JH-SAN-01'

    // ─────────────────────────────────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────────────────────────────────

    'dashboard' => [
        'metrics_cache_ttl'    => 300,    // seconds (5 minutes)
        'indicator_cache_ttl'  => 3600,   // seconds (1 hour)
        'lookups_cache_ttl'    => 86400,  // seconds (24 hours)
    ],

    // ─────────────────────────────────────────────────────────────────────
    // FINANCE / VALUE FOR MONEY DEFAULTS
    // ─────────────────────────────────────────────────────────────────────

    'finance' => [
        'default_cost_per_case'        => 8500,     // PKR
        'default_annual_operational'   => 45000000, // PKR (45M)
        'currency'                     => 'PKR',
    ],

    // ─────────────────────────────────────────────────────────────────────
    // INDICATOR RAG THRESHOLDS
    // ─────────────────────────────────────────────────────────────────────
    // Percentage of target achieved to determine Red/Amber/Green status.

    'rag_thresholds' => [
        'green' => 90,   // >= 90% of target
        'amber' => 70,   // >= 70% of target
        // < 70% = red
    ],

    // ─────────────────────────────────────────────────────────────────────
    // PAGINATION
    // ─────────────────────────────────────────────────────────────────────

    'per_page' => [
        'cases'       => 25,
        'complaints'  => 20,
        'feedback'    => 20,
        'evidence'    => 20,
        'staff'       => 20,
        'outreach'    => 15,
        'documents'   => 20,
    ],

    // ─────────────────────────────────────────────────────────────────────
    // CONTACT INFO (displayed in intake form conclusion)
    // ─────────────────────────────────────────────────────────────────────

    'contact' => [
        'organization' => 'Legal Aid Society',
        'phone'        => '0345-8270806',
    ],

];
