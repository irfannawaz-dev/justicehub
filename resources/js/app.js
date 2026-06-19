import './bootstrap';        // Laravel's axios bootstrap
import * as bootstrap from 'bootstrap';  // Bootstrap 5 JS (dropdowns, modals, tooltips, etc.)
window.bootstrap = bootstrap;

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import Chart from 'chart.js/auto';
window.Chart = Chart;

// ─────────────────────────────────────────────────────────────
// Chart.js global defaults — match Justice Hub design system
// ─────────────────────────────────────────────────────────────
Chart.defaults.font.family = "'Instrument Sans', 'Helvetica Neue', Arial, sans-serif";
Chart.defaults.font.size = 11;
Chart.defaults.color = '#6b6a65';
Chart.defaults.plugins.legend.display = false;
Chart.defaults.plugins.tooltip.backgroundColor = '#181714';
Chart.defaults.plugins.tooltip.titleFont = { family: "'Instrument Sans'", size: 12, weight: 600 };
Chart.defaults.plugins.tooltip.bodyFont = { family: "'Instrument Sans'", size: 11 };
Chart.defaults.plugins.tooltip.padding = 10;
Chart.defaults.plugins.tooltip.cornerRadius = 2;
Chart.defaults.plugins.tooltip.displayColors = true;
Chart.defaults.plugins.tooltip.boxWidth = 8;
Chart.defaults.plugins.tooltip.boxHeight = 8;
Chart.defaults.elements.line.tension = 0.3;
Chart.defaults.elements.line.borderWidth = 2;
Chart.defaults.elements.point.radius = 0;
Chart.defaults.elements.point.hoverRadius = 4;
Chart.defaults.scale.grid.color = '#ebe4d2';

// ─────────────────────────────────────────────────────────────
// Chart initializers — rich animations, gradients, legends
// ─────────────────────────────────────────────────────────────

function createGradient(ctx, color, height) {
    const grad = ctx.createLinearGradient(0, 0, 0, height || 300);
    grad.addColorStop(0, color + '40');
    grad.addColorStop(1, color + '05');
    return grad;
}

function initKpiSparkline(canvas, data) {
    const ctx = canvas.getContext('2d');
    const color = data.color || '#163029';
    const datasets = [];

    // Support multiple datasets
    if (data.datasets) {
        data.datasets.forEach((ds, i) => {
            datasets.push({
                label: ds.label || 'Dataset ' + (i + 1),
                data: ds.data || [],
                borderColor: ds.color || color,
                backgroundColor: createGradient(ctx, ds.color || color, 250),
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointBackgroundColor: ds.color || color,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                borderWidth: 2.5,
                tension: 0.4,
            });
        });
    } else {
        datasets.push({
            label: data.label || 'Cases',
            data: data.values || [],
            borderColor: color,
            backgroundColor: createGradient(ctx, color, 250),
            fill: true,
            pointRadius: 3,
            pointHoverRadius: 6,
            pointBackgroundColor: color,
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            borderWidth: 2.5,
            tension: 0.4,
        });
    }

    return new Chart(canvas, {
        type: 'line',
        data: { labels: data.labels || [], datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 1200, easing: 'easeOutQuart' },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: datasets.length > 1,
                    position: 'top',
                    align: 'end',
                    labels: { padding: 16, usePointStyle: true, pointStyle: 'circle', font: { size: 11 } },
                },
                tooltip: {
                    enabled: true,
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: ctx => ' ' + ctx.dataset.label + ': ' + ctx.parsed.y,
                    },
                },
            },
            scales: {
                x: {
                    display: true,
                    grid: { display: false },
                    ticks: { font: { size: 10 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 8 },
                },
                y: {
                    display: true,
                    beginAtZero: true,
                    grid: { color: '#ebe4d220' },
                    ticks: { font: { size: 10 }, maxTicksLimit: 5 },
                },
            },
        }
    });
}

