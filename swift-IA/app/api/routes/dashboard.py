"""
Dashboard HTML interactif pour BTL SWIFT AI.
Sert une page web avec graphiques et formulaire de test.
"""

from fastapi import APIRouter
from fastapi.responses import HTMLResponse

router = APIRouter()


@router.get("/dashboard", response_class=HTMLResponse, include_in_schema=False)
async def dashboard():
    return """
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BTL SWIFT AI — Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-bottom: 1px solid #334155;
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #f8fafc;
        }
        .header h1 span { color: #3b82f6; }
        .header-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }
        .status-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            background: #22c55e;
            animation: pulse 2s infinite;
        }
        .status-dot.offline { background: #ef4444; animation: none; }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Layout */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px;
        }

        /* KPI Cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .kpi-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 20px;
        }
        .kpi-card .label {
            font-size: 12px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .kpi-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #f8fafc;
        }
        .kpi-card .sub {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }
        .kpi-card.danger .value { color: #ef4444; }
        .kpi-card.warning .value { color: #f59e0b; }
        .kpi-card.success .value { color: #22c55e; }
        .kpi-card.info .value { color: #3b82f6; }

        /* Main grid */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        /* Panels */
        .panel {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 20px;
        }
        .panel h2 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #cbd5e1;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Test Form */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .form-group { display: flex; flex-direction: column; }
        .form-group label {
            font-size: 11px;
            color: #94a3b8;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .form-group input, .form-group select {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 6px;
            padding: 8px 10px;
            color: #e2e8f0;
            font-size: 13px;
            outline: none;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #3b82f6;
        }
        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #3b82f6;
            color: white;
            margin-top: 12px;
            width: 100%;
            grid-column: 1 / -1;
        }
        .btn-primary:hover { background: #2563eb; }
        .btn-primary:disabled { background: #475569; cursor: not-allowed; }

        /* Result */
        .result-box {
            margin-top: 16px;
            padding: 16px;
            border-radius: 8px;
            display: none;
        }
        .result-box.show { display: block; }
        .result-box.anomaly {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .result-box.normal {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        .result-score {
            font-size: 48px;
            font-weight: 800;
            text-align: center;
            margin: 8px 0;
        }
        .result-label {
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        .reason-list { list-style: none; }
        .reason-item {
            padding: 8px 12px;
            margin-bottom: 6px;
            border-radius: 6px;
            background: rgba(255,255,255,0.03);
            border-left: 3px solid #64748b;
            font-size: 13px;
        }
        .reason-item.critical { border-left-color: #ef4444; }
        .reason-item.high { border-left-color: #f59e0b; }
        .reason-item.medium { border-left-color: #3b82f6; }
        .reason-item.low { border-left-color: #22c55e; }
        .reason-item .severity {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
            margin-right: 6px;
        }
        .severity.critical { background: #ef4444; color: #fff; }
        .severity.high { background: #f59e0b; color: #000; }
        .severity.medium { background: #3b82f6; color: #fff; }
        .severity.low { background: #22c55e; color: #000; }

        /* Chart canvas */
        .chart-container { position: relative; height: 250px; }

        /* Recent anomalies table */
        .recent-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .recent-table th {
            text-align: left;
            padding: 8px;
            color: #94a3b8;
            font-size: 11px;
            text-transform: uppercase;
            border-bottom: 1px solid #334155;
        }
        .recent-table td {
            padding: 8px;
            border-bottom: 1px solid #1e293b;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-red { background: rgba(239,68,68,0.2); color: #ef4444; }
        .badge-green { background: rgba(34,197,94,0.2); color: #22c55e; }

        /* Full width panel */
        .full-width { grid-column: 1 / -1; }

        /* Loading spinner */
        .spinner {
            display: inline-block;
            width: 16px; height: 16px;
            border: 2px solid #334155;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Responsive */
        @media (max-width: 900px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .main-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <h1><span>BTL</span> SWIFT AI — Anomaly Detection</h1>
    <div class="header-status">
        <div class="status-dot" id="statusDot"></div>
        <span id="statusText">Connecting...</span>
        <span style="color:#64748b; margin-left:12px;" id="modelVersion"></span>
    </div>
</div>

<div class="container">

    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card info">
            <div class="label">Total Predictions</div>
            <div class="value" id="kpiTotal">—</div>
            <div class="sub">depuis le démarrage</div>
        </div>
        <div class="kpi-card danger">
            <div class="label">Anomalies Détectées</div>
            <div class="value" id="kpiAnomalies">—</div>
            <div class="sub">messages suspects</div>
        </div>
        <div class="kpi-card warning">
            <div class="label">Taux d'Anomalie</div>
            <div class="value" id="kpiRate">—</div>
            <div class="sub">pourcentage</div>
        </div>
        <div class="kpi-card success">
            <div class="label">Modèle</div>
            <div class="value" style="font-size:16px;" id="kpiModel">—</div>
            <div class="sub" id="kpiUptime"></div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="main-grid">

        <!-- Test Panel -->
        <div class="panel">
            <h2>🔍 Tester un Message SWIFT</h2>
            <form id="predictForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Type Message</label>
                        <select id="f_type">
                            <option value="MT103">MT103 — Transfert Client</option>
                            <option value="MT202">MT202 — Transfert Interbancaire</option>
                            <option value="MT199">MT199 — Message Libre</option>
                            <option value="MT900">MT900 — Confirmation Débit</option>
                            <option value="MT910">MT910 — Confirmation Crédit</option>
                            <option value="MT940">MT940 — Relevé Client</option>
                            <option value="MT950">MT950 — Relevé</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Direction</label>
                        <select id="f_direction">
                            <option value="OUT">OUT — Sortant</option>
                            <option value="IN">IN — Entrant</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Montant</label>
                        <input type="number" id="f_amount" value="15000" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Devise</label>
                        <select id="f_currency">
                            <option value="EUR">EUR</option>
                            <option value="USD">USD</option>
                            <option value="TND">TND</option>
                            <option value="GBP">GBP</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Banque Émettrice</label>
                        <input type="text" id="f_sender" value="BTLKTNTT" placeholder="Code BIC">
                    </div>
                    <div class="form-group">
                        <label>Banque Réceptrice</label>
                        <input type="text" id="f_receiver" value="BNPAFRPP" placeholder="Code BIC">
                    </div>
                    <div class="form-group">
                        <label>Pays Émetteur</label>
                        <input type="text" id="f_scountry" value="TN" maxlength="2" placeholder="ISO 2">
                    </div>
                    <div class="form-group">
                        <label>Pays Récepteur</label>
                        <input type="text" id="f_rcountry" value="FR" maxlength="2" placeholder="ISO 2">
                    </div>
                    <div class="form-group">
                        <label>Date/Heure Création</label>
                        <input type="datetime-local" id="f_created" value="2026-04-18T10:30">
                    </div>
                    <div class="form-group">
                        <label>Référence</label>
                        <input type="text" id="f_reference" value="REF2026041800123">
                    </div>
                    <button type="submit" class="btn btn-primary" id="btnPredict">
                        Analyser le Message
                    </button>
                </div>
            </form>

            <!-- Result -->
            <div class="result-box" id="resultBox">
                <div class="result-label" id="resultLabel"></div>
                <div class="result-score" id="resultScore"></div>
                <ul class="reason-list" id="reasonList"></ul>
            </div>
        </div>

        <!-- Charts Panel -->
        <div class="panel">
            <h2>📊 Anomalies par Règle</h2>
            <div class="chart-container">
                <canvas id="chartRules"></canvas>
            </div>
            <h2 style="margin-top:24px;">📈 Anomalies par Type SWIFT</h2>
            <div class="chart-container">
                <canvas id="chartTypes"></canvas>
            </div>
        </div>

        <!-- Recent Anomalies -->
        <div class="panel full-width">
            <h2>🚨 Dernières Anomalies Détectées</h2>
            <table class="recent-table">
                <thead>
                    <tr>
                        <th>Heure</th>
                        <th>Type</th>
                        <th>Score</th>
                        <th>Statut</th>
                        <th>Règles</th>
                    </tr>
                </thead>
                <tbody id="recentBody">
                    <tr><td colspan="5" style="text-align:center;color:#64748b;">Aucune anomalie détectée</td></tr>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
const API = window.location.origin;

// ── Charts ──
let chartRules = null;
let chartTypes = null;

function initCharts() {
    const colors = {
        bg: ['#ef444440','#f59e0b40','#3b82f640','#22c55e40','#8b5cf640','#ec489940'],
        border: ['#ef4444','#f59e0b','#3b82f6','#22c55e','#8b5cf6','#ec4899'],
    };
    const defaults = Chart.defaults;
    defaults.color = '#94a3b8';
    defaults.borderColor = '#334155';

    chartRules = new Chart(document.getElementById('chartRules'), {
        type: 'bar',
        data: { labels: [], datasets: [{ label: 'Occurrences', data: [],
            backgroundColor: colors.bg, borderColor: colors.border, borderWidth: 1 }] },
        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    chartTypes = new Chart(document.getElementById('chartTypes'), {
        type: 'doughnut',
        data: { labels: [], datasets: [{ data: [],
            backgroundColor: colors.border, borderColor: '#1e293b', borderWidth: 3 }] },
        options: { responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'right' } } }
    });
}

// ── Fetch Stats ──
async function refreshStats() {
    try {
        const [health, stats] = await Promise.all([
            fetch(API + '/api/health').then(r => r.json()),
            fetch(API + '/api/stats').then(r => r.json()),
        ]);

        // Status
        const dot = document.getElementById('statusDot');
        const txt = document.getElementById('statusText');
        if (health.status === 'ok') {
            dot.className = 'status-dot';
            txt.textContent = 'Service actif';
        } else {
            dot.className = 'status-dot offline';
            txt.textContent = 'Service inactif';
        }
        document.getElementById('modelVersion').textContent = health.model_version;

        // KPIs
        document.getElementById('kpiTotal').textContent = stats.total_predictions.toLocaleString();
        document.getElementById('kpiAnomalies').textContent = stats.total_anomalies.toLocaleString();
        document.getElementById('kpiRate').textContent = (stats.anomaly_rate * 100).toFixed(1) + '%';
        document.getElementById('kpiModel').textContent = health.model_loaded ? health.model_version : 'Non chargé';
        document.getElementById('kpiUptime').textContent = 'Uptime: ' + formatUptime(health.uptime_seconds);

        // Chart: rules
        const rules = stats.anomalies_by_rule || {};
        const ruleLabels = Object.keys(rules);
        const ruleData = Object.values(rules);
        chartRules.data.labels = ruleLabels.map(formatRule);
        chartRules.data.datasets[0].data = ruleData;
        chartRules.update();

        // Chart: types
        const types = stats.anomalies_by_type || {};
        chartTypes.data.labels = Object.keys(types);
        chartTypes.data.datasets[0].data = Object.values(types);
        chartTypes.update();

        // Recent anomalies
        const tbody = document.getElementById('recentBody');
        const recent = stats.recent_anomalies || [];
        if (recent.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#64748b;">Aucune anomalie</td></tr>';
        } else {
            tbody.innerHTML = recent.map(a => `
                <tr>
                    <td>${a.timestamp ? a.timestamp.substring(11, 19) : '—'}</td>
                    <td><strong>${a.message_type || '—'}</strong></td>
                    <td style="color:${a.score > 0.7 ? '#ef4444' : '#f59e0b'};font-weight:700;">
                        ${(a.score * 100).toFixed(0)}%
                    </td>
                    <td><span class="badge badge-red">ANOMALIE</span></td>
                    <td>${(a.reasons || []).map(formatRule).join(', ')}</td>
                </tr>
            `).join('');
        }

    } catch (e) {
        document.getElementById('statusDot').className = 'status-dot offline';
        document.getElementById('statusText').textContent = 'Connexion perdue';
    }
}

// ── Predict ──
document.getElementById('predictForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnPredict');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Analyse en cours...';

    const body = {
        message_type: document.getElementById('f_type').value,
        direction: document.getElementById('f_direction').value,
        amount: parseFloat(document.getElementById('f_amount').value) || 0,
        currency: document.getElementById('f_currency').value,
        sender_bank: document.getElementById('f_sender').value,
        receiver_bank: document.getElementById('f_receiver').value,
        sender_country: document.getElementById('f_scountry').value || null,
        receiver_country: document.getElementById('f_rcountry').value || null,
        created_at: document.getElementById('f_created').value ? 
            document.getElementById('f_created').value.replace('T', 'T') + ':00' : null,
        reference: document.getElementById('f_reference').value || null,
        value_date: document.getElementById('f_created').value ?
            document.getElementById('f_created').value.substring(0, 10) : null,
    };

    try {
        const res = await fetch(API + '/api/predict', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        const data = await res.json();

        const box = document.getElementById('resultBox');
        const label = document.getElementById('resultLabel');
        const score = document.getElementById('resultScore');
        const list = document.getElementById('reasonList');

        box.className = 'result-box show ' + (data.is_anomaly ? 'anomaly' : 'normal');
        label.textContent = data.is_anomaly ? '⚠️ ANOMALIE DÉTECTÉE' : '✅ MESSAGE NORMAL';
        label.style.color = data.is_anomaly ? '#ef4444' : '#22c55e';
        score.textContent = (data.score * 100).toFixed(1) + '%';
        score.style.color = data.is_anomaly ? '#ef4444' : '#22c55e';

        if (data.reasons && data.reasons.length > 0) {
            list.innerHTML = data.reasons.map(r => `
                <li class="reason-item ${r.severity}">
                    <span class="severity ${r.severity}">${r.severity}</span>
                    <strong>${formatRule(r.rule)}</strong> — ${r.description}
                </li>
            `).join('');
        } else {
            list.innerHTML = '<li class="reason-item" style="color:#22c55e;">Aucune règle déclenchée</li>';
        }

        // Refresh stats after prediction
        setTimeout(refreshStats, 300);

    } catch (err) {
        alert('Erreur: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Analyser le Message';
    }
});

// ── Helpers ──
function formatRule(rule) {
    const map = {
        'unusual_amount': 'Montant Inhabituel',
        'high_risk_country': 'Pays à Risque',
        'missing_field': 'Champ Manquant',
        'inconsistent_field': 'Incohérence',
        'isolation_forest': 'Isolation Forest',
        'random_forest': 'Random Forest',
        'unusual_destination': 'Destination Inhabituelle',
    };
    return map[rule] || rule;
}

function formatUptime(s) {
    if (s < 60) return Math.floor(s) + 's';
    if (s < 3600) return Math.floor(s / 60) + 'min';
    return Math.floor(s / 3600) + 'h ' + Math.floor((s % 3600) / 60) + 'min';
}

// ── Init ──
initCharts();
refreshStats();
setInterval(refreshStats, 5000);
</script>
</body>
</html>
"""
