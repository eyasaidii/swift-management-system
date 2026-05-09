@extends('layouts.app')

@section('title', 'Importer des messages SWIFT - BTL Bank')

@push('styles')
<style>
/* ── Header ── */
.swift-import-header {
    background: linear-gradient(135deg, #0f172a 0%, #1a3a2a 50%, #1e4a1e 100%);
    border-radius: 14px;
    padding: 1.4rem 1.8rem;
    margin-bottom: 1.5rem;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}
.swift-import-header h1 {
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: .6rem;
}
.swift-import-header p { margin: .25rem 0 0; font-size: .78rem; opacity: .6; }
.btn-back-import {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.22);
    color: #fff;
    border-radius: 8px;
    padding: .4rem 1rem;
    font-size: .8rem;
    text-decoration: none;
    transition: background .2s;
    white-space: nowrap;
}
.btn-back-import:hover { background: rgba(255,255,255,.22); color: #fff; }

/* ── Cards ── */
.import-card {
    background: #fff;
    border: none;
    border-radius: 14px;
    box-shadow: 0 2px 14px rgba(0,0,0,.07);
    overflow: hidden;
    margin-bottom: 1.2rem;
}
.import-card-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: .9rem 1.4rem;
    display: flex;
    align-items: center;
    gap: .6rem;
}
.import-card-header .icon-wrap {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: .85rem;
    flex-shrink: 0;
}
.import-card-header .icon-wrap.green  { background: linear-gradient(135deg,#134e4a,#0f766e); }
.import-card-header .icon-wrap.orange { background: linear-gradient(135deg,#92400e,#d97706); }
.import-card-header .title   { font-size: .85rem; font-weight: 700; color: #1e293b; }
.import-card-header .subtitle{ font-size: .7rem; color: #94a3b8; }
.import-card-body { padding: 1.5rem; }

/* ── Zone de dépôt ── */
.dropzone-area {
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 2.5rem 1rem;
    text-align: center;
    background: #f8fafc;
    cursor: pointer;
    transition: border-color .25s, background .25s;
    position: relative;
}
.dropzone-area:hover,
.dropzone-area.dragover {
    border-color: #0f766e;
    background: #f0fdfa;
}
.dropzone-area .dz-icon {
    font-size: 3rem;
    color: #94a3b8;
    margin-bottom: .8rem;
    transition: color .25s;
    display: block;
}
.dropzone-area:hover .dz-icon,
.dropzone-area.dragover .dz-icon { color: #0f766e; }
.dropzone-area .dz-text {
    font-size: .85rem;
    color: #64748b;
    margin-bottom: .9rem;
}
.btn-choose-file {
    background: #fff;
    border: 1.5px solid #0f766e;
    color: #0f766e;
    border-radius: 8px;
    padding: .45rem 1.1rem;
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s, color .2s;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
}
.btn-choose-file:hover { background: #0f766e; color: #fff; }

/* ── Fichier sélectionné ── */
.file-selected-badge {
    display: none;
    margin-top: 1rem;
    background: #f0fdfa;
    border: 1px solid #99f6e4;
    border-radius: 9px;
    padding: .6rem 1rem;
    font-size: .8rem;
    color: #134e4a;
    align-items: center;
    gap: .5rem;
}
.file-selected-badge i { color: #0f766e; }

/* ── Formats info ── */
.formats-hint {
    font-size: .7rem;
    color: #94a3b8;
    margin-top: .8rem;
    display: flex;
    align-items: center;
    gap: .4rem;
}

/* ── Footer card ── */
.import-card-footer {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: .9rem 1.4rem;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: .7rem;
}
.btn-cancel-import {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #475569;
    border-radius: 8px;
    padding: .5rem 1.2rem;
    font-size: .82rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    transition: background .2s;
}
.btn-cancel-import:hover { background: #e2e8f0; color: #334155; }
.btn-import-submit {
    background: linear-gradient(135deg, #92400e, #d97706);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: .5rem 1.4rem;
    font-size: .82rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    transition: opacity .2s, transform .1s;
}
.btn-import-submit:hover { opacity: .9; transform: translateY(-1px); }
.btn-import-submit:disabled { opacity: .65; transform: none; cursor: not-allowed; }

/* ── Steps info ── */
.step-list { list-style: none; padding: 0; margin: 0; }
.step-list li {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: .55rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: .8rem;
    color: #374151;
}
.step-list li:last-child { border-bottom: none; }
.step-list .step-num {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: linear-gradient(135deg, #134e4a, #0f766e);
    color: #fff;
    font-size: .65rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: .05rem;
}

/* ── Alertes ── */
.alert-swift-ok {
    background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 4px solid #22c55e;
    border-radius: 10px; padding: .8rem 1rem; margin-bottom: 1rem;
    font-size: .82rem; color: #166534;
    display: flex; align-items: center; gap: .6rem;
}
.alert-swift-err {
    background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid #ef4444;
    border-radius: 10px; padding: .8rem 1rem; margin-bottom: 1rem;
    font-size: .82rem; color: #b91c1c;
    display: flex; align-items: center; gap: .6rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width:820px;">

    {{-- Header --}}
    <div class="swift-import-header">
        <div>
            <h1>
                <i class="fas fa-file-import" style="color:#fcd34d;"></i>
                Importer des messages SWIFT
            </h1>
            <p>Chargez un fichier XML (ISO 20022) ou TXT (MT legacy) — Tunisian Libyan Bank</p>
        </div>
        <a href="{{ route('swift.index') }}" class="btn-back-import">
            <i class="fas fa-arrow-left me-1"></i>Retour à la liste
        </a>
    </div>

    {{-- Alertes session --}}
    @if(session('success'))
    <div class="alert-swift-ok">
        <i class="fas fa-check-circle fa-lg"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="alert-swift-err">
        <i class="fas fa-exclamation-circle fa-lg"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Carte formulaire --}}
    <div class="import-card">
        <div class="import-card-header">
            <div class="icon-wrap green"><i class="fas fa-upload"></i></div>
            <div>
                <div class="title">Sélectionner un fichier</div>
                <div class="subtitle">Glissez-déposez ou cliquez pour choisir</div>
            </div>
        </div>

        <div class="import-card-body">
            <form action="{{ route('swift.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf

                <div class="dropzone-area" id="dropzone">
                    <i class="fas fa-cloud-upload-alt dz-icon"></i>
                    <div class="dz-text" id="dz-text">Glissez-déposez votre fichier ici</div>
                    <input type="file" name="file" id="file" accept=".xml,.txt" required style="display:none;">
                    <button type="button" class="btn-choose-file" id="fileButton">
                        <i class="fas fa-folder-open"></i>Choisir un fichier
                    </button>

                    <div class="file-selected-badge" id="fileSelectedBadge">
                        <i class="fas fa-file-check fa-lg"></i>
                        <span id="fileName">—</span>
                        <span id="fileSize" style="color:#94a3b8;margin-left:.3rem;"></span>
                    </div>
                </div>

                @error('file')
                <div style="color:#ef4444;font-size:.78rem;margin-top:.5rem;">
                    <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                </div>
                @enderror

                <div class="formats-hint">
                    <i class="fas fa-info-circle" style="color:#0f766e;"></i>
                    Formats acceptés : <strong>.xml</strong> (ISO 20022) &nbsp;·&nbsp; <strong>.txt</strong> (MT legacy) &nbsp;·&nbsp; Max <strong>10 Mo</strong>
                </div>
            </form>
        </div>

        <div class="import-card-footer">
            <a href="{{ route('swift.index') }}" class="btn-cancel-import">
                <i class="fas fa-times"></i>Annuler
            </a>
            <button type="submit" form="importForm" class="btn-import-submit" id="submitBtn">
                <i class="fas fa-file-import"></i>Importer et traiter
            </button>
        </div>
    </div>

    {{-- Carte infos processus --}}
    <div class="import-card">
        <div class="import-card-header">
            <div class="icon-wrap orange"><i class="fas fa-info"></i></div>
            <div>
                <div class="title">Processus après l'import</div>
                <div class="subtitle">Traitement automatique en 4 étapes</div>
            </div>
        </div>
        <div class="import-card-body">
            <ul class="step-list">
                <li>
                    <span class="step-num">1</span>
                    <span>Fichier reçu et validé (format, taille, intégrité)</span>
                </li>
                <li>
                    <span class="step-num">2</span>
                    <span>Conversion automatique <strong>MX → MT</strong> si applicable (ISO 20022 → MT legacy)</span>
                </li>
                <li>
                    <span class="step-num">3</span>
                    <span>Message enregistré en Oracle avec statut <strong>pending</strong> ou <strong>processed</strong></span>
                </li>
                <li>
                    <span class="step-num">4</span>
                    <span>Analyse IA automatique — score d'anomalie calculé et sauvegardé dans <strong>ANOMALIES_SWIFT</strong></span>
                </li>
            </ul>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput  = document.getElementById('file');
    const fileButton = document.getElementById('fileButton');
    const dzText     = document.getElementById('dz-text');
    const badge      = document.getElementById('fileSelectedBadge');
    const fileName   = document.getElementById('fileName');
    const fileSize   = document.getElementById('fileSize');
    const submitBtn  = document.getElementById('submitBtn');
    const dropzone   = document.getElementById('dropzone');
    const form       = document.getElementById('importForm');

    // Ouvrir dialogue fichier
    fileButton.addEventListener('click', () => fileInput.click());
    dropzone.addEventListener('click', e => { if (e.target === dropzone) fileInput.click(); });

    // Drag & drop
    dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('dragover'); });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            fileInput.dispatchEvent(new Event('change'));
        }
    });

    // Affichage du fichier sélectionné
    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            dzText.textContent = 'Fichier prêt à l\'import :';
            fileName.textContent = file.name;
            fileSize.textContent = '(' + (file.size / 1024).toFixed(1) + ' Ko)';
            badge.style.display = 'flex';
            fileButton.innerHTML = '<i class="fas fa-redo"></i>Changer de fichier';
        } else {
            dzText.textContent = 'Glissez-déposez votre fichier ici';
            badge.style.display = 'none';
            fileButton.innerHTML = '<i class="fas fa-folder-open"></i>Choisir un fichier';
        }
    });

    // Spinner à la soumission
    form.addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Import en cours…';
    });

    // Auto-fermeture alertes après 5s
    setTimeout(() => {
        document.querySelectorAll('.alert-swift-ok, .alert-swift-err').forEach(el => {
            el.style.transition = 'opacity .5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        });
    }, 5000);
});
</script>
@endpush