function initServiceMixPie(canvas, data) {
    return new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: data.labels || [],
            datasets: [{
                data: data.values || [],
                backgroundColor: data.colors || ['#163029','#4a7a5c','#b87319','#8a2e1d','#6b6a65','#d9a05b'],
                borderWidth: 2,
                borderColor: '#fdfcf7',
                hoverBorderColor: '#fff',
                hoverBorderWidth: 3,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 1000,
                easing: 'easeOutBack',
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        padding: 14,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: { size: 10.5, weight: '500' },
                        generateLabels: function(chart) {
                            const d = chart.data;
                            const total = d.datasets[0].data.reduce((a, b) => a + b, 0);
                            return d.labels.map((label, i) => {
                                const val = d.datasets[0].data[i];
                                const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                return {
                                    text: label + ' (' + pct + '%)',
                                    fillStyle: d.datasets[0].backgroundColor[i],
                                    strokeStyle: 'transparent',
                                    lineWidth: 0,
                                    index: i,
                                    pointStyle: 'circle',
                                };
                            });
                        },
                    },
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = total > 0 ? Math.round((ctx.raw / total) * 100) : 0;
                            return ' ' + ctx.label + ': ' + ctx.raw + ' (' + pct + '%)';
                        },
                    },
                },
            },
        }
    });
}

function initResolutionBar(canvas, data) {
    var values = data.values || [];
    var labels = data.labels || [];
    var palette = ['#163029','#4a7a5c','#b87319','#8a2e1d','#7e57c2','#d9a05b','#6b6a65','#3e6b53'];
    var bgColors = data.colors || (data.color
        ? values.map(function() { return data.color; })
        : values.map(function(_, i) { return palette[i % palette.length]; }));

    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Count',
                data: values,
                backgroundColor: bgColors,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: function(ctx) { return ' ' + ctx.raw + ' cases'; } } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { beginAtZero: true, ticks: { font: { size: 10 }, maxTicksLimit: 5 } },
            },
        }
    });
}

function initDemographicsBar(canvas, data) {
    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels: data.labels || [],
            datasets: (data.datasets || []).map(ds => ({
                label: ds.label,
                data: ds.data,
                backgroundColor: ds.color,
                borderRadius: 4,
                maxBarThickness: 28,
                hoverBackgroundColor: ds.color + 'cc',
            }))
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 800, easing: 'easeOutQuart' },
            plugins: {
                legend: { display: true, position: 'bottom', labels: { padding: 14, usePointStyle: true, pointStyle: 'circle', font: { size: 10.5 } } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { beginAtZero: true, grid: { color: '#ebe4d240' }, ticks: { font: { size: 10 } } },
            },
        }
    });
}

function initIndicatorTrend(canvas, data) {
    const ctx = canvas.getContext('2d');
    return new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [
                {
                    label: 'Actual',
                    data: data.actuals || [],
                    borderColor: '#163029',
                    backgroundColor: createGradient(ctx, '#163029', 250),
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#163029',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    borderWidth: 2.5,
                    tension: 0.4,
                },
                ...(data.targets ? [{
                    label: 'Target',
                    data: data.targets,
                    borderColor: '#b87319',
                    borderDash: [6, 4],
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    fill: false,
                    tension: 0.3,
                }] : [])
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 1200, easing: 'easeOutQuart' },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: true, position: 'top', align: 'end', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle', font: { size: 11 } } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { beginAtZero: true, grid: { color: '#ebe4d240' }, ticks: { font: { size: 10 } } },
            },
        }
    });
}

function initRadialGauge(canvas, data) {
    const value = data.value || 0, max = data.max || 100;
    const color = data.color || '#4a7a5c';
    return new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: [data.label || 'Score', 'Remaining'],
            datasets: [{
                data: [value, max - value],
                backgroundColor: [color, '#ebe4d2'],
                borderWidth: 0,
                hoverOffset: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '78%',
            rotation: -90,
            circumference: 180,
            animation: { animateRotate: true, duration: 1200, easing: 'easeOutBack' },
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
        }
    });
}

const chartInitializers = {
    kpiSparkline:    initKpiSparkline,
    serviceMixPie:   initServiceMixPie,
    resolutionBar:   initResolutionBar,
    demographicsBar: initDemographicsBar,
    indicatorTrend:  initIndicatorTrend,
    radialGauge:     initRadialGauge,
};

