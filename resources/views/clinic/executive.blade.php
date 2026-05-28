<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Clinic Stock Management - SolidCare SSD</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --report-bg: #f4f9ff;
                --report-surface: #ffffff;
                --report-surface-alt: #eef5ff;
                --report-border: #dbe8f6;
                --report-ink: #132238;
                --report-muted: #6e8096;
                --report-primary: #2f6df6;
                --report-secondary: #14b8a6;
                --report-accent: #fb923c;
                --report-violet: #7c3aed;
                --report-danger: #ef4444;
            }

            body {
                font-family: 'Manrope', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background:
                    radial-gradient(circle at top, rgba(34, 197, 94, 0.16), transparent 34%),
                    linear-gradient(135deg, #0f172a 0%, #111827 45%, #020617 100%);
                color: #e5e7eb;
                min-height: 100vh;
            }
            .page-header {
                padding: 2rem 1rem 1rem;
                text-align: center;
            }
            .page-header h1 {
                font-family: 'Space Grotesk', 'Segoe UI', sans-serif;
                font-size: 2.4rem;
                margin-bottom: 0.5rem;
                color: #f8fafc;
                letter-spacing: -0.04em;
            }
            .page-header p {
                color: #cbd5e1;
            }
            .btn-custom {
                border-radius: 0.85rem;
                padding: 0.75rem 1rem;
            }
            .dashboard-action-btn {
                width: 100%;
                min-height: 52px;
            }
            .board-intro-copy {
                min-height: 72px;
            }
            .board-card {
                background: #f8fafc;
                color: #1f2937;
                border-radius: 1rem;
                border: 1px solid rgba(148, 163, 184, 0.2);
                box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
                height: 100%;
            }
            .board-card h3 {
                margin-bottom: 1rem;
                color: #0f172a;
                font-family: 'Space Grotesk', 'Segoe UI', sans-serif;
            }
            .status-pill {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.35rem 0.75rem;
                border-radius: 999px;
                font-size: 0.82rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                background: #dbeafe;
                color: #1d4ed8;
            }
            .mini-card {
                background: #f1f5f9;
                border: 1px solid #dbe4ee;
                border-radius: 0.9rem;
                padding: 1rem;
                margin-bottom: 1rem;
            }
            .mini-card:last-child {
                margin-bottom: 0;
            }
            .stock-entry-group {
                background: #f1f5f9;
                border: 1px solid #dbe4ee;
                border-radius: 0.9rem;
                padding: 1rem;
                margin-bottom: 1rem;
            }
            .hidden {
                display: none;
            }
            .stock-balance {
                display: block;
                font-size: 1.35rem;
                font-weight: 800;
                color: #0f766e;
                margin-top: 0.35rem;
            }
            .comment-box,
            .reply-box {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 0.85rem;
                padding: 0.9rem 1rem;
                margin-top: 1rem;
            }
            .reply-box {
                margin-left: 1rem;
                background: #f1f5f9;
            }
            .read-only-note {
                background: #f1f5f9;
                border: 1px solid #dbe4ee;
                border-radius: 0.9rem;
                padding: 1rem;
                color: #475569;
            }
            .usage-table {
                width: 100%;
                font-size: 0.92rem;
                min-width: 860px;
            }
            .usage-table th,
            .usage-table td {
                padding: 0.55rem;
                border-bottom: 1px solid #e2e8f0;
                vertical-align: top;
                white-space: nowrap;
            }
            .usage-table th:nth-child(5),
            .usage-table td:nth-child(5) {
                white-space: normal;
                min-width: 180px;
            }
            .report-table {
                width: 100%;
                font-size: 0.92rem;
            }
            .report-table th,
            .report-table td {
                padding: 0.55rem;
                border-bottom: 1px solid #e2e8f0;
                vertical-align: top;
            }
            .report-shell {
                position: relative;
                overflow: hidden;
                background:
                    radial-gradient(circle at top right, rgba(47, 109, 246, 0.12), transparent 28%),
                    radial-gradient(circle at bottom left, rgba(20, 184, 166, 0.1), transparent 25%),
                    linear-gradient(180deg, #ffffff 0%, var(--report-bg) 100%);
            }
            .report-shell::before {
                content: '';
                position: absolute;
                inset: 0;
                background-image:
                    linear-gradient(rgba(219, 232, 246, 0.22) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(219, 232, 246, 0.22) 1px, transparent 1px);
                background-size: 44px 44px;
                mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.55), transparent 85%);
                pointer-events: none;
            }
            .report-browser-bar {
                position: relative;
                z-index: 1;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 0.95rem 1.4rem;
                border-bottom: 1px solid var(--report-border);
                background: rgba(255, 255, 255, 0.82);
                backdrop-filter: blur(12px);
            }
            .report-browser-dots {
                display: inline-flex;
                gap: 0.4rem;
                align-items: center;
            }
            .report-browser-dots span {
                width: 0.72rem;
                height: 0.72rem;
                border-radius: 50%;
            }
            .report-browser-dots span:nth-child(1) {
                background: #fb7185;
            }
            .report-browser-dots span:nth-child(2) {
                background: #fbbf24;
            }
            .report-browser-dots span:nth-child(3) {
                background: #34d399;
            }
            .report-browser-title {
                flex: 1;
                text-align: center;
                font-size: 0.82rem;
                font-weight: 700;
                color: var(--report-muted);
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }
            .report-browser-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.45rem 0.75rem;
                border-radius: 999px;
                background: #e9f0ff;
                color: var(--report-primary);
                font-size: 0.76rem;
                font-weight: 800;
                letter-spacing: 0.06em;
                text-transform: uppercase;
            }
            .report-toolbar {
                position: relative;
                z-index: 1;
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 1rem;
                flex-wrap: wrap;
                padding: 1.6rem 1.6rem 0;
            }
            .report-eyebrow,
            .table-kicker {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                color: var(--report-primary);
                font-size: 0.76rem;
                font-weight: 800;
                letter-spacing: 0.1em;
                text-transform: uppercase;
            }
            .report-heading {
                margin: 0.4rem 0 0.55rem;
                color: var(--report-ink);
                font-family: 'Space Grotesk', 'Segoe UI', sans-serif;
                font-size: clamp(1.85rem, 3vw, 2.5rem);
                line-height: 1.04;
                letter-spacing: -0.05em;
            }
            .report-subcopy {
                margin: 0;
                max-width: 760px;
                color: var(--report-muted);
                line-height: 1.7;
            }
            .report-actions {
                display: flex;
                gap: 0.8rem;
                align-items: center;
                flex-wrap: wrap;
            }
            .report-period-chip {
                display: grid;
                gap: 0.2rem;
                min-width: 200px;
                padding: 0.9rem 1rem;
                border-radius: 1rem;
                border: 1px solid var(--report-border);
                background: rgba(255, 255, 255, 0.86);
                box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
            }
            .report-period-chip span {
                color: var(--report-muted);
                font-size: 0.76rem;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }
            .report-period-chip strong {
                color: var(--report-ink);
                font-size: 1rem;
            }
            .report-board-body {
                position: relative;
                z-index: 1;
                padding: 1.5rem;
            }
            .report-filter-card {
                margin-bottom: 1rem;
                padding: 1.15rem;
                border-radius: 1.2rem;
                border: 1px solid var(--report-border);
                background: rgba(255, 255, 255, 0.86);
                box-shadow: 0 16px 34px rgba(15, 23, 42, 0.05);
                backdrop-filter: blur(12px);
            }
            .report-filter-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 1rem;
            }
            .report-filter-card .form-label {
                color: var(--report-ink);
                font-size: 0.82rem;
                font-weight: 800;
                letter-spacing: 0.04em;
            }
            .report-generator-launch {
                margin-bottom: 1rem;
            }
            .report-launch-action {
                width: min(100%, 340px);
            }
            .report-generator-launch h3 {
                margin-bottom: 0.45rem;
            }
            .report-generator-launch p {
                margin-bottom: 1rem;
                color: #64748b;
            }
            .report-generator-card {
                padding: 1.15rem;
                border-radius: 1.2rem;
                border: 1px solid var(--report-border);
                background: rgba(255, 255, 255, 0.92);
                box-shadow: 0 16px 34px rgba(15, 23, 42, 0.05);
            }
            .report-generator-menu-copy {
                margin-bottom: 0.85rem;
                color: var(--report-muted);
                font-size: 0.82rem;
                font-weight: 700;
                letter-spacing: 0.04em;
            }
            .report-type-menu-grid {
                display: grid;
                gap: 0.65rem;
                margin-bottom: 1rem;
            }
            .report-type-menu-button {
                width: 100%;
                border: 1px solid #dbe4ee;
                border-radius: 0.95rem;
                background: #ffffff;
                color: var(--report-ink);
                padding: 0.9rem 1rem;
                text-align: left;
                font-weight: 700;
                transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
            }
            .report-type-menu-button:hover,
            .report-type-menu-button:focus-visible {
                border-color: rgba(25, 135, 84, 0.35);
                background: #f4fbf6;
                outline: none;
            }
            .report-type-menu-button.is-active {
                border-color: rgba(25, 135, 84, 0.4);
                background: #eefaf2;
                color: #157347;
            }
            .report-generator-fields {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                margin-top: 0;
            }
            .report-generator-reset {
                margin-top: 0.7rem;
                text-align: center;
            }
            .report-generator-reset a {
                color: var(--report-muted);
                font-size: 0.92rem;
                text-decoration: none;
            }
            .report-generator-reset a:hover,
            .report-generator-reset a:focus-visible {
                color: var(--report-primary);
                text-decoration: underline;
            }
            .report-placeholder {
                margin-top: 1rem;
                padding: 1.25rem 1.35rem;
                border-radius: 1.1rem;
                border: 1px dashed rgba(47, 109, 246, 0.28);
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.9) 0%, rgba(238, 245, 255, 0.9) 100%);
                color: var(--report-muted);
                text-align: center;
                line-height: 1.7;
            }
            .report-placeholder strong {
                display: block;
                color: var(--report-ink);
                font-size: 1rem;
                margin-bottom: 0.35rem;
            }
            .report-filter-card .form-select,
            .report-filter-card .form-control {
                border: 1px solid #ccdbed;
                border-radius: 0.95rem;
                padding: 0.8rem 0.95rem;
                color: var(--report-ink);
                box-shadow: none;
            }
            .report-filter-card .form-select:focus,
            .report-filter-card .form-control:focus {
                border-color: rgba(47, 109, 246, 0.42);
                box-shadow: 0 0 0 0.2rem rgba(47, 109, 246, 0.1);
            }
            .report-filter-actions {
                display: flex;
                gap: 0.75rem;
                flex-wrap: wrap;
                margin-top: 1rem;
            }
            .report-kpi-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 1rem;
                margin-bottom: 1rem;
            }
            .report-kpi-card,
            .analytics-card,
            .report-table-card {
                animation: reportRise 0.6s ease both;
            }
            .report-kpi-card {
                padding: 1.15rem;
                border-radius: 1.2rem;
                border: 1px solid var(--report-border);
                background: var(--report-surface);
                box-shadow: 0 18px 34px rgba(15, 23, 42, 0.06);
            }
            .report-kpi-card small {
                display: block;
                color: var(--report-muted);
                font-size: 0.74rem;
                font-weight: 800;
                letter-spacing: 0.09em;
                text-transform: uppercase;
            }
            .report-kpi-value {
                display: block;
                margin: 0.45rem 0 0.35rem;
                color: var(--report-ink);
                font-family: 'Space Grotesk', 'Segoe UI', sans-serif;
                font-size: clamp(1.65rem, 2.6vw, 2.2rem);
                line-height: 1.05;
                letter-spacing: -0.05em;
            }
            .report-kpi-meta {
                margin: 0;
                color: var(--report-muted);
                line-height: 1.6;
                font-size: 0.9rem;
            }
            .report-kpi-accent {
                width: 100%;
                height: 0.32rem;
                margin-top: 0.95rem;
                border-radius: 999px;
                background: linear-gradient(90deg, var(--accent-color), rgba(255, 255, 255, 0));
            }
            .report-main-grid {
                display: grid;
                grid-template-columns: minmax(0, 1.05fr) minmax(0, 1.05fr) minmax(280px, 0.9fr);
                gap: 1rem;
                margin-bottom: 1rem;
            }
            .analytics-card,
            .report-table-card {
                padding: 1.2rem;
                border-radius: 1.25rem;
                border: 1px solid var(--report-border);
                background: var(--report-surface);
                box-shadow: 0 18px 34px rgba(15, 23, 42, 0.06);
            }
            .report-card-header,
            .report-table-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 0.9rem;
                margin-bottom: 1rem;
            }
            .report-card-header h4,
            .report-table-header h4 {
                margin: 0.25rem 0 0;
                color: var(--report-ink);
                font-family: 'Space Grotesk', 'Segoe UI', sans-serif;
                font-size: 1.25rem;
                letter-spacing: -0.04em;
            }
            .report-note {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.45rem 0.75rem;
                border-radius: 999px;
                background: #eef5ff;
                color: var(--report-muted);
                font-size: 0.78rem;
                font-weight: 700;
                text-align: center;
            }
            .donut-layout {
                display: grid;
                grid-template-columns: 180px minmax(0, 1fr);
                gap: 1rem;
                align-items: center;
            }
            .donut-chart {
                width: min(100%, 170px);
                aspect-ratio: 1;
                margin-inline: auto;
                border-radius: 50%;
                background: var(--chart-fill);
                position: relative;
                display: grid;
                place-items: center;
                box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.04);
            }
            .donut-chart::after {
                content: '';
                position: absolute;
                inset: 2.1rem;
                border-radius: 50%;
                background: linear-gradient(180deg, #ffffff 0%, #f3f8ff 100%);
                box-shadow: inset 0 0 0 1px rgba(207, 224, 242, 0.85);
            }
            .donut-center {
                position: relative;
                z-index: 1;
                text-align: center;
            }
            .donut-center strong {
                display: block;
                color: var(--report-ink);
                font-family: 'Space Grotesk', 'Segoe UI', sans-serif;
                font-size: 1.55rem;
                line-height: 1.05;
                letter-spacing: -0.05em;
            }
            .donut-center span {
                display: block;
                margin-top: 0.2rem;
                color: var(--report-muted);
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }
            .donut-legend {
                display: grid;
                gap: 0.8rem;
            }
            .donut-legend-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 0.8rem;
                padding: 0.8rem 0.9rem;
                border-radius: 0.95rem;
                background: #f8fbff;
                border: 1px solid #e7eef7;
            }
            .legend-meta {
                display: flex;
                align-items: center;
                gap: 0.65rem;
                min-width: 0;
            }
            .legend-swatch,
            .caption-swatch {
                flex: 0 0 auto;
                width: 0.75rem;
                height: 0.75rem;
                border-radius: 50%;
                background: var(--legend-color);
            }
            .legend-name {
                color: var(--report-ink);
                font-weight: 700;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .legend-value {
                text-align: right;
                color: var(--report-muted);
                font-size: 0.88rem;
            }
            .no-chart-data {
                padding: 1rem;
                border-radius: 1rem;
                border: 1px dashed #c8d8ea;
                background: #f8fbff;
                color: var(--report-muted);
                text-align: center;
            }
            .report-status-list {
                display: grid;
                gap: 0.9rem;
            }
            .status-metric {
                padding: 0.95rem;
                border-radius: 1rem;
                border: 1px solid #e6eef8;
                background: #f8fbff;
            }
            .status-row {
                display: flex;
                justify-content: space-between;
                gap: 0.8rem;
                align-items: center;
                margin-bottom: 0.6rem;
                color: var(--report-ink);
                font-weight: 700;
            }
            .status-row span {
                color: var(--report-muted);
                font-size: 0.85rem;
                font-weight: 700;
            }
            .status-track {
                width: 100%;
                height: 0.5rem;
                overflow: hidden;
                border-radius: 999px;
                background: #deebf7;
            }
            .status-fill {
                width: var(--fill-width);
                height: 100%;
                border-radius: inherit;
                background: var(--fill-color);
            }
            .report-activity-card {
                margin-bottom: 1rem;
            }
            .activity-canvas {
                display: flex;
                align-items: flex-end;
                gap: 0.9rem;
                min-height: 250px;
                padding: 1rem 0.35rem 0.45rem;
                overflow-x: auto;
            }
            .activity-cluster {
                min-width: 60px;
                display: grid;
                justify-items: center;
                gap: 0.75rem;
            }
            .activity-bars {
                display: flex;
                align-items: flex-end;
                gap: 0.45rem;
                height: 185px;
            }
            .activity-bar {
                width: 15px;
                height: var(--bar-height);
                min-height: 0;
                border-radius: 999px 999px 12px 12px;
                box-shadow: 0 12px 22px rgba(15, 23, 42, 0.12);
                transition: transform 0.2s ease;
            }
            .activity-bar:hover {
                transform: translateY(-4px);
            }
            .activity-bar--received {
                background: linear-gradient(180deg, rgba(47, 109, 246, 0.9), #2f6df6);
            }
            .activity-bar--used {
                background: linear-gradient(180deg, rgba(20, 184, 166, 0.92), #14b8a6);
            }
            .activity-label {
                color: var(--report-muted);
                font-size: 0.74rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                white-space: nowrap;
            }
            .activity-caption {
                display: flex;
                gap: 1rem;
                flex-wrap: wrap;
                color: var(--report-muted);
                font-size: 0.84rem;
            }
            .caption-key {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                font-weight: 700;
            }
            .report-tables {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem;
            }
            .report-tables > :last-child:nth-child(odd) {
                grid-column: 1 / -1;
            }
            .report-table-wrap {
                overflow: auto;
                border: 1px solid #e6eef8;
                border-radius: 1rem;
                background: #ffffff;
            }
            .report-table {
                margin-bottom: 0;
                min-width: 520px;
            }
            .report-table thead {
                background: #eef5ff;
            }
            .report-table th {
                color: var(--report-muted);
                font-size: 0.74rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }
            .report-table td {
                color: var(--report-ink);
            }
            .report-table tbody tr:nth-child(even) {
                background: #fbfdff;
            }
            .report-empty {
                margin: 0;
            }
            .report-disease-groups {
                display: grid;
                gap: 1rem;
            }
            .report-disease-card {
                border: 1px solid #e6eef8;
                border-radius: 1rem;
                background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
            }
            .report-disease-card-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 1rem;
                padding: 1rem 1rem 0;
            }
            .report-disease-card-header h5 {
                margin: 0.35rem 0 0.3rem;
                color: var(--report-ink);
                font-family: 'Space Grotesk', 'Segoe UI', sans-serif;
                font-size: 1.08rem;
                letter-spacing: -0.03em;
            }
            .report-disease-card-header p {
                margin: 0;
                color: var(--report-muted);
                line-height: 1.6;
                font-size: 0.92rem;
            }
            .report-disease-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 0.55rem;
                justify-content: flex-end;
            }
            .report-disease-chip {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.45rem 0.72rem;
                border-radius: 999px;
                background: #eef5ff;
                color: var(--report-ink);
                font-size: 0.78rem;
                font-weight: 700;
                text-align: center;
            }
            .report-disease-card .report-table-wrap {
                margin: 0.9rem 1rem 1rem;
            }
            .report-table--compact {
                min-width: 680px;
            }
            @keyframes reportRise {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            @media (max-width: 1199px) {
                .report-kpi-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
                .report-main-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
                .report-main-grid > :last-child {
                    grid-column: 1 / -1;
                }
            }
            @media (max-width: 991px) {
                .report-filter-grid,
                .report-tables,
                .report-main-grid {
                    grid-template-columns: 1fr;
                }
                .report-disease-card-header {
                    flex-direction: column;
                }
                .report-disease-meta {
                    justify-content: flex-start;
                }
                .report-browser-title {
                    text-align: left;
                }
            }
            @media (max-width: 767px) {
                .board-intro-copy {
                    min-height: 0;
                }
                .report-browser-bar,
                .report-toolbar,
                .report-board-body {
                    padding-inline: 1rem;
                }
                .report-browser-bar {
                    align-items: flex-start;
                    flex-wrap: wrap;
                }
                .report-kpi-grid {
                    grid-template-columns: 1fr;
                }
                .donut-layout {
                    grid-template-columns: 1fr;
                }
                .report-actions,
                .report-filter-actions {
                    width: 100%;
                }
                .report-actions > *,
                .report-filter-actions > * {
                    width: 100%;
                }
                .report-generator-card {
                    padding: 1rem;
                }
                .activity-canvas {
                    gap: 0.7rem;
                }
                .activity-cluster {
                    min-width: 54px;
                }
            }
            footer {
                text-align: center;
                padding: 1.5rem 0;
                color: #cbd5e1;
            }
        </style>
    </head>
    <body>
        @php
            $canManageClinicStock = $user->role === 'senior_nurse_officer';
            $canCommentOnStock = in_array($user->role, ['senior_nurse_officer', 'executive'], true);
            $canConfirmStock = $user->role === 'executive';
            $canGenerateStockReport = in_array($user->role, ['senior_nurse_officer', 'executive'], true);
            $reportWorkspaceRole = $user->role === 'senior_nurse_officer' ? 'Senior Nurse Officer' : 'Executive';
            $pendingConfirmationItems = $stockItems->filter(fn ($item) => $item->quantity_received > 0 && ! $item->confirmed_at);
            $totalReceived = (int) $stockReceipts->sum('quantity_received');
            $totalIssued = (int) $stockUsages->sum('quantity_issued');
            $netMovement = $totalReceived - $totalIssued;
            $reportActivityCount = $stockReceipts->count() + $stockUsages->count();
            $trackedMedicines = $stockItems->count();
            $inStockCount = $stockItems->where('status', 'in_stock')->count();
            $lowStockCount = $stockItems->where('status', 'low_stock')->count();
            $outOfStockCount = $stockItems->where('status', 'out_of_stock')->count();
            $topReceiptMedicine = $stockReceipts
                ->groupBy(fn ($receipt) => $receipt->stock_medicine_name ?: 'Unknown')
                ->map(fn ($rows) => (int) $rows->sum('quantity_received'))
                ->sortDesc();
            $topUsageMedicine = $stockUsages
                ->groupBy(fn ($usage) => optional($usage->stockItem)->medicine_name ?: 'Unknown')
                ->map(fn ($rows) => (int) $rows->sum('quantity_issued'))
                ->sortDesc();
            $receivedMix = $topReceiptMedicine->take(4);
            $usedMix = $topUsageMedicine->take(4);
            $chartPalette = ['#2f6df6', '#14b8a6', '#fb923c', '#7c3aed', '#ef4444', '#22c55e'];
            $buildDonutChart = function ($data) use ($chartPalette) {
                if ($data->isEmpty() || $data->sum() <= 0) {
                    return [
                        'gradient' => 'conic-gradient(#dbeafe 0deg 360deg)',
                        'legend' => collect(),
                    ];
                }

                $total = (float) $data->sum();
                $segments = [];
                $legend = [];
                $start = 0;

                foreach ($data as $label => $value) {
                    $color = $chartPalette[count($legend) % count($chartPalette)];
                    $degrees = ($value / $total) * 360;
                    $end = $start + $degrees;
                    $segments[] = sprintf('%s %.2fdeg %.2fdeg', $color, $start, $end);
                    $legend[] = [
                        'label' => $label,
                        'value' => (int) $value,
                        'percent' => round(($value / $total) * 100, 1),
                        'color' => $color,
                    ];
                    $start = $end;
                }

                if ($start < 360) {
                    $segments[] = sprintf('%s %.2fdeg 360deg', '#e2e8f0', $start);
                }

                return [
                    'gradient' => 'conic-gradient(' . implode(', ', $segments) . ')',
                    'legend' => collect($legend),
                ];
            };
            $receivedChart = $buildDonutChart($receivedMix);
            $usedChart = $buildDonutChart($usedMix);
            $trackedDiseases = $diseaseBreakdown->count();
            $diseaseCaseTotal = (int) $diseaseBreakdown->sum('case_count');
            $topDisease = $diseaseBreakdown->first();
            $topDiseaseMix = $diseaseBreakdown
                ->take(4)
                ->mapWithKeys(fn ($disease) => [$disease['label'] => $disease['case_count']]);
            $diseaseChart = $buildDonutChart($topDiseaseMix);
            $activityBucketFormat = in_array($reportType, ['month', 'week'], true) ? 'Y-m-d' : 'Y-m';
            $activityLabelFormat = in_array($reportType, ['month', 'week'], true) ? 'd M' : 'M y';
            $activityFullLabelFormat = in_array($reportType, ['month', 'week'], true) ? 'M j, Y' : 'F Y';
            $activitySource = collect();

            foreach ($stockReceipts as $receipt) {
                $date = $receipt->received_date ? \Carbon\Carbon::parse($receipt->received_date) : $receipt->created_at;
                $activitySource->push([
                    'bucket' => $date->format($activityBucketFormat),
                    'short_label' => $date->format($activityLabelFormat),
                    'full_label' => $date->format($activityFullLabelFormat),
                    'received' => (int) $receipt->quantity_received,
                    'used' => 0,
                ]);
            }

            foreach ($stockUsages as $usage) {
                $date = $usage->usage_date ? \Carbon\Carbon::parse($usage->usage_date) : $usage->created_at;
                $activitySource->push([
                    'bucket' => $date->format($activityBucketFormat),
                    'short_label' => $date->format($activityLabelFormat),
                    'full_label' => $date->format($activityFullLabelFormat),
                    'received' => 0,
                    'used' => (int) $usage->quantity_issued,
                ]);
            }

            $activityTimeline = $activitySource
                ->groupBy('bucket')
                ->map(function ($rows) {
                    $sample = $rows->first();

                    return [
                        'bucket' => $sample['bucket'],
                        'short_label' => $sample['short_label'],
                        'full_label' => $sample['full_label'],
                        'received' => $rows->sum('received'),
                        'used' => $rows->sum('used'),
                    ];
                })
                ->sortBy('bucket')
                ->values();

            if ($activityTimeline->count() > 8) {
                $activityTimeline = $activityTimeline->slice(-8)->values();
            }

            $peakActivity = max(1, (int) $activityTimeline->map(fn ($row) => max($row['received'], $row['used']))->max());
            $statusRows = collect([
                ['label' => 'In Stock', 'count' => $inStockCount, 'color' => '#14b8a6'],
                ['label' => 'Low Stock', 'count' => $lowStockCount, 'color' => '#fb923c'],
                ['label' => 'Out of Stock', 'count' => $outOfStockCount, 'color' => '#ef4444'],
            ]);
            $activeClinicPanel = old('clinic_panel', session('clinic_panel'));
        @endphp

        <div class="page-header">
            <h1>Clinic Stock Management</h1>
            <p>
                @if ($canManageClinicStock)
                    Use the clinic action boards below to add stock and review stock details.
                @else
                    View clinic stock details.
                @endif
            </p>
            <a href="{{ route('home') }}" class="btn btn-outline-light btn-custom mt-3">Back to Home</a>
        </div>

        <div class="container py-4">
            @if (session('success'))
                <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif

            @if ($pendingConfirmationItems->isNotEmpty())
                <div class="alert alert-warning mb-4">
                    <strong>Stock confirmation needed:</strong>
                    {{ $pendingConfirmationItems->count() }}
                    {{ \Illuminate\Support\Str::plural('item', $pendingConfirmationItems->count()) }}
                    waiting for confirmation.
                    <div class="mt-2">
                        {{ $pendingConfirmationItems->pluck('medicine_name')->join(', ') }}
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="row g-4">
                @if ($canManageClinicStock)
                    <div class="col-lg-4">
                        <div class="board-card p-4">
                            <h3>Add Stock</h3>
                            <p class="text-secondary">
                                Enter medicine name, quantity received, and expiry date. Records are saved to <code>clinic_stock_receipts</code> with automatic received date.
                            </p>

                            <div class="d-grid gap-2 mb-3">
                                <button type="button" id="show-add-stock-form" class="btn btn-success btn-custom dashboard-action-btn {{ $activeClinicPanel === 'add-stock' ? 'hidden' : '' }}">Add Stock</button>
                            </div>

                            <form method="POST" action="{{ route('clinic.stock.store') }}" id="add-stock-form" class="{{ $activeClinicPanel === 'add-stock' ? '' : 'hidden' }}">
                                @csrf
                                <input type="hidden" name="clinic_panel" value="add-stock">
                                <div class="d-flex justify-content-end mb-3">
                                    <button type="button" id="close-add-stock-form" class="btn btn-light btn-custom">Done</button>
                                </div>
                                <div id="stock-entry-list">
                                    <div class="stock-entry-group">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Medicine Name</label>
                                                <input type="text" name="stock_entries[0][medicine_name]" class="form-control" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Quantity Received</label>
                                                <input type="number" name="stock_entries[0][quantity_received]" min="0" class="form-control" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Expiry Date</label>
                                                <input type="date" name="stock_entries[0][expiry_date]" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="button" id="add-stock-group" class="btn btn-outline-secondary btn-custom">Add Another Stock Group</button>
                                    <button type="submit" class="btn btn-success btn-custom">Add Stock Item(s)</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <div class="{{ $canManageClinicStock ? 'col-lg-4' : 'col-12' }}">
                    <div class="board-card p-4">
                        <h3>Stock Details</h3>
                        <p class="text-secondary board-intro-copy">Review the current stock details, remaining balance, and discussion for each medicine.</p>

                        <div class="d-grid gap-2 mb-3">
                            <button type="button" id="show-stock-details" class="btn btn-primary btn-custom dashboard-action-btn {{ $activeClinicPanel === 'stock-details' ? 'hidden' : '' }}">Stock Details</button>
                        </div>

                        <div id="stock-details-board" class="{{ $activeClinicPanel === 'stock-details' ? '' : 'hidden' }}">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" id="close-stock-details" class="btn btn-light btn-custom">Done</button>
                            </div>
                            @foreach ($stockItems as $item)
                                <div class="mini-card">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <strong>{{ $item->medicine_name }}</strong>
                                            <div class="mt-2"><span class="status-pill">{{ str_replace('_', ' ', $item->status) }}</span></div>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-secondary">Remaining Balance</small>
                                            <span class="stock-balance">{{ $item->balance }}</span>
                                        </div>
                                    </div>

                                    <div class="mt-3 text-secondary">
                                        <div>Opening Stock: {{ $item->opening_stock }}</div>
                                        <div>Received: {{ $item->quantity_received > 0 ? $item->quantity_received : '' }}</div>
                                        <div>Issued: {{ $item->quantity_issued }}</div>
                                        <div>Date Entered: {{ optional($item->firstReceipt?->received_date ?? $item->created_at)->format('F j, Y') ?? 'Not recorded' }}</div>
                                        <div>Expiry Date: {{ optional($item->expiry_date)->format('F j, Y') ?? 'Not recorded' }}</div>
                                        <div>
                                            Confirmed:
                                            @if ($item->confirmed_at)
                                                {{ $item->confirmed_at->format('F j, Y g:i A') }}
                                                by {{ optional($item->confirmer)->name ?? 'Clinic Officer' }}
                                            @else
                                                Not confirmed
                                            @endif
                                        </div>
                                    </div>

                                    @if ($canConfirmStock)
                                        <form method="POST" action="{{ route('clinic.stock.confirm', $item) }}" class="mt-3">
                                            @csrf
                                            <input type="hidden" name="clinic_panel" value="stock-details">
                                            <button type="submit" class="btn btn-outline-success btn-custom w-100">
                                                {{ $item->confirmed_at ? 'Confirm Again' : 'Confirm Stock' }}
                                            </button>
                                        </form>
                                    @endif

                                    <div class="mt-4">
                                        <h6 class="mb-3">Comments and Replies</h6>

                                        @if ($canCommentOnStock)
                                            <form method="POST" action="{{ route('clinic.stock.comment', $item) }}">
                                                @csrf
                                                <input type="hidden" name="clinic_panel" value="stock-details">
                                                <label class="form-label">Add Comment</label>
                                                <textarea name="message" rows="2" class="form-control" placeholder="Write your stock comment here..." required></textarea>
                                                <button type="submit" class="btn btn-dark btn-custom mt-3 w-100">Post Comment</button>
                                            </form>
                                        @endif

                                        @forelse ($item->comments as $comment)
                                            <div class="comment-box">
                                                <div class="d-flex justify-content-between gap-3 mb-2">
                                                    <strong>{{ optional($comment->user)->name ?? 'Clinic Officer' }}</strong>
                                                    <span class="text-secondary">{{ $comment->created_at->format('F j, Y g:i A') }}</span>
                                                </div>
                                                <p class="mb-3">{{ $comment->message }}</p>

                                                @foreach ($comment->replies as $reply)
                                                    <div class="reply-box">
                                                        <div class="d-flex justify-content-between gap-3 mb-2">
                                                            <strong>{{ optional($reply->user)->name ?? 'Clinic Officer' }}</strong>
                                                            <span class="text-secondary">{{ $reply->created_at->format('F j, Y g:i A') }}</span>
                                                        </div>
                                                        <p class="mb-0">{{ $reply->message }}</p>
                                                    </div>
                                                @endforeach

                                                @if ($canCommentOnStock)
                                                    <form method="POST" action="{{ route('clinic.stock.comment', $item) }}" class="mt-3">
                                                        @csrf
                                                        <input type="hidden" name="clinic_panel" value="stock-details">
                                                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                                        <label class="form-label">Reply</label>
                                                        <textarea name="message" rows="2" class="form-control" placeholder="Write a reply..." required></textarea>
                                                        <button type="submit" class="btn btn-outline-secondary btn-custom mt-3 w-100">Reply</button>
                                                    </form>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="read-only-note mt-3">No comments recorded for this medicine yet.</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="board-card p-4">
                        <h3>Stock Usage Dashboard</h3>
                        <p class="text-secondary">
                            @if ($canManageClinicStock)
                                Record stock usage and manage usage records for students.
                            @else
                                Review stock usage records.
                            @endif
                        </p>

                        <div class="d-grid gap-2 mb-3">
                            <button type="button" id="show-stock-usage" class="btn btn-warning btn-custom dashboard-action-btn {{ $activeClinicPanel === 'stock-usage' ? 'hidden' : '' }}">
                                @if ($canManageClinicStock)
                                    Stock Usage Dashboard
                                @else
                                    View Stock Usage Dashboard
                                @endif
                            </button>
                        </div>

                        <div id="stock-usage-board" class="{{ $activeClinicPanel === 'stock-usage' ? '' : 'hidden' }}">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" id="close-stock-usage" class="btn btn-light btn-custom">Done</button>
                            </div>
                            @if ($canManageClinicStock)
                                <form method="POST" action="{{ route('clinic.stock.usage.store') }}" class="mini-card mb-3">
                                    @csrf
                                    <input type="hidden" name="clinic_panel" value="stock-usage">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Medicine</label>
                                            <select name="clinic_stock_item_id" class="form-select" required>
                                                <option value="">Select medicine</option>
                                                @foreach ($stockItems as $item)
                                                    <option value="{{ $item->id }}">{{ $item->medicine_name }} (Balance: {{ $item->balance }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Student ID</label>
                                            <input type="text" name="student_id" class="form-control" maxlength="100" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Quantity Issued</label>
                                            <input type="number" name="quantity_issued" min="1" class="form-control" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Diagnosis</label>
                                            <textarea name="diagnosis" rows="2" class="form-control" required></textarea>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-warning btn-custom mt-3 w-100">Save Usage</button>
                                </form>
                            @endif

                            @if ($stockUsages->isEmpty())
                                <div class="read-only-note">No stock usage records yet.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="usage-table">
                                        <thead>
                                            <tr>
                                                <th>Medicine</th>
                                                <th>Student</th>
                                                <th>Issued</th>
                                                <th>Remaining Stock</th>
                                                <th>Diagnosis</th>
                                                <th>Date</th>
                                                @if ($canManageClinicStock)
                                                    <th>Actions</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($stockUsages as $usage)
                                                <tr>
                                                    <td>{{ optional($usage->stockItem)->medicine_name ?? 'Unknown' }}</td>
                                                    <td>{{ $usage->student_id }}</td>
                                                    <td>{{ $usage->quantity_issued }}</td>
                                                    <td>{{ optional($usage->stockItem)->balance ?? 0 }}</td>
                                                    <td>{{ $usage->diagnosis }}</td>
                                                    <td>{{ optional($usage->usage_date)->format('M j, Y') ?? $usage->created_at->format('M j, Y') }}</td>
                                                    @if ($canManageClinicStock)
                                                        <td>
                                                            <div class="d-grid gap-2">
                                                                <button type="button" class="btn btn-sm btn-outline-primary toggle-usage-edit" data-target="edit-usage-{{ $usage->id }}">Edit</button>
                                                                <form method="POST" action="{{ route('clinic.stock.usage.delete', $usage) }}">
                                                                    @csrf
                                                                    <input type="hidden" name="clinic_panel" value="stock-usage">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">Delete</button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    @endif
                                                </tr>
                                                @if ($canManageClinicStock)
                                                    <tr id="edit-usage-{{ $usage->id }}" class="hidden">
                                                        <td colspan="7">
                                                            <form method="POST" action="{{ route('clinic.stock.usage.update', $usage) }}" class="mini-card">
                                                                @csrf
                                                                <input type="hidden" name="clinic_panel" value="stock-usage">
                                                                <div class="row g-3">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Medicine</label>
                                                                        <select name="clinic_stock_item_id" class="form-select" required>
                                                                            @foreach ($stockItems as $item)
                                                                                <option value="{{ $item->id }}" @selected($usage->clinic_stock_item_id === $item->id)>
                                                                                    {{ $item->medicine_name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label class="form-label">Student ID</label>
                                                                        <input type="text" name="student_id" class="form-control" value="{{ $usage->student_id }}" maxlength="100" required>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <label class="form-label">Issued</label>
                                                                        <input type="number" name="quantity_issued" min="1" class="form-control" value="{{ $usage->quantity_issued }}" required>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label class="form-label">Diagnosis</label>
                                                                        <textarea name="diagnosis" rows="1" class="form-control" required>{{ $usage->diagnosis }}</textarea>
                                                                    </div>
                                                                </div>
                                                                <button type="submit" class="btn btn-primary btn-custom mt-3">Save Changes</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($canGenerateStockReport)
                    <div class="col-12">
                        <div class="board-card p-0 overflow-hidden">
                            <div class="report-shell">
                                <div class="report-browser-bar">
                                    <div class="report-browser-dots" aria-hidden="true">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                    <div class="report-browser-title">SolidCare analytics / clinic-stock-report</div>
                                    <div class="report-browser-badge">{{ $reportWorkspaceRole }}</div>
                                </div>

                                <div class="report-toolbar">
                                    <div>
                                        <span class="report-eyebrow">Clinic Reporting Workspace</span>
                                        <h3 class="report-heading">Stock Report Overview</h3>
                                        <p class="report-subcopy">
                                            A cleaner analytics view for the clinic report, shaped around your reference image with clearer totals,
                                            stock pressure indicators, category mix, and movement trends for the selected period.
                                        </p>
                                    </div>

                                    @if ($reportGenerated)
                                        <div class="report-actions">
                                            <div class="report-period-chip">
                                                <span>Selected Period</span>
                                                <strong>{{ $reportLabel }}</strong>
                                            </div>
                                            <a
                                                href="{{ route('clinic') }}"
                                                class="btn btn-light btn-custom"
                                            >
                                                Done
                                            </a>
                                            <a
                                                href="{{ route('clinic.report.download', ['report_type' => $reportType, 'report_semester' => $reportSemester, 'report_month' => $reportMonth, 'report_start_date' => $reportStartDate->toDateString(), 'report_end_date' => $reportEndDate->toDateString(), 'report_year' => $reportYear]) }}"
                                                class="btn btn-outline-primary btn-custom"
                                            >
                                                Download CSV
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <div id="stock-report-board" class="report-board-body">
                                    <div class="report-generator-launch">
                                        <h3>Generate Report</h3>
                                        <p>Select a report type to display clinic stock analytics and summaries for the period you want.</p>

                                        <div class="d-grid gap-2 mb-3 report-launch-action">
                                            <button
                                                type="button"
                                                id="show-report-generator"
                                                class="btn btn-success btn-custom dashboard-action-btn {{ $reportGenerated ? 'hidden' : '' }}"
                                            >
                                                Generate Report
                                            </button>
                                        </div>

                                        <form method="GET" action="{{ route('clinic') }}" class="report-filter-card report-generator-card {{ $reportGenerated ? '' : 'hidden' }}" id="clinic-report-filter-form">
                                            <input type="hidden" name="report_generated" value="1">
                                            <input type="hidden" name="report_type" id="report-type-input" value="{{ $reportType }}">

                                            <div class="report-generator-menu-copy">Choose a report type from the menu below.</div>

                                            <div class="report-type-menu-grid" role="menu" aria-label="Report types">
                                                <button type="button" class="report-type-menu-button {{ $reportType === 'general' ? 'is-active' : '' }}" data-report-type="general">General Report</button>
                                                <button type="button" class="report-type-menu-button {{ $reportType === 'semester' ? 'is-active' : '' }}" data-report-type="semester">Semester Report</button>
                                                <button type="button" class="report-type-menu-button {{ $reportType === 'month' ? 'is-active' : '' }}" data-report-type="month">Monthly Report</button>
                                                <button type="button" class="report-type-menu-button {{ $reportType === 'week' ? 'is-active' : '' }}" data-report-type="week">Weekly Report</button>
                                                <button type="button" class="report-type-menu-button {{ $reportType === 'year' ? 'is-active' : '' }}" data-report-type="year">Yearly Report</button>
                                            </div>

                                            <div class="report-filter-grid report-generator-fields">
                                                <div>
                                                    <label class="form-label">Year</label>
                                                    <input type="number" name="report_year" class="form-control" min="2000" max="2100" value="{{ $reportYear }}" required>
                                                </div>
                                                <div>
                                                    <label class="form-label">Month</label>
                                                    <select name="report_month" class="form-select">
                                                        @for ($month = 1; $month <= 12; $month++)
                                                            <option value="{{ $month }}" @selected($reportMonth === $month)>{{ \Carbon\Carbon::create()->month($month)->format('F') }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label">Week Start Date</label>
                                                    <input type="date" name="report_start_date" class="form-control" value="{{ $reportStartDate->toDateString() }}" required>
                                                </div>
                                                <div>
                                                    <label class="form-label">Week End Date</label>
                                                    <input type="date" name="report_end_date" class="form-control" value="{{ $reportEndDate->toDateString() }}" required>
                                                </div>
                                                <div>
                                                    <label class="form-label">Semester</label>
                                                    <select name="report_semester" class="form-select">
                                                        <option value="1" @selected($reportSemester === 1)>Semester 1</option>
                                                        <option value="2" @selected($reportSemester === 2)>Semester 2</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="report-generator-reset">
                                                <a href="{{ route('clinic') }}">Reset Filters</a>
                                            </div>
                                        </form>
                                    </div>

                                    @if ($reportGenerated)
                                        <div class="report-kpi-grid">
                                            <article class="report-kpi-card" style="--accent-color: var(--report-primary);">
                                                <small>Total Clinic Stock Received</small>
                                                <span class="report-kpi-value">{{ number_format($totalReceived) }}</span>
                                                <p class="report-kpi-meta">
                                                    {{ $reportType === 'general' ? 'All medicine received into clinic stock across every recorded receipt.' : 'All medicine received into clinic stock for the selected report period.' }}
                                                </p>
                                                <div class="report-kpi-accent"></div>
                                            </article>

                                            <article class="report-kpi-card" style="--accent-color: var(--report-secondary);">
                                                <small>Units Issued</small>
                                                <span class="report-kpi-value">{{ number_format($totalIssued) }}</span>
                                                <p class="report-kpi-meta">
                                                    {{ $topUsageMedicine->isNotEmpty() ? $topUsageMedicine->keys()->first() . ' is the most used medicine.' : 'No usage entries recorded yet.' }}
                                                </p>
                                                <div class="report-kpi-accent"></div>
                                            </article>

                                            <article class="report-kpi-card" style="--accent-color: var(--report-accent);">
                                                <small>Net Movement</small>
                                                <span class="report-kpi-value">{{ $netMovement > 0 ? '+' : '' }}{{ number_format($netMovement) }}</span>
                                                <p class="report-kpi-meta">
                                                    {{ $netMovement >= 0 ? 'Receipts stayed ahead of usage.' : 'Usage exceeded new receipts in this report window.' }}
                                                </p>
                                                <div class="report-kpi-accent"></div>
                                            </article>

                                            <article class="report-kpi-card" style="--accent-color: var(--report-violet);">
                                                <small>Disease Categories</small>
                                                <span class="report-kpi-value">{{ number_format($trackedDiseases) }}</span>
                                                <p class="report-kpi-meta">
                                                    @if ($topDisease)
                                                        {{ $topDisease['label'] }} appears in {{ number_format($topDisease['case_count']) }}
                                                        {{ \Illuminate\Support\Str::plural('case', $topDisease['case_count']) }}
                                                        for this report window.
                                                    @else
                                                        No diagnosis categories have been captured in the selected report period yet.
                                                    @endif
                                                </p>
                                                <div class="report-kpi-accent"></div>
                                            </article>
                                        </div>

                                        <div class="report-main-grid">
                                            <article class="analytics-card">
                                                <div class="report-card-header">
                                                    <div>
                                                        <span class="table-kicker">Stock Received</span>
                                                        <h4>Inventory Intake Mix</h4>
                                                    </div>
                                                    <span class="status-pill">Top 4 Medicines</span>
                                                </div>

                                                @if ($receivedChart['legend']->isEmpty())
                                                    <div class="no-chart-data">No clinic stock receipt data is available yet.</div>
                                                @else
                                                    <div class="donut-layout">
                                                        <div class="donut-chart" style="--chart-fill: {{ $receivedChart['gradient'] }};">
                                                            <div class="donut-center">
                                                                <strong>{{ number_format($totalReceived) }}</strong>
                                                                <span>Units Received</span>
                                                            </div>
                                                        </div>

                                                        <div class="donut-legend">
                                                            @foreach ($receivedChart['legend'] as $legend)
                                                                <div class="donut-legend-item">
                                                                    <div class="legend-meta">
                                                                        <span class="legend-swatch" style="--legend-color: {{ $legend['color'] }};"></span>
                                                                        <span class="legend-name">{{ $legend['label'] }}</span>
                                                                    </div>
                                                                    <div class="legend-value">
                                                                        <strong>{{ number_format($legend['value']) }}</strong><br>
                                                                        {{ $legend['percent'] }}%
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </article>

                                            <article class="analytics-card">
                                                <div class="report-card-header">
                                                    <div>
                                                        <span class="table-kicker">Disease Categories</span>
                                                        <h4>Disease Mix</h4>
                                                    </div>
                                                    <span class="status-pill">Top 4 Diagnoses</span>
                                                </div>

                                                @if ($diseaseChart['legend']->isEmpty())
                                                    <div class="no-chart-data">No diagnosis data is available for the selected report period.</div>
                                                @else
                                                    <div class="donut-layout">
                                                        <div class="donut-chart" style="--chart-fill: {{ $diseaseChart['gradient'] }};">
                                                            <div class="donut-center">
                                                                <strong>{{ number_format($diseaseCaseTotal) }}</strong>
                                                                <span>Case Records</span>
                                                            </div>
                                                        </div>

                                                        <div class="donut-legend">
                                                            @foreach ($diseaseChart['legend'] as $legend)
                                                                <div class="donut-legend-item">
                                                                    <div class="legend-meta">
                                                                        <span class="legend-swatch" style="--legend-color: {{ $legend['color'] }};"></span>
                                                                        <span class="legend-name">{{ $legend['label'] }}</span>
                                                                    </div>
                                                                    <div class="legend-value">
                                                                        <strong>{{ number_format($legend['value']) }}</strong><br>
                                                                        {{ $legend['percent'] }}%
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </article>

                                            <article class="analytics-card">
                                                <div class="report-card-header">
                                                    <div>
                                                        <span class="table-kicker">Stock Health</span>
                                                        <h4>Stock Limits</h4>
                                                    </div>
                                                    <span class="report-note">{{ number_format($trackedMedicines) }} total lines</span>
                                                </div>

                                                <div class="report-status-list">
                                                    @foreach ($statusRows as $status)
                                                        <div class="status-metric">
                                                            <div class="status-row">
                                                                <strong>{{ $status['label'] }}</strong>
                                                                <span>{{ $status['count'] }}/{{ max($trackedMedicines, 1) }}</span>
                                                            </div>
                                                            <div class="status-track">
                                                                <div
                                                                    class="status-fill"
                                                                    style="--fill-width: {{ $trackedMedicines > 0 ? round(($status['count'] / $trackedMedicines) * 100) : 0 }}%; --fill-color: {{ $status['color'] }};"
                                                                ></div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </article>
                                        </div>

                                        <article class="analytics-card report-activity-card">
                                            <div class="report-card-header">
                                                <div>
                                                    <span class="table-kicker">Movement Over Time</span>
                                                    <h4>Receipt vs Usage Trend</h4>
                                                </div>
                                                <span class="report-note">Peak volume: {{ number_format($peakActivity) }} units</span>
                                            </div>

                                            @if ($activityTimeline->isEmpty())
                                                <div class="no-chart-data">There is no timeline activity to chart for this period yet.</div>
                                            @else
                                                <div class="activity-canvas">
                                                    @foreach ($activityTimeline as $point)
                                                        @php
                                                            $receivedHeight = $point['received'] > 0 ? max(12, round(($point['received'] / $peakActivity) * 100)) : 0;
                                                            $usedHeight = $point['used'] > 0 ? max(12, round(($point['used'] / $peakActivity) * 100)) : 0;
                                                        @endphp
                                                        <div class="activity-cluster" title="{{ $point['full_label'] }}">
                                                            <div class="activity-bars">
                                                                <span class="activity-bar activity-bar--received" style="--bar-height: {{ $receivedHeight }}%;"></span>
                                                                <span class="activity-bar activity-bar--used" style="--bar-height: {{ $usedHeight }}%;"></span>
                                                            </div>
                                                            <span class="activity-label">{{ $point['short_label'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <div class="activity-caption">
                                                    <span class="caption-key">
                                                        <span class="caption-swatch" style="--legend-color: var(--report-primary);"></span>
                                                        Receipts
                                                    </span>
                                                    <span class="caption-key">
                                                        <span class="caption-swatch" style="--legend-color: var(--report-secondary);"></span>
                                                        Usage
                                                    </span>
                                                </div>
                                            @endif
                                        </article>

                                        <div class="report-tables">
                                            <article class="report-table-card">
                                                <div class="report-table-header">
                                                    <div>
                                                        <span class="table-kicker">Stock</span>
                                                        <h4>Available Stock</h4>
                                                    </div>
                                                    <span class="report-note">{{ number_format($reportStockItems->count()) }} entries</span>
                                                </div>

                                                @if ($reportStockItems->isEmpty())
                                                    <div class="read-only-note report-empty">No available stock found for this report.</div>
                                                @else
                                                    <div class="report-table-wrap">
                                                        <table class="report-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Medicine</th>
                                                                    <th>Available Stock</th>
                                                                    <th>Date Entered</th>
                                                                    <th>Expiry Date</th>
                                                                    <th>Status</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($reportStockItems as $item)
                                                                    <tr>
                                                                        <td>{{ $item->medicine_name ?: 'Unknown' }}</td>
                                                                        <td>{{ number_format($item->balance) }}</td>
                                                                        <td>{{ optional($item->firstReceipt?->received_date ?? $item->created_at)->format('M j, Y') ?? 'Not recorded' }}</td>
                                                                        <td>{{ optional($item->expiry_date)->format('M j, Y') ?? 'Not recorded' }}</td>
                                                                        <td>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $item->status)) }}</td>
                                                                        <td>
                                                                            <form method="POST" action="{{ route('clinic.stock.delete', $item) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this stock item?');">
                                                                                @csrf
                                                                                <input type="hidden" name="clinic_panel" value="report">
                                                                                <input type="hidden" name="report_type" value="{{ $reportType }}">
                                                                                <input type="hidden" name="report_year" value="{{ $reportYear }}">
                                                                                <input type="hidden" name="report_month" value="{{ $reportMonth }}">
                                                                                <input type="hidden" name="report_start_date" value="{{ $reportStartDate->toDateString() }}">
                                                                                <input type="hidden" name="report_end_date" value="{{ $reportEndDate->toDateString() }}">
                                                                                <input type="hidden" name="report_semester" value="{{ $reportSemester }}">
                                                                                <button type="submit" class="btn-delete" title="Delete">
                                                                                    <i class="fas fa-trash-alt"></i>
                                                                                </button>
                                                                            </form>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </article>

                                            <article class="report-table-card">
                                                <div class="report-table-header">
                                                    <div>
                                                        <span class="table-kicker">Diagnosis</span>
                                                        <h4>Disease Summary Report</h4>
                                                    </div>
                                                    <span class="report-note">{{ number_format($trackedDiseases) }} categories</span>
                                                </div>

                                                @if ($diseaseBreakdown->isEmpty())
                                                    <div class="read-only-note report-empty">No disease categories have been recorded for the selected period.</div>
                                                @else
                                                    <div class="report-table-wrap">
                                                        <table class="report-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Disease</th>
                                                                    <th>Cases</th>
                                                                    <th>Quantity Used</th>
                                                                    <th>Medicines Used</th>
                                                                    <th>Latest Date</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($diseaseBreakdown as $disease)
                                                                    <tr>
                                                                        <td>{{ $disease['label'] }}</td>
                                                                        <td>{{ number_format($disease['case_count']) }}</td>
                                                                        <td>{{ number_format($disease['quantity_issued']) }}</td>
                                                                        <td>{{ $disease['medicine_names'] !== [] ? implode(', ', $disease['medicine_names']) : 'Unknown' }}</td>
                                                                        <td>{{ $disease['latest_used_at']?->format('M j, Y') ?? '-' }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </article>

                                            <article class="report-table-card">
                                                <div class="report-table-header">
                                                    <div>
                                                        <span class="table-kicker">Usage</span>
                                                        <h4>Stock Used by Disease</h4>
                                                    </div>
                                                    <span class="report-note">{{ number_format($stockUsages->count()) }} usage entries</span>
                                                </div>

                                                @if ($diseaseUsageGroups->isEmpty())
                                                    <div class="read-only-note report-empty">No stock usage records exist for the selected period.</div>
                                                @else
                                                    <div class="report-disease-groups">
                                                        @foreach ($diseaseUsageGroups as $diseaseGroup)
                                                            <section class="report-disease-card">
                                                                <div class="report-disease-card-header">
                                                                    <div>
                                                                        <span class="table-kicker">Disease</span>
                                                                        <h5>{{ $diseaseGroup['label'] }}</h5>
                                                                        <p>
                                                                            {{ $diseaseGroup['medicine_names'] !== [] ? 'Medicines used: ' . implode(', ', $diseaseGroup['medicine_names']) : 'No medicine names were captured for this disease group yet.' }}
                                                                        </p>
                                                                    </div>

                                                                    <div class="report-disease-meta">
                                                                        <span class="report-disease-chip">
                                                                            {{ number_format($diseaseGroup['case_count']) }}
                                                                            {{ \Illuminate\Support\Str::plural('case', $diseaseGroup['case_count']) }}
                                                                        </span>
                                                                        <span class="report-disease-chip">{{ number_format($diseaseGroup['quantity_issued']) }} units used</span>
                                                                        <span class="report-disease-chip">
                                                                            Latest:
                                                                            {{ $diseaseGroup['latest_used_at']?->format('M j, Y') ?? '-' }}
                                                                        </span>
                                                                    </div>
                                                                </div>

                                                                <div class="report-table-wrap">
                                                                    <table class="report-table report-table--compact">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Medicine</th>
                                                                                <th>Student ID</th>
                                                                                <th>Quantity Used</th>
                                                                                <th>Date Used</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($diseaseGroup['usages'] as $usage)
                                                                                <tr>
                                                                                    <td>{{ optional($usage->stockItem)->medicine_name ?? 'Unknown' }}</td>
                                                                                    <td>{{ $usage->student_id }}</td>
                                                                                    <td>{{ $usage->quantity_issued }}</td>
                                                                                    <td>{{ optional($usage->usage_date)->format('M j, Y') ?? optional($usage->created_at)->format('M j, Y') ?? '-' }}</td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </section>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </article>
                                        </div>
                                    @else
                                        <div class="report-placeholder">
                                            <strong>No report displayed yet</strong>
                                            Choose a report type, set the period fields you need, then click <em>Generate Report</em> to display the clinic report for that selection.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <footer>&copy; 2026 SolidCare SSD. All rights reserved.</footer>
        <script>
            (function () {
                const showFormButton = document.getElementById('show-add-stock-form');
                const addStockForm = document.getElementById('add-stock-form');
                const closeAddStockButton = document.getElementById('close-add-stock-form');
                const showDetailsButton = document.getElementById('show-stock-details');
                const detailsBoard = document.getElementById('stock-details-board');
                const closeDetailsButton = document.getElementById('close-stock-details');
                const showUsageButton = document.getElementById('show-stock-usage');
                const usageBoard = document.getElementById('stock-usage-board');
                const closeUsageButton = document.getElementById('close-stock-usage');
                const showReportGeneratorButton = document.getElementById('show-report-generator');
                const reportFilterForm = document.getElementById('clinic-report-filter-form');
                const reportTypeInput = document.getElementById('report-type-input');
                const reportTypeButtons = document.querySelectorAll('[data-report-type]');
                const addButton = document.getElementById('add-stock-group');
                const stockEntryList = document.getElementById('stock-entry-list');
                const editToggles = document.querySelectorAll('.toggle-usage-edit');
                let stockIndex = 1;

                if (showFormButton && addStockForm) {
                    showFormButton.addEventListener('click', function () {
                        addStockForm.classList.remove('hidden');
                        showFormButton.classList.add('hidden');
                    });
                }

                if (closeAddStockButton && addStockForm && showFormButton) {
                    closeAddStockButton.addEventListener('click', function () {
                        addStockForm.classList.add('hidden');
                        showFormButton.classList.remove('hidden');
                    });
                }

                if (showDetailsButton && detailsBoard) {
                    showDetailsButton.addEventListener('click', function () {
                        detailsBoard.classList.remove('hidden');
                        showDetailsButton.classList.add('hidden');
                    });
                }

                if (closeDetailsButton && detailsBoard && showDetailsButton) {
                    closeDetailsButton.addEventListener('click', function () {
                        detailsBoard.classList.add('hidden');
                        showDetailsButton.classList.remove('hidden');
                    });
                }

                if (showUsageButton && usageBoard) {
                    showUsageButton.addEventListener('click', function () {
                        usageBoard.classList.remove('hidden');
                        showUsageButton.classList.add('hidden');
                    });
                }

                if (closeUsageButton && usageBoard && showUsageButton) {
                    closeUsageButton.addEventListener('click', function () {
                        usageBoard.classList.add('hidden');
                        showUsageButton.classList.remove('hidden');
                    });
                }

                if (showReportGeneratorButton && reportFilterForm) {
                    showReportGeneratorButton.addEventListener('click', function () {
                        reportFilterForm.classList.remove('hidden');
                        showReportGeneratorButton.classList.add('hidden');
                    });
                }

                reportTypeButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        if (!reportFilterForm || !reportTypeInput) {
                            return;
                        }

                        reportTypeInput.value = button.dataset.reportType || 'general';

                        if (typeof reportFilterForm.requestSubmit === 'function') {
                            reportFilterForm.requestSubmit();
                            return;
                        }

                        reportFilterForm.submit();
                    });
                });

                if (addButton && stockEntryList) {
                    addButton.addEventListener('click', function () {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'stock-entry-group';
                        wrapper.innerHTML = `
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Medicine Name</label>
                                    <input type="text" name="stock_entries[${stockIndex}][medicine_name]" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Quantity Received</label>
                                    <input type="number" name="stock_entries[${stockIndex}][quantity_received]" min="0" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Expiry Date</label>
                                    <input type="date" name="stock_entries[${stockIndex}][expiry_date]" class="form-control" required>
                                </div>
                            </div>
                        `;
                        stockEntryList.appendChild(wrapper);
                        stockIndex += 1;
                    });
                }

                editToggles.forEach(function (button) {
                    button.addEventListener('click', function () {
                        const target = document.getElementById(button.dataset.target);
                        if (!target) {
                            return;
                        }

                        target.classList.toggle('hidden');
                    });
                });
            }());
        </script>
    </body>
</html>