// ─────────────────────────────────────────────────────────────
// Bootstrap Modal helpers (global)
// ─────────────────────────────────────────────────────────────
window.jhOpenModal = function (name) {
    const el = document.getElementById('modal-' + name);
    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
};
window.jhCloseModal = function (name) {
    const el = document.getElementById('modal-' + name);
    if (el) { const m = bootstrap.Modal.getInstance(el); if (m) m.hide(); }
};

// ─────────────────────────────────────────────────────────────
// Password visibility toggle
// ─────────────────────────────────────────────────────────────
window.jhTogglePassword = function (btn) {
    const wrapper = btn.closest('[data-pw-wrapper]');
    const input   = wrapper ? wrapper.querySelector('input') : btn.previousElementSibling;
    if (!input) return;
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    const eye    = btn.querySelector('.icon-eye');
    const eyeOff = btn.querySelector('.icon-eye-off');
    if (eye)    eye.style.display    = isText ? '' : 'none';
    if (eyeOff) eyeOff.style.display = isText ? 'none' : '';
    btn.title = isText ? 'Show password' : 'Hide password';
};

// ─────────────────────────────────────────────────────────────
// View-mode toggle (cases/index)
// ─────────────────────────────────────────────────────────────
window.jhSetViewMode = function (mode) {
    const listEl = document.getElementById('jh-view-list');
    const gridEl = document.getElementById('jh-view-grid');
    const listBtn = document.getElementById('jh-btn-list');
    const gridBtn = document.getElementById('jh-btn-grid');
    if (listEl) listEl.style.display = mode === 'list' ? '' : 'none';
    if (gridEl) gridEl.style.display = mode === 'grid' ? 'grid' : 'none';
    if (listBtn) {
        listBtn.style.background = mode === 'list' ? 'var(--forest)' : 'var(--paper)';
        listBtn.style.color      = mode === 'list' ? 'var(--cream)'  : 'var(--ink-2)';
    }
    if (gridBtn) {
        gridBtn.style.background = mode === 'grid' ? 'var(--forest)' : 'var(--paper)';
        gridBtn.style.color      = mode === 'grid' ? 'var(--cream)'  : 'var(--ink-2)';
    }
};

// ─────────────────────────────────────────────────────────────
// Theme toggle
// ─────────────────────────────────────────────────────────────
window.jhSetTheme = function (theme, csrfToken, themeUrl) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('jh-theme', theme);
    fetch(themeUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ theme })
    });
    // Update theme toggle button icon
    const sunIcon  = document.getElementById('jh-icon-sun');
    const moonIcon = document.getElementById('jh-icon-moon');
    if (sunIcon)  sunIcon.style.display  = theme === 'dark'  ? 'none' : '';
    if (moonIcon) moonIcon.style.display = theme === 'dark'  ? ''     : 'none';
    // Update settings page active indicators if present
    document.querySelectorAll('[data-theme-opt]').forEach(el => {
        const isActive = el.dataset.themeOpt === theme;
        el.style.border = isActive ? '2px solid var(--forest)' : '2px solid var(--rule)';
        const badge = el.querySelector('.jh-theme-active');
        if (badge) badge.style.display = isActive ? '' : 'none';
    });
};

// ─────────────────────────────────────────────────────────────
// Global search (vanilla JS)
// ─────────────────────────────────────────────────────────────
function jhInitGlobalSearch() {
    const input    = document.getElementById('jh-search-input');
    const dropdown = document.getElementById('jh-search-dropdown');
    const list     = document.getElementById('jh-search-results');
    if (!input || !dropdown || !list) return;

    let results = [], activeIndex = 0;

    function debounce(fn, ms) {
        let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
    }

    async function doSearch() {
        const q = input.value.trim();
        if (q.length < 2) { results = []; dropdown.style.display = 'none'; return; }
        try {
            const res = await fetch('/api/search?q=' + encodeURIComponent(q));
            results = await res.json();
            activeIndex = 0;
            renderResults();
        } catch { results = []; dropdown.style.display = 'none'; }
    }

    function renderResults() {
        if (!results.length) { dropdown.style.display = 'none'; return; }
        list.innerHTML = results.map((r, i) => `
            <a href="/cases/${r.id}" class="tr-hover jh-sr-item" data-idx="${i}"
               style="display:flex;align-items:center;gap:10px;padding:10px 14px;text-decoration:none;color:var(--ink);border-bottom:1px solid var(--rule-2);">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--ink-3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;font-weight:500;">${r.name}</div>
                    <div style="font-size:11px;color:var(--ink-3);display:flex;gap:8px;margin-top:2px;">
                        <span class="mono">${r.case_uid}</span><span>${r.primary_issue}</span>
                    </div>
                </div>
                <span class="pill" style="font-size:10px;">${r.status}</span>
            </a>`).join('');
        list.querySelectorAll('.jh-sr-item').forEach((el, i) => {
            el.addEventListener('mouseenter', () => { activeIndex = i; highlight(); });
        });
        highlight();
        dropdown.style.display = 'block';
    }

    function highlight() {
        list.querySelectorAll('.jh-sr-item').forEach((el, i) => {
            el.style.background = i === activeIndex ? 'var(--parchment-2)' : '';
        });
    }

    input.addEventListener('input', debounce(doSearch, 300));
    input.addEventListener('focus', () => { if (results.length) dropdown.style.display = 'block'; });
    input.addEventListener('keydown', e => {
        if (e.key === 'Escape') { dropdown.style.display = 'none'; }
        if (e.key === 'ArrowDown') { e.preventDefault(); activeIndex = (activeIndex + 1) % Math.max(results.length, 1); highlight(); }
        if (e.key === 'ArrowUp')   { e.preventDefault(); activeIndex = (activeIndex - 1 + results.length) % Math.max(results.length, 1); highlight(); }
        if (e.key === 'Enter')     { e.preventDefault(); if (results[activeIndex]) window.location.href = '/cases/' + results[activeIndex].id; }
    });
    document.addEventListener('click', e => {
        if (!input.closest('[data-jh-search]').contains(e.target)) dropdown.style.display = 'none';
    });
}

// ─────────────────────────────────────────────────────────────
// Lookup management (settings page)
// ─────────────────────────────────────────────────────────────
function jhInitLookups() {
    const container = document.getElementById('jh-lookup-manager');
    if (!container) return;

    const searchInput = container.querySelector('#jh-lookup-search');
    const groupBtns   = () => container.querySelectorAll('[data-group-btn]');
    const panels      = () => container.querySelectorAll('[data-group-panel]');

    function setActive(groupKey) {
        groupBtns().forEach(btn => {
            const isActive = btn.dataset.groupBtn === groupKey;
            btn.style.background   = isActive ? 'var(--parchment)' : 'transparent';
            btn.style.borderLeft   = isActive ? '3px solid var(--forest)' : '3px solid transparent';
            btn.style.color        = isActive ? 'var(--ink)'  : 'var(--ink-3)';
            btn.style.fontWeight   = isActive ? '500' : '400';
            btn.style.paddingLeft  = isActive ? '11px' : '14px';
        });
        panels().forEach(panel => {
            panel.style.display = panel.dataset.groupPanel === groupKey ? '' : 'none';
        });
    }

    groupBtns().forEach(btn => {
        btn.addEventListener('click', () => {
            setActive(btn.dataset.groupBtn);
            // reset any open edit rows
            container.querySelectorAll('[data-edit-row]').forEach(r => {
                r.querySelector('[data-edit-form]').style.display  = 'none';
                r.querySelector('[data-read-view]').style.display  = '';
                r.querySelector('[data-status-cell]').style.display = '';
                r.querySelector('[data-action-cell]').style.display = '';
            });
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.toLowerCase();
            groupBtns().forEach(btn => {
                btn.style.display = btn.dataset.groupBtn.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    function openEditRow(row) {
        const ef = row.querySelector('[data-edit-form]');
        const sc = row.querySelector('[data-status-cell]');
        const ac = row.querySelector('[data-action-cell]');
        if (ef) ef.style.display = '';
        row.querySelectorAll('[data-read-view]').forEach(el => el.style.display = 'none');
        if (sc) sc.style.display = 'none';
        if (ac) ac.style.display = 'none';
    }
    function closeEditRow(row) {
        const ef = row.querySelector('[data-edit-form]');
        const sc = row.querySelector('[data-status-cell]');
        const ac = row.querySelector('[data-action-cell]');
        if (ef) ef.style.display = 'none';
        row.querySelectorAll('[data-read-view]').forEach(el => el.style.display = '');
        if (sc) sc.style.display = '';
        if (ac) ac.style.display = '';
    }

    // Edit row toggle
    container.querySelectorAll('[data-edit-btn]').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('[data-edit-row]');
            if (row) openEditRow(row);
        });
    });
    container.querySelectorAll('[data-cancel-edit]').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('[data-edit-row]');
            if (row) closeEditRow(row);
        });
    });

    // Show first group
    const firstBtn = container.querySelector('[data-group-btn]');
    if (firstBtn) setActive(firstBtn.dataset.groupBtn);
}

// ─────────────────────────────────────────────────────────────
// Intake wizard (vanilla JS)
// ─────────────────────────────────────────────────────────────
function jhInitIntakeWizard() {
    const form = document.getElementById('jh-intake-form');
    if (!form) return;

    let step = 1;
    const totalSteps = 5;
    const DRAFT_KEY = 'jh-intake-draft-' + (form.dataset.userId || 'guest');

    // ── Draft persistence (sessionStorage) ──────────────────────
    function saveDraft() {
        const data = { _step: step };
        form.querySelectorAll('input, select, textarea').forEach(el => {
            if (!el.name || el.name === '_token') return;
            data[el.name] = el.value;
        });
        try { sessionStorage.setItem(DRAFT_KEY, JSON.stringify(data)); } catch (_) {}
    }

    function restoreDraft() {
        let data;
        try { data = JSON.parse(sessionStorage.getItem(DRAFT_KEY)); } catch (_) {}
        if (!data) return false;
        // Restore field values
        Object.entries(data).forEach(([name, val]) => {
            if (name === '_step') return;
            const el = form.querySelector(`[name="${name}"]`);
            if (el) el.value = val;
        });
        return data._step || 1;
    }

    function clearDraft() {
        try { sessionStorage.removeItem(DRAFT_KEY); } catch (_) {}
    }

    const _adrOpts  = (typeof _adrPartners  !== 'undefined' && _adrPartners.length)  ? [..._adrPartners,  'Other'] : ['Provincial Ombudsman / Mohtasib', 'Federal Ombudsman', 'Other'];
    const _govtOpts = (typeof _governmentPartners !== 'undefined' && _governmentPartners.length) ? [..._governmentPartners, 'Other'] : ['Other'];
    const _ngoOpts  = (typeof _ngoPartners  !== 'undefined' && _ngoPartners.length)  ? [..._ngoPartners,  'Other'] : ['Other'];

    const pathwaySpecificMap = {
        'Legal Advice / Consultation':                  ['SLACC', 'Justice Hub Lawyer', 'NAZ Assist', 'Other'],
        'Court Representation':                         ['Justice Hub Lawyer', 'Other'],
        'Mediation':                                    ['Justice Hub Accredited Mediator', 'MICADR', 'Other'],
        'ADR / Dispute Resolution Support':             _adrOpts,
        'Government Department / Public Institution':   _govtOpts,
        'Civil Society / NGO / CSO / NPO':              _ngoOpts,
    };

    function getVal(name) {
        const el = form.querySelector(`[name="${name}"]`);
        return el ? el.value.trim() : '';
    }

    function showStep(n) {
        for (let i = 1; i <= totalSteps; i++) {
            const panel = document.getElementById('intake-step-' + i);
            if (panel) panel.style.display = i === n ? '' : 'none';
        }
        step = n;
        // Update step counter
        const counter = document.getElementById('jh-step-counter');
        if (counter) counter.textContent = n;
        // Update wizard nav
        document.querySelectorAll('[data-wizard-step]').forEach(el => {
            const s = parseInt(el.dataset.wizardStep);
            el.classList.toggle('active', s === n);
            el.classList.toggle('done',   s < n);
        });
        // Update progress bar
        const bar = document.getElementById('wiz-progress-bar');
        if (bar) bar.style.width = ((n - 1) / (totalSteps - 1) * 100) + '%';
        updateNav();
        updateValidationHint();
    }

    function canContinue() {
        if (step === 1) {
            if (!getVal('hubLocation') || !getVal('staffReceiving') || !getVal('consent')) return false;
            if (getVal('consent') === "No, I don't" && !getVal('noConsentReason')) return false;
            return true;
        }
        if (step === 2) {
            if (!getVal('heardAboutUs')) return false;
            if (getVal('heardAboutUs') === 'Other - please specify' && !getVal('heardAboutUsOther')) return false;
            if (getVal('heardAboutUs') === 'Paralegal' && !getVal('paralegalName')) return false;
            return true;
        }
        if (step === 3) {
            return !!(getVal('fullName') && getVal('fatherHusbandName') && getVal('gender') && getVal('age')
                && getVal('maritalStatus') && getVal('religion') && getVal('educationLevel')
                && getVal('monthlyIncome') && getVal('disabilityStatus')
                && getVal('primaryContact') && getVal('tehsil') && getVal('district') && getVal('preferredLanguage'));
        }
        if (step === 4) return !!(getVal('category') && getVal('urgencyLevel'));
        if (step === 5) {
            const pw = getVal('assignedPathway');
            if (!pw) return false;
            const needsSpecific = ['Legal Advice / Consultation','Court Representation','Mediation','ADR / Dispute Resolution Support'];
            if (needsSpecific.includes(pw) && !getVal('pathwaySpecific')) return false;
            if (pw === 'Court Representation' && getVal('pathwaySpecific') === 'Justice Hub Lawyer' && !getVal('assignedLawyer')) return false;
            if (pw === 'Government Department / Public Institution' && !getVal('pathwayGovernmentDept')) return false;
            if (pw === 'Civil Society / NGO / CSO / NPO' && !getVal('pathwayNgoName')) return false;
            if (pw === 'Other' && !getVal('pathwayOtherDetails')) return false;
            return true;
        }
        return true;
    }

    function updateNav() {
        const backBtn   = document.getElementById('intake-back-btn');
        const nextBtn   = document.getElementById('intake-next-btn');
        const submitBtn = document.getElementById('intake-submit-btn');
        const ok = canContinue();
        if (backBtn) backBtn.textContent = step === 1 ? 'Cancel' : '← Back';
        if (nextBtn)   { nextBtn.style.display  = step < 5 ? '' : 'none'; nextBtn.style.opacity = ok ? '1' : '0.5'; nextBtn.style.cursor = ok ? 'pointer' : 'not-allowed'; }
        if (submitBtn) { submitBtn.style.display = step === 5 ? '' : 'none'; submitBtn.disabled = !ok; submitBtn.style.opacity = ok ? '1' : '0.5'; }
    }

    function updateValidationHint() {
        const hint = document.getElementById('intake-validation-hint');
        if (!hint) return;
        const ok = canContinue();
        hint.textContent = ok ? '' : (step < 5 ? 'Complete required fields to continue' : 'Complete required fields to register');
        hint.style.display = ok ? 'none' : '';
    }

    // Conditional visibility helpers
    function toggleConditional(triggerName, showValues, targetId) {
        const trigger = form.querySelector(`[name="${triggerName}"]`);
        const target  = document.getElementById(targetId);
        if (!trigger || !target) return;
        const update = () => {
            const val = trigger.value;
            const show = Array.isArray(showValues) ? showValues.includes(val) : val === showValues;
            target.style.display = show ? '' : 'none';
        };
        trigger.addEventListener('change', update);
        update();
    }

    // Pathway-specific options
    function updatePathwaySpecificOptions() {
        const pw  = getVal('assignedPathway');
        const sel = form.querySelector('[name="pathwaySpecific"]');
        const box = document.getElementById('intake-pathway-specific-box');
        if (!sel || !box) return;
        const opts = pathwaySpecificMap[pw] || [];
        box.style.display = opts.length ? '' : 'none';
        const current = sel.value;
        sel.innerHTML = '<option value="">Select...</option>' + opts.map(o => `<option value="${o}"${o === current ? ' selected' : ''}>${o}</option>`).join('');
        updateValidationHint();
    }

    // Wire up all conditional fields
    toggleConditional('consent', "No, I don't", 'intake-no-consent-box');
    toggleConditional('heardAboutUs', 'Other - please specify', 'intake-other-source-box');
    toggleConditional('heardAboutUs', 'Paralegal', 'intake-paralegal-box');
    toggleConditional('heardAboutUs', 'NGO / CSO / NPO', 'intake-ngo-box');
    toggleConditional('heardAboutUs', 'Government Department', 'intake-govt-box');
    toggleConditional('gender', 'Other', 'intake-gender-other-box');
    toggleConditional('preferredLanguage', 'Other', 'intake-lang-other-box');
    toggleConditional('assignedPathway', 'Government Department / Public Institution', 'intake-pw-govt-box');
    toggleConditional('assignedPathway', 'Civil Society / NGO / CSO / NPO', 'intake-pw-ngo-box');
    toggleConditional('assignedPathway', 'Other', 'intake-pw-other-box');
    toggleConditional('pathwaySpecific', 'Other', 'intake-pw-specific-other-box');

    // Show lawyer dropdown when pathway + specific = Justice Hub Lawyer
    const lawyerPathways = ['Court Representation', 'Legal Advice / Consultation'];
    function updateLawyerBox() {
        const pw  = getVal('assignedPathway');
        const sp  = getVal('pathwaySpecific');
        const box = document.getElementById('intake-pw-lawyer-box');
        if (box) box.style.display = (lawyerPathways.includes(pw) && sp === 'Justice Hub Lawyer') ? '' : 'none';
        updateValidationHint();
    }
    const pathwaySpecificSel = form.querySelector('[name="pathwaySpecific"]');
    if (pathwaySpecificSel) pathwaySpecificSel.addEventListener('change', updateLawyerBox);

    // Show Hub Coordinator for Mediation / Govt / NGO / Other pathways
    const coordinatorPathways = ['Mediation', 'ADR / Dispute Resolution Support', 'Government Department / Public Institution', 'Civil Society / NGO / CSO / NPO', 'Other'];
    function updateCoordinatorBox() {
        const pw  = getVal('assignedPathway');
        const box = document.getElementById('intake-pw-coordinator-box');
        const nameEl   = document.getElementById('intake-pw-coordinator-name');
        const hiddenEl = document.getElementById('intake-pw-coordinator-hidden');
        if (!box) return;
        if (coordinatorPathways.includes(pw)) {
            box.style.display = '';
            const hubVal   = getVal('hubLocation');
            const coordName = (typeof _hubCoordinators !== 'undefined' && _hubCoordinators[hubVal]) || '';
            if (nameEl)   nameEl.value   = coordName;
            if (hiddenEl) hiddenEl.value = coordName;
        } else {
            box.style.display = 'none';
            if (nameEl)   nameEl.value   = '';
            if (hiddenEl) hiddenEl.value = '';
        }
    }

    const assignedPw = form.querySelector('[name="assignedPathway"]');
    if (assignedPw) {
        assignedPw.addEventListener('change', () => { updatePathwaySpecificOptions(); updateLawyerBox(); updateCoordinatorBox(); updateValidationHint(); });
        updatePathwaySpecificOptions();
        updateLawyerBox();
        updateCoordinatorBox();
    }

    // Re-run coordinator when hub changes (hub may change before pathway is picked)
    const hubSel2 = form.querySelector('[name="hubLocation"]');
    if (hubSel2) hubSel2.addEventListener('change', updateCoordinatorBox);

    // ── Repeat-client lookup via Search button ──────────────────
    (function () {
        const cnicEl    = form.querySelector('[name="cnic"]');
        const repeatEl  = form.querySelector('[name="repeatClient"]');
        const statusEl  = document.getElementById('intake-repeat-status');
        const searchBtn = document.getElementById('intake-cnic-search-btn');
        if (!cnicEl || !repeatEl || !searchBtn) return;

        function setField(name, value) {
            const el = form.querySelector(`[name="${name}"]`);
            if (el && value != null && value !== '') el.value = value;
        }

        function doLookup() {
            const cnic = cnicEl.value.replace(/\D/g, '');
            if (cnic.length !== 13) {
                statusEl.textContent = 'Enter a valid 13-digit CNIC';
                statusEl.style.color = 'var(--burgundy)';
                return;
            }

            searchBtn.disabled = true;
            statusEl.textContent = 'Searching…';
            statusEl.style.color = 'var(--ink-3)';

            fetch('/api/client-lookup?cnic=' + encodeURIComponent(cnic), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
            .then(r => r.json())
            .then(data => {
                if (!data.found) {
                    repeatEl.value = 'New';
                    statusEl.textContent = 'New Client';
                    statusEl.style.color = 'var(--forest)';
                    return;
                }
                repeatEl.value = 'Repeat';
                statusEl.textContent = 'Repeat — ' + data.case_uid;
                statusEl.style.color = 'var(--ochre)';

                // Prefill beneficiary fields from previous case
                const c = data.client;
                setField('fullName', c.fullName);
                setField('fatherHusbandName', c.fatherHusbandName);
                setField('gender', c.gender);
                setField('genderOther', c.genderOther);
                setField('age', c.age);
                setField('primaryContact', c.primaryContact);
                setField('alternativeContact', c.alternativeContact);
                setField('maritalStatus', c.maritalStatus);
                setField('religion', c.religion);
                setField('educationLevel', c.educationLevel);
                setField('occupation', c.occupation);
                setField('monthlyIncome', c.monthlyIncome);
                setField('disabilityStatus', c.disabilityStatus);
                setField('fullAddress', c.fullAddress);
                setField('unionCouncil', c.unionCouncil);
                setField('tehsil', c.tehsil);
                setField('district', c.district);
                setField('preferredLanguage', c.preferredLanguage);

                // Trigger conditional visibility updates
                form.querySelectorAll('select').forEach(s => s.dispatchEvent(new Event('change', { bubbles: true })));
                saveDraft();
            })
            .catch(() => {
                statusEl.textContent = 'Lookup failed';
                statusEl.style.color = 'var(--burgundy)';
            })
            .finally(() => { searchBtn.disabled = false; });
        }

        searchBtn.addEventListener('click', doLookup);
    })();

    // Re-validate + save draft on any field change
    form.addEventListener('change', () => { updateNav(); updateValidationHint(); saveDraft(); });
    form.addEventListener('input',  () => { updateNav(); updateValidationHint(); saveDraft(); });

    // Back button
    const backBtn = document.getElementById('intake-back-btn');
    if (backBtn) {
        backBtn.addEventListener('click', () => {
            if (step === 1) { clearDraft(); window.location.href = backBtn.dataset.cancelUrl || '/cases'; }
            else showStep(step - 1);
        });
    }

    // Next button
    const nextBtn = document.getElementById('intake-next-btn');
    if (nextBtn) {
        nextBtn.addEventListener('click', () => { if (canContinue()) showStep(step + 1); });
    }

    // Form submit guard — clear draft on successful submit
    form.addEventListener('submit', e => {
        if (!canContinue()) { e.preventDefault(); return; }
        clearDraft();
    });

    // Restore draft if available, then show the saved step
    const savedStep = restoreDraft();
    // Fire conditional toggles & pathway options after restore
    if (savedStep) {
        form.querySelectorAll('select').forEach(el => el.dispatchEvent(new Event('change', { bubbles: true })));
        updatePathwaySpecificOptions();
        // Re-restore pathwaySpecific after options are rebuilt
        try {
            const data = JSON.parse(sessionStorage.getItem(DRAFT_KEY));
            if (data && data.pathwaySpecific) {
                const sel = form.querySelector('[name="pathwaySpecific"]');
                if (sel) sel.value = data.pathwaySpecific;
            }
        } catch (_) {}
    }
    showStep(savedStep || 1);
}

// ─────────────────────────────────────────────────────────────
// Boot on DOMContentLoaded
// ─────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Restore saved theme
    const savedTheme = localStorage.getItem('jh-theme');
    if (savedTheme) document.documentElement.setAttribute('data-theme', savedTheme);

    // Init all data-chart elements
    document.querySelectorAll('[data-chart]').forEach(el => {
        const type   = el.dataset.chart;
        const canvas = el.querySelector('canvas');
        const attr   = el.dataset.chartConfig;
        if (!canvas || !attr || !chartInitializers[type]) return;
        try { chartInitializers[type](canvas, JSON.parse(attr)); }
        catch (e) { console.warn('Chart init failed:', type, e); }
    });

    jhInitGlobalSearch();
    jhInitLookups();
    jhInitIntakeWizard();
});
