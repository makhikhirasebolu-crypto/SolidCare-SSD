{{-- resources/views/academic/referrals.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Academic Supports | SolidCare SSD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f0f4f8;
            font-family: 'Inter', sans-serif;
            color: #1e293b;
            padding: 2rem 1.5rem;
        }

        .dashboard {
            max-width: 1600px;
            margin: 0 auto;
        }

        .top-bar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            gap: 1rem;
        }

        .logo-area h1 {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            letter-spacing: -0.3px;
        }

        .logo-area p {
            font-size: 0.85rem;
            color: #4b5563;
            margin-top: 4px;
        }

        .header-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .user-card {
            background: white;
            border-radius: 60px;
            padding: 0.5rem 1.2rem;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            border: 1px solid #e2e8f0;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #2c3e66;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1rem;
        }

        .user-info strong {
            display: block;
            font-size: 0.9rem;
        }

        .user-info small {
            font-size: 0.7rem;
            color: #5b6e8c;
        }

        .desk-note {
            background: white;
            border-radius: 1.5rem;
            padding: 1rem 1.2rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            color: #475569;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .desk-note strong {
            color: #0f2b4d;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 1.2rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: 1.5rem;
            padding: 1.2rem 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            border: 1px solid #eef2ff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .stat-left h4 {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #5b6e8c;
            font-weight: 600;
        }

        .stat-left .number {
            font-size: 2.2rem;
            font-weight: 800;
            color: #0f2b4d;
            line-height: 1.2;
        }

        .stat-left small {
            color: #7b8ba3;
            font-size: 0.75rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            background: #eef2ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2c5282;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .flash {
            display: grid;
            gap: 0.8rem;
            margin-bottom: 1.2rem;
        }

        .alert {
            border-radius: 1rem;
            padding: 1rem 1.2rem;
            border: 1px solid #e2e8f0;
            margin-bottom: 0;
        }

        .alert-success {
            background: #ecfdf5;
            color: #166534;
            border-color: #bbf7d0;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .notice {
            padding: 1rem 1.2rem;
            border-radius: 1rem;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
        }

        .two-columns {
            display: flex;
            flex-wrap: wrap;
            gap: 1.8rem;
        }

        .two-columns.single-column .right-panel {
            flex: 1 1 100%;
            min-width: 100%;
        }

        .left-panel {
            flex: 2;
            min-width: 320px;
        }

        .right-panel {
            flex: 3;
            min-width: 360px;
        }

        .card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.02);
            border: 1px solid #e9edf2;
            margin-bottom: 1.8rem;
            overflow: hidden;
        }

        .compose-card {
            position: sticky;
            top: 1rem;
            align-self: flex-start;
        }

        .card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #edf2f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            background: #fefefe;
        }

        .card-header h3 {
            font-weight: 600;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #0f2b4d;
            margin: 0;
        }

        .card-subcopy {
            font-size: 0.82rem;
            color: #64748b;
            width: 100%;
            margin-top: 0.35rem;
        }

        .badge-live {
            background: #dcfce7;
            color: #15803d;
            font-size: 0.65rem;
            padding: 2px 8px;
            border-radius: 30px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .form-grid {
            padding: 1.2rem 1.5rem 1.5rem;
        }

        label,
        .sheet-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #5c6e8c;
            display: block;
            margin-bottom: 4px;
            letter-spacing: 0.03em;
        }

        input,
        select,
        textarea,
        .form-control,
        .form-select {
            width: 100%;
            padding: 0.65rem 0.8rem;
            border: 1px solid #cfdfed;
            border-radius: 14px;
            font-size: 0.85rem;
            font-family: 'Inter', sans-serif;
            background: #fff;
            color: #1e293b;
            transition: 0.2s;
            box-shadow: none;
        }

        input:focus,
        select:focus,
        textarea:focus,
        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b6ea0;
            box-shadow: 0 0 0 3px rgba(59, 110, 160, 0.1);
        }

        textarea {
            resize: vertical;
        }

        .btn-primary,
        .submit-btn,
        .comment-btn {
            background: #1e3a5f;
            border: none;
            color: white;
            padding: 0.7rem 1.4rem;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: 0.15s;
        }

        .btn-secondary,
        .reply-btn,
        .status-btn {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            padding: 0.55rem 1rem;
            border-radius: 40px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: 0.15s;
        }

        .btn-outline-sm {
            background: transparent;
            border: 1px solid #cbd5e1;
            padding: 0.3rem 0.8rem;
            border-radius: 30px;
            font-size: 0.75rem;
            color: #2d3a5e;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary:hover,
        .submit-btn:hover,
        .comment-btn:hover,
        .btn-secondary:hover,
        .reply-btn:hover,
        .status-btn:hover,
        .btn-outline-sm:hover {
            transform: translateY(-1px);
        }

        .sheet-actions {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .sheet-actions .submit-btn {
            min-width: 230px;
        }

        .attendance-sheet {
            background: #f9fbfe;
            border-radius: 1rem;
            padding: 1rem;
            margin-top: 0;
            border: 1px solid #edf2f7;
        }

        .attendance-sheet + .attendance-sheet {
            margin-top: 12px;
        }

        .sheet-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .sheet-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: #16324f;
        }

        .sheet-copy {
            margin: 0.25rem 0 0;
            color: #64748b;
            line-height: 1.6;
            font-size: 0.82rem;
        }

        .sheet-stamp {
            background: #eef2ff;
            color: #2c5282;
            font-size: 0.68rem;
            padding: 0.3rem 0.7rem;
            border-radius: 30px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .sheet-section {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #edf2f7;
        }

        .sheet-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .sheet-grid.cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .sheet-grid.cols-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .sheet-field {
            display: grid;
            gap: 0.35rem;
        }

        .sheet-field.full {
            grid-column: 1 / -1;
        }

        .sheet-note {
            font-size: 0.75rem;
            color: #7b8ba3;
            margin-top: 10px;
            padding: 0.9rem 1rem;
            border-radius: 1rem;
            border: 1px dashed #d8e5f3;
            background: #f8fbff;
        }

        .support-queue {
            padding: 0.2rem 0;
        }

        .empty {
            display: grid;
            justify-items: center;
            gap: 1rem;
            padding: 3rem 1.5rem;
            text-align: center;
        }

        .empty-orbit {
            position: relative;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: #eef3fc;
        }

        .empty-orbit::before {
            content: "";
            position: absolute;
            inset: 18px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
        }

        .empty h4 {
            color: #0f2b4d;
            font-size: 1.2rem;
        }

        .empty p {
            color: #64748b;
            line-height: 1.7;
            max-width: 520px;
        }

        .ref-card {
            border-bottom: 1px solid #f0f2f5;
            padding: 1rem 1.5rem;
            transition: background 0.1s;
        }

        .ref-card:last-child {
            border-bottom: none;
        }

        .ref-card:hover {
            background: #fafcff;
        }

        .queue-header,
        .action-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .badges {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .card-name {
            font-weight: 700;
            font-size: 1rem;
            color: #0f2b4d;
            margin: 0;
        }

        .meta,
        .referrer,
        .time {
            font-size: 0.75rem;
            color: #4a627a;
        }

        .priority-badge,
        .status-badge {
            font-size: 0.7rem;
            padding: 4px 10px;
            border-radius: 40px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .priority-badge {
            background: #e9ecef;
            color: #1e4a76;
        }

        .priority-normal .priority-badge:last-child {
            background: #e6f0ff;
            color: #1e4a76;
        }

        .priority-urgent .priority-badge:last-child {
            background: #fff3e3;
            color: #b45309;
        }

        .priority-critical .priority-badge:last-child {
            background: #fee2e2;
            color: #b91c1c;
        }

        .status-pending {
            background: #ffedd5;
            color: #9a3412;
        }

        .status-reviewed {
            background: #e0f2fe;
            color: #0f4c81;
        }

        .status-resolved {
            background: #e3f7ec;
            color: #166534;
        }

        .reason {
            margin-top: 10px;
            font-size: 0.8rem;
            background: #fafcff;
            border-radius: 20px;
            padding: 12px;
            border-left: 3px solid #2c5282;
            border-top: 1px solid #eef2ff;
            border-right: 1px solid #eef2ff;
            border-bottom: 1px solid #eef2ff;
        }

        .reason strong {
            color: #16324f;
            font-size: 0.8rem;
            text-transform: none;
            letter-spacing: 0;
        }

        .reason p {
            margin: 0.55rem 0 0;
            color: #1e293b;
            line-height: 1.7;
        }

        .sheet-read {
            background: white;
            border-radius: 1rem;
            padding: 10px;
            border: 1px solid #eef2ff;
        }

        .sheet-read strong {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.68rem;
            text-transform: uppercase;
            color: #5c6e8c;
            letter-spacing: 0.04em;
        }

        .sheet-read span,
        .sheet-read p {
            margin: 0;
            color: #1e293b;
            line-height: 1.65;
            white-space: pre-wrap;
        }

        .read-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #d7e2ee;
            border-radius: 0.8rem;
            overflow: hidden;
            font-size: 0.78rem;
        }

        .read-table th,
        .read-table td {
            padding: 0.65rem;
            border-bottom: 1px solid #e4edf6;
            vertical-align: top;
            color: #1e293b;
        }

        .read-table th {
            background: #eef6ff;
            color: #1e4a76;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .read-table tr:last-child td {
            border-bottom: 0;
        }

        .paper-feedback-form {
            background:
                linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            color: #1f2937;
            border: 1px solid #dbe5f0;
            border-radius: 1.25rem;
            padding: 1.35rem;
            box-shadow: 0 14px 34px rgba(15, 43, 77, 0.08);
        }

        .paper-feedback-form .paper-code {
            text-align: right;
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .paper-institution-name {
            margin: 0 auto 0.85rem;
            text-align: center;
            color: #0f2b4d;
            line-height: 1.25;
            font-weight: 800;
        }

        .paper-institution-name span {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 0.15rem;
        }

        .paper-title {
            text-align: center;
            margin-bottom: 1.15rem;
            color: #0f2b4d;
        }

        .paper-title strong {
            display: inline-block;
            background: #eef6ff;
            color: #1e4a76;
            border: 1px solid #d7e8fb;
            border-radius: 999px;
            padding: 0.32rem 0.75rem;
            font-size: 0.72rem;
            text-transform: uppercase;
            margin-top: 0.2rem;
            max-width: 100%;
        }

        .paper-section-label {
            font-weight: 700;
            font-size: 0.78rem;
            color: #16324f;
            margin: 1.1rem 0 0.45rem;
            letter-spacing: 0.02em;
        }

        .paper-row {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 0.85rem;
            align-items: stretch;
            margin-bottom: 0.85rem;
        }

        .paper-field {
            grid-column: span 4;
            display: grid;
            gap: 0.35rem;
            min-width: 0;
        }

        .paper-field.small { grid-column: span 3; }
        .paper-field.tiny { grid-column: span 2; }
        .paper-field.wide { grid-column: span 6; }
        .paper-field.full { grid-column: 1 / -1; }

        .paper-field label {
            margin: 0;
            color: #475569;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            line-height: 1.35;
        }

        .paper-feedback-form .form-control {
            border: 1px solid #d7e2ee;
            border-radius: 0.75rem;
            padding: 0.62rem 0.75rem;
            min-height: 2.55rem;
            background: #fff;
            color: #172033;
            font-size: 0.88rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }

        .paper-feedback-form .form-control:focus {
            border-color: #2c5282;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(44, 82, 130, 0.12);
        }

        .paper-feedback-form textarea.form-control {
            line-height: 1.6;
            min-height: 7rem;
            resize: vertical;
        }

        .paper-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 0.45rem;
            font-size: 0.78rem;
            overflow: hidden;
            border: 1px solid #d7e2ee;
            border-radius: 0.95rem;
        }

        .paper-table th,
        .paper-table td {
            border: 0;
            border-bottom: 1px solid #e4edf6;
            padding: 0.7rem;
            vertical-align: top;
            background: #fff;
        }

        .paper-table th {
            text-align: left;
            background: #eef6ff;
            color: #1e4a76;
            font-weight: 700;
        }

        .paper-table tr:last-child td {
            border-bottom: 0;
        }

        .paper-table textarea.form-control {
            min-height: 9rem;
            border: 0;
            border-radius: 0.7rem;
            background: #fbfdff;
        }

        .paper-table .form-control {
            width: 100%;
        }

        .paper-table input.form-control {
            min-height: 2.35rem;
        }

        .paper-table .group-feedback {
            min-height: 3.5rem;
        }

        @media (max-width: 768px) {
            .paper-feedback-form {
                padding: 0.85rem;
            }

            .paper-row {
                grid-template-columns: 1fr;
            }

            .paper-field,
            .paper-field.small,
            .paper-field.tiny,
            .paper-field.wide,
            .paper-field.full {
                grid-column: 1 / -1;
            }

            .paper-field label {
                white-space: normal;
            }
        }

        .conv {
            margin-top: 1rem;
        }

        .comment,
        .reply {
            background: white;
            border-radius: 1rem;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #eef2ff;
        }

        .reply {
            background: #f8fbff;
            margin-left: 1rem;
        }

        .comment-btn {
            margin-top: 0.5rem;
        }

        .reply-btn {
            min-width: 92px;
        }

        .input-group {
            display: flex;
            align-items: stretch;
            gap: 0.5rem;
        }

        .input-group .form-control {
            margin-bottom: 0;
        }

        .review-btn {
            color: #0f4c81;
        }

        .resolve-btn {
            color: #166534;
        }

        .count {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            padding: 0.5rem 1rem;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .footer-note {
            text-align: center;
            font-size: 0.7rem;
            color: #7b8ba3;
            margin-top: 2rem;
        }

        /* Clickable Name List Styles */
        .student-name-list {
            display: grid;
            gap: 1rem;
            padding: 1rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .queue-group {
            background: white;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #cbd5e1;
            border-radius: 1rem;
            padding: 1rem;
        }

        .queue-group-pending {
            border-left-color: #f59e0b;
        }

        .queue-group-attended {
            border-left-color: #10b981;
        }

        .queue-group-header {
            margin-bottom: 0.85rem;
        }

        .queue-group-title-row {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .queue-group-label {
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #0f2b4d;
        }

        .queue-group-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            padding: 0.2rem 0.65rem;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #334155;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .queue-group-pending .queue-group-count {
            background: #fff7ed;
            border-color: #fed7aa;
            color: #9a3412;
        }

        .queue-group-attended .queue-group-count {
            background: #ecfdf5;
            border-color: #bbf7d0;
            color: #166534;
        }

        .queue-group-copy {
            margin: 0.35rem 0 0;
            font-size: 0.8rem;
            color: #64748b;
        }

        .queue-group-items {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .queue-group-empty {
            padding: 0.8rem 0.95rem;
            border-radius: 0.85rem;
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
            color: #64748b;
            font-size: 0.8rem;
        }

        .student-name-item {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 500;
            color: #1e293b;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .student-name-item:hover {
            background: #eef2ff;
            border-color: #3b6ea0;
            color: #1e3a5f;
        }

        .student-name-item.active {
            background: #1e3a5f;
            border-color: #1e3a5f;
            color: white;
        }

        .student-name-item .student-id {
            font-size: 0.7rem;
            color: #64748b;
        }

        .student-name-item:hover .student-id,
        .student-name-item.active .student-id {
            color: rgba(255, 255, 255, 0.7);
        }

        .student-name-item .priority-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .student-name-item .priority-dot.normal { background: #3b82f6; }
        .student-name-item .priority-dot.urgent { background: #f59e0b; }
        .student-name-item .priority-dot.critical { background: #ef4444; }

        .student-name-item.active .priority-dot.normal { background: #60a5fa; }
        .student-name-item.active .priority-dot.urgent { background: #fbbf24; }
        .student-name-item.active .priority-dot.critical { background: #f87171; }

        /* Student Info Panel */
        .student-info-panel {
            display: none;
            padding: 1.5rem;
            background: white;
            border-bottom: 1px solid #e2e8f0;
        }

        .student-info-panel.show {
            display: block;
        }

        .student-info-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .student-info-header h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f2b4d;
        }

        .student-info-header .student-meta {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        .student-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .student-info-field {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .student-info-field label {
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #5c6e8c;
            letter-spacing: 0.03em;
        }

        .student-info-field span {
            font-size: 0.9rem;
            color: #1e293b;
        }

        /* Collapsible Referral Card */
        .ref-card.collapsed {
            display: none;
        }

        .ref-card.collapsed.show {
            display: block;
        }

        @media (max-width: 1080px) {
            .compose-card {
                position: static;
            }
        }

        @media (max-width: 780px) {
            body {
                padding: 1rem;
            }

            .header-actions,
            .queue-header,
            .badges,
            .action-row,
            .sheet-actions,
            .input-group {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-primary,
            .btn-secondary,
            .btn-outline-sm,
            .submit-btn,
            .comment-btn,
            .reply-btn,
            .status-btn {
                width: 100%;
            }

            .sheet-grid,
            .sheet-grid.cols-3,
            .sheet-grid.cols-4 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    @php
        $canManageReferrals = $canManageReferrals ?? in_array($user->role, ['yearleader', 'executive', 'ssd_assistant_1', 'ssd_assistant_2'], true);
        $canUpdateStatus = $canUpdateStatus ?? in_array($user->role, ['executive', 'ssd_assistant_1', 'ssd_assistant_2'], true);
        $canRefer = $canRefer ?? in_array($user->role, ['yearleader', 'ssd_assistant_2'], true);
        $canSubmitAbsence = false;
        $canComment = $canComment ?? $canManageReferrals;
        $studentDirectory = $studentDirectory ?? collect();
        $yearLeaderProfile = $yearLeaderProfile ?? null;
        $pendingReferrals = $referrals->filter(fn ($r) => $r->status === 'pending');
        $isAttendedReferral = function ($referral) {
            $attendanceForm = $referral->ssd_attendance_form ?? [];
            $hasAttendanceSheet = ! empty(array_filter($attendanceForm, fn ($value) => filled($value)));

            return $hasAttendanceSheet
                || filled($referral->ssd_attended_at)
                || in_array($referral->status, ['reviewed', 'resolved'], true);
        };
        $queuePendingReferrals = $referrals->reject($isAttendedReferral)->values();
        $queueAttendedReferrals = $referrals->filter($isAttendedReferral)->values();
        $queueDisplayReferrals = $queuePendingReferrals->concat($queueAttendedReferrals);
        $queueSections = [
            [
                'label' => 'Pending',
                'description' => 'Waiting for SSD review and attendance form completion.',
                'empty' => 'No pending referrals right now.',
                'referrals' => $queuePendingReferrals,
            ],
            [
                'label' => 'Attended',
                'description' => 'SSD has already attended these cases and follow-up can continue here.',
                'empty' => 'No attended referrals yet.',
                'referrals' => $queueAttendedReferrals,
            ],
        ];
        $urgentReferrals = $referrals->filter(fn ($r) => $r->priority === 'Urgent');
        $normalReferrals = $referrals->filter(fn ($r) => $r->priority === 'Normal');
        $dashboardLabel = $user->role === 'yearleader' ? 'Year Leader Referral Desk' : 'Student Support Follow-up Desk';
        $today = now()->format('Y-m-d');
        $limkokwingFaculties = config('limkokwing.faculties', []);
        $limkokwingYearsOfStudy = config('limkokwing.years_of_study', []);
        $studentProfilesById = $studentProfilesById ?? collect();
        $roleLabel = $user->role === 'yearleader' ? 'Year Leader' : ucwords(str_replace('_', ' ', $user->role));
        $userInitials = collect(preg_split('/\s+/', trim($user->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');
    @endphp

    <div class="dashboard">
        <div class="top-bar">
            <div class="logo-area">
                <h1><i class="fas fa-graduation-cap" style="color:#2c5282;"></i> SolidCare SSD</h1>
                <p>Academic Supports | {{ $dashboardLabel }}</p>
            </div>
            <div class="header-actions">
                @if ($canRefer)
                    <a href="#refer-student" class="btn-primary"><i class="fas fa-paper-plane"></i> Start Referral</a>
                @elseif ($canManageReferrals)
                    <a href="#support-queue" class="btn-primary"><i class="fas fa-user-check"></i> Attend Student Cases</a>
                    <a href="{{ route('academic.referrals.report', ['type' => 'general', 'year' => now()->year]) }}" class="btn-primary"><i class="fas fa-chart-bar"></i> Generate Report</a>
                @endif
                <a href="{{ route('home') }}" class="btn-secondary"><i class="fas fa-house"></i> Home</a>
                <div class="user-card">
                    <div class="user-avatar">{{ $userInitials }}</div>
                    <div class="user-info">
                        <strong>{{ $user->name }}</strong>
                        <small>{{ $roleLabel }} | signed in</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="desk-note">
            <strong>Academic Supports.</strong>
            Year Leaders refer students to SSD for attendance, behavior, or academic concerns. Executive & SSD assistants review cases, complete attendance forms, add follow-up notes, and update resolution status from one desk.
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-left">
                    <h4>Total Referrals</h4>
                    <div class="number">{{ $referrals->count() }}</div>
                    <small>tracked in desk</small>
                </div>
                <div class="stat-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-left">
                    <h4>Pending Review</h4>
                    <div class="number">{{ $pendingReferrals->count() }}</div>
                    <small>waiting follow-up</small>
                </div>
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-left">
                    <h4>Urgent Priority</h4>
                    <div class="number">{{ $urgentReferrals->count() }}</div>
                    <small>needs prompt SSD attention</small>
                </div>
                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-left">
                    <h4>Normal Priority</h4>
                    <div class="number">{{ $normalReferrals->count() }}</div>
                    <small>standard follow-up</small>
                </div>
                <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
            </div>
        </div>

        <div class="flash">
            @if (session('success'))
                <div class="alert alert-success mb-0">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger mb-0">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger mb-0">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            @if ($pendingReferrals->isNotEmpty() && $canManageReferrals)
                <div class="notice">
                    <strong>Attention needed:</strong> {{ $pendingReferrals->count() }} {{ \Illuminate\Support\Str::plural('student case', $pendingReferrals->count()) }} still pending review.
                    <div class="mt-2">{{ $pendingReferrals->pluck('student_name')->join(', ') }}</div>
                </div>
            @endif
        </div>

        <div class="two-columns {{ $canRefer ? '' : 'single-column' }}">
            @if ($canRefer)
                <aside id="refer-student" class="card left-panel compose-card">
                    <div class="card-header">
                        <div>
                            <h3><i class="fas fa-file-alt"></i> Official Referral Sheet</h3>
                            <div class="card-subcopy">Use the digital version of the LUCT year leader referral form so SSD receives the same full information as the paper sheet.</div>
                        </div>
                        <span class="badge-live"><i class="fas fa-circle" style="font-size:0.45rem;"></i> Live</span>
                    </div>

                    <div class="form-grid">
                        <form method="POST" action="{{ route('academic.referrals.store') }}">
                            @csrf

                            <section class="attendance-sheet">
                                <div class="sheet-head">
                                    <div>
                                        <h4 class="sheet-title">Individual Student</h4>
                                        <p class="sheet-copy">Enter the student's details to refer them to SSD. If the student already exists in SolidCare SSD, saved details can still auto-fill when the identity number matches.</p>
                                    </div>
                                    <span class="sheet-stamp">Year Leader Form</span>
                                </div>

                                <input type="hidden" id="student-user-id" name="student_user_id" value="{{ old('student_user_id') }}">

                            <div class="sheet-grid cols-3">
                                <div class="sheet-field">
                                    <label class="sheet-label">First Name</label>
                                    <input type="text" id="student-first-name" name="student_first_name" class="form-control" value="{{ old('student_first_name') }}" required>
                                </div>
                                <div class="sheet-field">
                                    <label class="sheet-label">Surname</label>
                                    <input type="text" id="student-surname" name="student_surname" class="form-control" value="{{ old('student_surname') }}" required>
                                </div>
                                <div class="sheet-field">
                                    <label class="sheet-label">Student Identity Number</label>
                                    <input type="text" id="student-identity-number" name="student_identity_number" class="form-control" value="{{ old('student_identity_number') }}" required>
                                </div>
                                <div class="sheet-field">
                                    <label class="sheet-label">Sex</label>
                                    <input type="text" id="sex-field" name="sex" class="form-control" value="{{ old('sex') }}">
                                </div>
                                <div class="sheet-field">
                                    <label class="sheet-label">Faculty</label>
                                    <select
                                        id="faculty-field"
                                        name="faculty"
                                        class="form-select"
                                        data-current-value="{{ old('faculty', optional($yearLeaderProfile)->faculty) }}"
                                    >
                                        <option value="">Select Faculty</option>
                                        @foreach ($limkokwingFaculties as $facultyKey => $faculty)
                                            <option value="{{ $faculty['label'] }}" data-faculty-key="{{ $facultyKey }}">
                                                {{ $faculty['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sheet-field">
                                    <label class="sheet-label">Programme</label>
                                    <select
                                        id="programme-field"
                                        name="programme"
                                        class="form-select"
                                        data-current-value="{{ old('programme') }}"
                                    >
                                        <option value="">Select Programme</option>
                                    </select>
                                </div>
                                <div class="sheet-field">
                                    <label class="sheet-label">Class</label>
                                    <select
                                        id="class-name"
                                        name="class_name"
                                        class="form-select"
                                        data-current-value="{{ old('class_name', optional($yearLeaderProfile)->class) }}"
                                    >
                                        <option value="">Select Class</option>
                                    </select>
                                </div>
                                <div class="sheet-field">
                                    <label class="sheet-label">Year of Study</label>
                                    <select
                                        id="year-of-study"
                                        name="year_of_study"
                                        class="form-select"
                                        data-current-value="{{ old('year_of_study', optional($yearLeaderProfile)->year) }}"
                                    >
                                        <option value="">Select Year of Study</option>
                                        @foreach ($limkokwingYearsOfStudy as $studyYear)
                                            <option value="{{ $studyYear }}">{{ $studyYear }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sheet-field">
                                    <label class="sheet-label">Contact Number</label>
                                    <input type="text" id="contact-number" name="contact_number" class="form-control" value="{{ old('contact_number') }}">
                                </div>
                                <div class="sheet-field">
                                    <label class="sheet-label">Year Leader</label>
                                    <input type="text" id="year-leader-name" name="year_leader_name" class="form-control" value="{{ old('year_leader_name', $user->name) }}" required>
                                </div>
                                <div class="sheet-field">
                                    <label class="sheet-label">Principal Lecturer</label>
                                    <input type="text" id="principal-lecturer" name="principal_lecturer" class="form-control" value="{{ old('principal_lecturer') }}">
                                </div>
                                <div class="sheet-field">
                                    <label class="sheet-label">FMG</label>
                                    <input type="text" name="fmg" class="form-control" value="{{ old('fmg') }}">
                                </div>
                                <div class="sheet-field">
                                    <label class="sheet-label">Referring Lecturer Email</label>
                                    <input type="email" id="referring-lecturer-email" name="referring_lecturer_email" class="form-control" value="{{ old('referring_lecturer_email', $user->email) }}">
                                </div>
                                <div class="sheet-field">
                                    <label class="sheet-label">Extension</label>
                                    <input type="text" name="extension" class="form-control" value="{{ old('extension') }}">
                                </div>
                                <div class="sheet-field">
                                    <label class="sheet-label">Date</label>
                                    <input type="date" name="referral_date" class="form-control" value="{{ old('referral_date', $today) }}" required>
                                </div>
                                <div class="sheet-field">
                                    <label class="sheet-label">Referral Priority</label>
                                    <select name="priority" class="form-select" required>
                                        <option value="">Select Priority</option>
                                        <option value="Urgent" @selected(old('priority') === 'Urgent')>Urgent</option>
                                        <option value="Normal" @selected(old('priority', 'Normal') === 'Normal')>Normal</option>
                                    </select>
                                </div>
                            </div>

                            <div class="sheet-section">
                                <div class="sheet-grid">
                                    <div class="sheet-field full">
                                        <label class="sheet-label">Reasons for Referral</label>
                                        <textarea name="reasons_for_referral" rows="4" class="form-control" required>{{ old('reasons_for_referral') }}</textarea>
                                    </div>
                                    <div class="sheet-field">
                                        <label class="sheet-label">When was the problem identified?</label>
                                        <input type="text" name="problem_identified_when" class="form-control" value="{{ old('problem_identified_when') }}" required>
                                    </div>
                                    <div class="sheet-field full">
                                        <label class="sheet-label">Action Taken</label>
                                        <textarea name="action_taken" rows="3" class="form-control" required>{{ old('action_taken') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="sheet-section">
                                <div class="sheet-head">
                                    <div>
                                        <h4 class="sheet-title">Group of Students</h4>
                                        <p class="sheet-copy">Complete this section when the referral covers more than one student, following the second half of the paper form.</p>
                                    </div>
                                    <span class="sheet-stamp">Optional</span>
                                </div>
                                <div class="sheet-grid">
                                    <div class="sheet-field full">
                                        <label class="sheet-label">Group's Details</label>
                                        <textarea name="group_students_details" rows="5" class="form-control" placeholder="Enter one student per line: First Name | Surname | Class | Student ID | Contact / Guardian">{{ old('group_students_details') }}</textarea>
                                    </div>
                                    <div class="sheet-field full">
                                        <label class="sheet-label">Reasons for Referral</label>
                                        <textarea name="group_reasons_for_referral" rows="3" class="form-control">{{ old('group_reasons_for_referral') }}</textarea>
                                    </div>
                                    <div class="sheet-field">
                                        <label class="sheet-label">When was the problem identified?</label>
                                        <input type="text" name="group_problem_identified_when" class="form-control" value="{{ old('group_problem_identified_when') }}">
                                    </div>
                                    <div class="sheet-field">
                                        <label class="sheet-label">Referring Lecturer / Name</label>
                                        <input type="text" name="group_referring_lecturer_name" class="form-control" value="{{ old('group_referring_lecturer_name', $user->name) }}">
                                    </div>
                                    <div class="sheet-field">
                                        <label class="sheet-label">Date</label>
                                        <input type="date" name="group_referral_date" class="form-control" value="{{ old('group_referral_date', $today) }}">
                                    </div>
                                    <div class="sheet-field full">
                                        <label class="sheet-label">Action Taken</label>
                                        <textarea name="group_action_taken" rows="3" class="form-control">{{ old('group_action_taken') }}</textarea>
                                    </div>
                                </div>
                            </div>

                                <div class="sheet-actions">
                                    <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> Submit Referral Sheet</button>
                                </div>
                                <div class="sheet-note">This form creates the student referral and keeps the year leader's referral sheet attached to the case for SSD follow-up.</div>
                            </section>
                        </form>
                    </div>
                </aside>
            @endif

            <section id="support-queue" class="card right-panel queue-card">
                <div class="card-header">
                    <div>
                        <h3><i class="fas fa-list-ul"></i> Support Queue | Referred Student Queue</h3>
                        <div class="card-subcopy">@if ($canRefer)Create referrals, review updates, and hand cases to SSD staff for follow-up from one action board.@elseif ($canManageReferrals)Review referred students, complete the SSD attendance sheet, update statuses, and add follow-up notes from one support desk.@else View submitted support entries and follow-up comments from the team.@endif</div>
                    </div>
                    <span class="count">{{ $referrals->count() }} total</span>
                </div>

                {{-- Clickable Name List --}}
                @if ($queueDisplayReferrals->isNotEmpty())
                <div class="student-name-list">
                    @foreach ($queueSections as $queueSection)
                        <section class="queue-group queue-group-{{ strtolower($queueSection['label']) }}">
                            <div class="queue-group-header">
                                <div class="queue-group-title-row">
                                    <span class="queue-group-label">{{ $queueSection['label'] }}</span>
                                    <span class="queue-group-count">{{ $queueSection['referrals']->count() }}</span>
                                </div>
                                <p class="queue-group-copy">{{ $queueSection['description'] }}</p>
                            </div>

                            @if ($queueSection['referrals']->isNotEmpty())
                                <div class="queue-group-items">
                                    @foreach ($queueSection['referrals'] as $referral)
                                        @php
                                            $priorityLower = strtolower($referral->priority ?? 'normal');
                                        @endphp
                                        <button type="button" class="student-name-item" data-student-id="{{ $referral->id }}">
                                            <span class="priority-dot {{ $priorityLower }}"></span>
                                            {{ $referral->student_name }}
                                            <span class="student-id">{{ $referral->student_id }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="queue-group-empty">{{ $queueSection['empty'] }}</div>
                            @endif
                        </section>
                    @endforeach
                </div>
                @endif

                <div class="support-queue">

                @if ($queueDisplayReferrals->isEmpty())
                    <div class="empty">
                        <div class="empty-orbit"></div>
                        <h4>Support queue is clear</h4>
                        <p>No referred students have been recorded yet. @if ($canRefer)Start with the composer to open a new referral for SSD follow-up.@else Student cases will appear here with statuses, comments, and team updates.@endif</p>
                    </div>
                @else
                    @foreach ($queueDisplayReferrals as $referral)
                        @php
                            $priorityClass = 'priority-' . strtolower($referral->priority);
                            $entryType = $referral->entry_type ?? 'referral';
                            $isAbsenceNotice = $entryType === 'absence_notice';
                            $referralForm = $referral->yearleader_referral_form ?? [];
                            $attendanceForm = $referral->ssd_attendance_form ?? [];
                            $studentProfile = $studentProfilesById->get($referral->student_user_id, []);
                            $findFacultyEntry = function ($value) use ($limkokwingFaculties) {
                                $normalizedValue = strtolower(trim((string) $value));

                                if ($normalizedValue === '') {
                                    return null;
                                }

                                foreach ($limkokwingFaculties as $facultyKey => $faculty) {
                                    $aliases = array_filter(array_merge([$facultyKey, $faculty['label']], $faculty['aliases'] ?? []));

                                    foreach ($aliases as $alias) {
                                        if (strtolower(trim((string) $alias)) === $normalizedValue) {
                                            return ['key' => $facultyKey, 'label' => $faculty['label']];
                                        }
                                    }
                                }

                                return null;
                            };
                            $nameParts = preg_split('/\s+/', trim($referral->student_name));
                            $rawFaculty = $referralForm['faculty'] ?? ($studentProfile['faculty'] ?? '');
                            $rawProgramme = $referralForm['programme'] ?? ($referral->programme ?? ($studentProfile['programme'] ?? ''));
                            $facultyFromFacultyField = $findFacultyEntry($rawFaculty);
                            $facultyFromProgrammeField = $findFacultyEntry($rawProgramme);
                            $derivedFaculty = $facultyFromFacultyField['label'] ?? $rawFaculty;
                            $derivedProgramme = $rawProgramme ?: ($studentProfile['programme'] ?? '');

                            if ($facultyFromProgrammeField && (! filled($rawFaculty) || (($facultyFromFacultyField['key'] ?? null) === $facultyFromProgrammeField['key']))) {
                                $derivedFaculty = $facultyFromFacultyField['label'] ?? $facultyFromProgrammeField['label'];
                                $derivedProgramme = $studentProfile['programme'] ?? '';
                            }

                            $derivedClassName = $referralForm['class_name'] ?? '';
                            $derivedYearOfStudy = $referralForm['year_of_study'] ?? '';
                            $derivedContactNumber = $referralForm['contact_number'] ?? ($studentProfile['contact_number'] ?? '');
                            $derivedYearLeaderName = $referralForm['year_leader_name'] ?? optional($referral->referrer)->name;
                            $derivedPrincipalLecturer = $referralForm['principal_lecturer'] ?? '';
                            $derivedFmg = $referralForm['fmg'] ?? '';
                            $derivedFieldOfStudy = $referralForm['field_of_study'] ?? $derivedProgramme;
                            $derivedSemester = $referralForm['semester'] ?? $derivedYearOfStudy;
                            $defaultFirstName = $attendanceForm['student_first_name'] ?? ($referralForm['student_first_name'] ?? ($nameParts[0] ?? ''));
                            $defaultSurname = $attendanceForm['student_surname'] ?? ($referralForm['student_surname'] ?? (count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : ''));
                            $defaultAttendanceDate = $attendanceForm['attended_on'] ?? optional($referral->ssd_attended_at)->format('Y-m-d') ?? now()->format('Y-m-d');
                            $defaultDesignation = $attendanceForm['designation'] ?? ($canUpdateStatus ? ucwords(str_replace('_', ' ', $user->role)) : '');
                            $defaultOfficerName = $attendanceForm['ssd_officer_name'] ?? ($canUpdateStatus ? $user->name : '');
                            $usingOldAttendance = (string) old('form_referral_id') === (string) $referral->id;
                            $attendanceValue = function ($key, $default = '') use ($attendanceForm, $usingOldAttendance) {
                                return $usingOldAttendance ? old($key, $attendanceForm[$key] ?? $default) : ($attendanceForm[$key] ?? $default);
                            };
                            $groupStudentRows = $usingOldAttendance
                                ? old('group_students', $attendanceForm['group_students'] ?? [])
                                : ($attendanceForm['group_students'] ?? []);

                            if (empty($groupStudentRows) && filled($attendanceValue('group_students_feedback'))) {
                                $groupStudentRows = [[
                                    'first_name' => '',
                                    'surname' => '',
                                    'feedback' => $attendanceValue('group_students_feedback'),
                                ]];
                            }

                            $groupStudentRows = array_values(array_pad($groupStudentRows, 5, [
                                'first_name' => '',
                                'surname' => '',
                                'feedback' => '',
                            ]));
                            $savedGroupStudentRows = array_values(array_filter($attendanceForm['group_students'] ?? [], function ($student) {
                                return filled($student['first_name'] ?? null)
                                    || filled($student['surname'] ?? null)
                                    || filled($student['feedback'] ?? null);
                            }));
                            $canCommentOnReferral = $canComment
                                && (
                                    in_array($user->role, ['executive', 'ssd_assistant_1', 'ssd_assistant_2'], true)
                                    || ($user->role === 'yearleader' && (int) $referral->referred_by === (int) $user->id)
                                );
                            $hasReferralSheet = ! empty(array_filter($referralForm, fn ($value) => filled($value)));
                            $hasAttendanceSheet = ! empty(array_filter($attendanceForm, fn ($value) => filled($value)));
                        @endphp
                        <article class="ref-card queue-item {{ $priorityClass }}" data-student-id="{{ $referral->id }}">
                            {{-- Student Info Panel (shown when name is clicked) --}}
                            <div class="student-info-panel" data-student-id="{{ $referral->id }}">
                                <div class="student-info-header">
                                    <div>
                                        <h4>{{ $referral->student_name }}</h4>
                                        <div class="student-meta">
                                            <i class="fas fa-id-card me-1"></i>{{ $referral->student_id }}
                                            | <i class="fas fa-graduation-cap me-1"></i>{{ $derivedProgramme ?: $referral->programme ?: 'N/A' }}
                                            | <i class="far fa-calendar-alt me-1"></i>{{ $referral->created_at->format('M j, Y g:i A') }}
                                        </div>
                                    </div>
                                    <div class="badges">
                                        <span class="status-badge status-{{ $referral->status }}">{{ ucfirst($referral->status) }}</span>
                                        <span class="priority-badge">{{ $referral->priority }}</span>
                                    </div>
                                </div>
                                <div class="student-info-grid">
                                    <div class="student-info-field">
                                        <label>First Name</label>
                                        <span>{{ $referralForm['student_first_name'] ?? $defaultFirstName ?: 'Not recorded' }}</span>
                                    </div>
                                    <div class="student-info-field">
                                        <label>Surname</label>
                                        <span>{{ $referralForm['student_surname'] ?? $defaultSurname ?: 'Not recorded' }}</span>
                                    </div>
                                    <div class="student-info-field">
                                        <label>Student ID</label>
                                        <span>{{ $referral->student_id }}</span>
                                    </div>
                                    <div class="student-info-field">
                                        <label>Sex</label>
                                        <span>{{ $referralForm['sex'] ?? 'Not recorded' }}</span>
                                    </div>
                                    <div class="student-info-field">
                                        <label>Faculty</label>
                                        <span>{{ $derivedFaculty ?: 'Not recorded' }}</span>
                                    </div>
                                    <div class="student-info-field">
                                        <label>Programme</label>
                                        <span>{{ $derivedProgramme ?: $referral->programme ?: 'Not recorded' }}</span>
                                    </div>
                                    <div class="student-info-field">
                                        <label>Class</label>
                                        <span>{{ $derivedClassName ?: 'Not recorded' }}</span>
                                    </div>
                                    <div class="student-info-field">
                                        <label>Year of Study</label>
                                        <span>{{ $derivedYearOfStudy ?: 'Not recorded' }}</span>
                                    </div>
                                    <div class="student-info-field">
                                        <label>Contact Number</label>
                                        <span>{{ $derivedContactNumber ?: 'Not recorded' }}</span>
                                    </div>
                                    <div class="student-info-field">
                                        <label>Year Leader</label>
                                        <span>{{ $derivedYearLeaderName ?: 'Not recorded' }}</span>
                                    </div>
                                    <div class="student-info-field">
                                        <label>Principal Lecturer</label>
                                        <span>{{ $derivedPrincipalLecturer ?: 'Not recorded' }}</span>
                                    </div>
                                    <div class="student-info-field">
                                        <label>Referring Lecturer</label>
                                        <span>{{ $referralForm['referring_lecturer_email'] ?? 'Not recorded' }}</span>
                                    </div>
                                    <div class="student-info-field">
                                        <label>Referral Date</label>
                                        <span>{{ $referralForm['referral_date'] ?? 'Not recorded' }}</span>
                                    </div>
                                    <div class="student-info-field">
                                        <label>Referral Reason</label>
                                        <span>{{ $referral->reason }}</span>
                                    </div>
                                    <div class="student-info-field">
                                        <label>Referred By</label>
                                        <span>{{ optional($referral->referrer)->name ?? 'Unknown User' }}</span>
                                    </div>
                                    <div class="student-info-field">
                                        <label>SSD Status</label>
                                        <span>{{ ucfirst($referral->status) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="queue-header">
                                <div>
                                    <h3 class="card-name">{{ $referral->student_name }}</h3>
                                    <div class="meta mt-2">
                                        <i class="fas fa-id-card me-1"></i>{{ $referral->student_id }}
                                        @if ($referral->programme)
                                            | <i class="fas fa-graduation-cap me-1"></i>{{ $referral->programme }}
                                        @endif
                                        | <i class="far fa-calendar-alt me-1"></i>{{ $referral->created_at->format('M j, Y g:i A') }}
                                    </div>
                                    <div class="referrer mt-2">
                                        <i class="fas fa-user me-1"></i>Referred by: {{ optional($referral->referrer)->name ?? 'Unknown User' }}
                                    </div>
                                </div>

                                <div class="badges">
                                    <span class="priority-badge">{{ $isAbsenceNotice ? 'Absence Notice' : 'Referral' }}</span>
                                    <span class="status-badge status-{{ $referral->status }}">{{ ucfirst($referral->status) }}</span>
                                    <span class="priority-badge">{{ $referral->priority }}</span>
                                </div>
                            </div>

                            <div class="reason">
                                <strong>{{ $isAbsenceNotice ? 'Absence Details' : 'Referral Reason' }}</strong>
                                <p>{{ $referral->reason }}</p>
                            </div>

                            @if ($hasReferralSheet)
                                <section class="attendance-sheet">
                                    <div class="sheet-head">
                                        <div>
                                            <h4 class="sheet-title">Year Leader Referral Form</h4>
                                            <p class="sheet-copy">Official referral details captured by the year leader before this case was sent to SSD.</p>
                                        </div>
                                        <span class="sheet-stamp">Referral Saved</span>
                                    </div>
                                    <div class="sheet-grid cols-3">
                                        <div class="sheet-read"><strong>First Name</strong><span>{{ $referralForm['student_first_name'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Surname</strong><span>{{ $referralForm['student_surname'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Student Identity Number</strong><span>{{ $referralForm['student_identity_number'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Sex</strong><span>{{ $referralForm['sex'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Faculty</strong><span>{{ $derivedFaculty ?: 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Programme</strong><span>{{ $derivedProgramme ?: 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Class</strong><span>{{ $derivedClassName ?: 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Year of Study</strong><span>{{ $derivedYearOfStudy ?: 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Contact Number</strong><span>{{ $derivedContactNumber ?: 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Year Leader</strong><span>{{ $derivedYearLeaderName ?: 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Principal Lecturer</strong><span>{{ $derivedPrincipalLecturer ?: 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>FMG</strong><span>{{ $referralForm['fmg'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Referring Lecturer Email</strong><span>{{ $referralForm['referring_lecturer_email'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Extension</strong><span>{{ $referralForm['extension'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Date</strong><span>{{ $referralForm['referral_date'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Problem Identified</strong><span>{{ $referralForm['problem_identified_when'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read" style="grid-column:1/-1;"><strong>Reasons for Referral</strong><p>{{ $referralForm['reasons_for_referral'] ?? 'Not recorded' }}</p></div>
                                        <div class="sheet-read" style="grid-column:1/-1;"><strong>Action Taken</strong><p>{{ $referralForm['action_taken'] ?? 'Not recorded' }}</p></div>
                                        <div class="sheet-read" style="grid-column:1/-1;"><strong>Group's Details</strong><p>{{ $referralForm['group_students_details'] ?? 'Not recorded' }}</p></div>
                                        <div class="sheet-read" style="grid-column:1/-1;"><strong>Group Reasons for Referral</strong><p>{{ $referralForm['group_reasons_for_referral'] ?? 'Not recorded' }}</p></div>
                                        <div class="sheet-read"><strong>Group Problem Identified</strong><span>{{ $referralForm['group_problem_identified_when'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Group Referring Lecturer / Name</strong><span>{{ $referralForm['group_referring_lecturer_name'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Group Date</strong><span>{{ $referralForm['group_referral_date'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read" style="grid-column:1/-1;"><strong>Group Action Taken</strong><p>{{ $referralForm['group_action_taken'] ?? 'Not recorded' }}</p></div>
                                    </div>
                                </section>
                            @endif

                            <section class="attendance-sheet">
                                <div class="sheet-head">
                                    <div>
                                        <h4 class="sheet-title">SSD Attendance Form</h4>
                                        <p class="sheet-copy">Digital version of the referral feedback sheet used by SSD staff to attend, document, and follow up on referred students.</p>
                                    </div>
                                    <span class="sheet-stamp">{{ $hasAttendanceSheet ? 'Form Saved' : 'Awaiting SSD Form' }}</span>
                                </div>

                                @if ($canUpdateStatus)
                                    <form method="POST" action="{{ route('academic.referrals.attendance', $referral) }}">
                                        @csrf
                                        <input type="hidden" name="form_referral_id" value="{{ $referral->id }}">

                                        <div class="paper-feedback-form">
                                            <div class="paper-code">LUCT Lesotho SSD/FF 1</div>
                                            <div class="paper-institution-name">
                                                Limkokwing University
                                                <span>of Creative Technology</span>
                                                <span>Lesotho</span>
                                            </div>
                                            <div class="paper-title">
                                                <strong>Limkokwing University of Creative Technology, Lesotho</strong>
                                                <strong>LUCT Student's Referral Feedback Form from the Student Services Department</strong>
                                            </div>

                                            <div class="paper-section-label">Individual Student:</div>
                                            <div class="paper-row">
                                                <div class="paper-field">
                                                    <label>Student Details: First Name</label>
                                                    <input type="text" name="student_first_name" class="form-control" value="{{ $attendanceValue('student_first_name', $defaultFirstName) }}" required>
                                                </div>
                                                <div class="paper-field">
                                                    <label>Surname</label>
                                                    <input type="text" name="student_surname" class="form-control" value="{{ $attendanceValue('student_surname', $defaultSurname) }}">
                                                </div>
                                                <div class="paper-field small">
                                                    <label>Sex</label>
                                                    <input type="text" name="sex" class="form-control" value="{{ $attendanceValue('sex', $referralForm['sex'] ?? '') }}">
                                                </div>
                                            </div>
                                            <div class="paper-row">
                                                <div class="paper-field wide">
                                                    <label>Student Identity Number</label>
                                                    <input type="text" name="student_identity_number" class="form-control" value="{{ $attendanceValue('student_identity_number', $referralForm['student_identity_number'] ?? $referral->student_id) }}" required>
                                                </div>
                                                <div class="paper-field small">
                                                    <label>Class</label>
                                                    <input type="text" name="class_name" class="form-control" value="{{ $attendanceValue('class_name', $derivedClassName) }}">
                                                </div>
                                                <div class="paper-field small">
                                                    <label>Programme</label>
                                                    <input type="text" name="programme" class="form-control" value="{{ $attendanceValue('programme', $derivedProgramme) }}">
                                                </div>
                                            </div>
                                            <div class="paper-row">
                                                <div class="paper-field">
                                                    <label>Faculty</label>
                                                    <input type="text" name="faculty" class="form-control" value="{{ $attendanceValue('faculty', $derivedFaculty) }}">
                                                </div>
                                                <div class="paper-field tiny">
                                                    <label>Year of Study</label>
                                                    <input type="text" name="year_of_study" class="form-control" value="{{ $attendanceValue('year_of_study', $derivedYearOfStudy) }}">
                                                </div>
                                                <div class="paper-field tiny">
                                                    <label>Semester</label>
                                                    <input type="text" name="semester" class="form-control" value="{{ $attendanceValue('semester', $derivedSemester) }}">
                                                </div>
                                                <div class="paper-field">
                                                    <label>Contact Number</label>
                                                    <input type="text" name="contact_number" class="form-control" value="{{ $attendanceValue('contact_number', $derivedContactNumber) }}">
                                                </div>
                                            </div>
                                            <div class="paper-row">
                                                <div class="paper-field">
                                                    <label>Year Leader</label>
                                                    <input type="text" name="year_leader_name" class="form-control" value="{{ $attendanceValue('year_leader_name', $derivedYearLeaderName) }}">
                                                </div>
                                                <div class="paper-field">
                                                    <label>Principal Lecturer</label>
                                                    <input type="text" name="principal_lecturer" class="form-control" value="{{ $attendanceValue('principal_lecturer', $derivedPrincipalLecturer) }}">
                                                </div>
                                                <div class="paper-field">
                                                    <label>FMG</label>
                                                    <input type="text" name="fmg" class="form-control" value="{{ $attendanceValue('fmg', $derivedFmg) }}">
                                                    <input type="hidden" name="field_of_study" value="{{ $attendanceValue('field_of_study', $derivedFieldOfStudy) }}">
                                                </div>
                                            </div>

                                            <div class="paper-section-label">Feedback</div>
                                            <textarea name="feedback" rows="6" class="form-control" required>{{ $attendanceValue('feedback') }}</textarea>

                                            <div class="paper-section-label">Group of Students</div>
                                            <div class="paper-field full">
                                                <label>Group's Details</label>
                                                <input type="text" name="group_problems" class="form-control" value="{{ $attendanceValue('group_problems') }}">
                                            </div>
                                            <table class="paper-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width:22%;">First Name</th>
                                                        <th style="width:22%;">Surname</th>
                                                        <th>Feedback as obtained from the student</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($groupStudentRows as $rowIndex => $groupStudent)
                                                        <tr>
                                                            <td>
                                                                <input type="text" name="group_students[{{ $rowIndex }}][first_name]" class="form-control" value="{{ $groupStudent['first_name'] ?? '' }}">
                                                            </td>
                                                            <td>
                                                                <input type="text" name="group_students[{{ $rowIndex }}][surname]" class="form-control" value="{{ $groupStudent['surname'] ?? '' }}">
                                                            </td>
                                                            <td>
                                                                <textarea name="group_students[{{ $rowIndex }}][feedback]" rows="2" class="form-control group-feedback">{{ $groupStudent['feedback'] ?? '' }}</textarea>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>

                                            <div class="paper-section-label">Action Taken</div>
                                            <textarea name="action_taken" rows="3" class="form-control" required>{{ $attendanceValue('action_taken') }}</textarea>

                                            <div class="paper-section-label">Plan of Action</div>
                                            <textarea name="plan_of_action" rows="3" class="form-control">{{ $attendanceValue('plan_of_action') }}</textarea>

                                            <div class="paper-row" style="margin-top: 1rem;">
                                                <div class="paper-field wide">
                                                    <label>SSD Officer's Name</label>
                                                    <input type="text" name="ssd_officer_name" class="form-control" value="{{ $attendanceValue('ssd_officer_name', $defaultOfficerName) }}" required>
                                                </div>
                                                <div class="paper-field wide">
                                                    <label>Designation</label>
                                                    <input type="text" name="designation" class="form-control" value="{{ $attendanceValue('designation', $defaultDesignation) }}">
                                                </div>
                                                <div class="paper-field tiny">
                                                    <label>Date</label>
                                                    <input type="date" name="attended_on" class="form-control" value="{{ $attendanceValue('attended_on', $defaultAttendanceDate) }}" required>
                                                </div>
                                            </div>
                                            <div class="sheet-actions">
                                                <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Save SSD Attendance Form</button>
                                                <button type="button" class="btn-secondary" onclick="closeStudentDetails()"><i class="fas fa-times"></i> Done</button>
                                            </div>
                                            <div class="sheet-note">Saving this form marks a pending referral as reviewed and keeps the paper-style attendance details attached to the student case.</div>
                                        </div>
                                    </form>
                                @elseif ($hasAttendanceSheet)
                                    <div class="sheet-grid">
                                        <div class="sheet-read"><strong>First Name</strong><span>{{ $attendanceForm['student_first_name'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Surname</strong><span>{{ $attendanceForm['student_surname'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Class</strong><span>{{ $attendanceForm['class_name'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Programme</strong><span>{{ $attendanceForm['programme'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Sex</strong><span>{{ $attendanceForm['sex'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Student Identity Number</strong><span>{{ $attendanceForm['student_identity_number'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Faculty</strong><span>{{ $attendanceForm['faculty'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Year Leader</strong><span>{{ $attendanceForm['year_leader_name'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Principal Lecturer</strong><span>{{ $attendanceForm['principal_lecturer'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>FMG</strong><span>{{ $attendanceForm['fmg'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Year of Study</strong><span>{{ $attendanceForm['year_of_study'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Semester</strong><span>{{ $attendanceForm['semester'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Contact No.</strong><span>{{ $attendanceForm['contact_number'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>SSD Officer's Name</strong><span>{{ $attendanceForm['ssd_officer_name'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Designation</strong><span>{{ $attendanceForm['designation'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read"><strong>Date</strong><span>{{ $attendanceForm['attended_on'] ?? 'Not recorded' }}</span></div>
                                        <div class="sheet-read" style="grid-column:1/-1;"><strong>Feedback</strong><p>{{ $attendanceForm['feedback'] ?? 'Not recorded' }}</p></div>
                                        <div class="sheet-read" style="grid-column:1/-1;"><strong>Group Problems</strong><p>{{ $attendanceForm['group_problems'] ?? 'Not recorded' }}</p></div>
                                        <div class="sheet-read" style="grid-column:1/-1;">
                                            <strong>Group of Students / Feedback as Obtained</strong>
                                            @if (! empty($savedGroupStudentRows))
                                                <table class="read-table">
                                                    <thead>
                                                        <tr>
                                                            <th style="width:22%;">First Name</th>
                                                            <th style="width:22%;">Surname</th>
                                                            <th>Feedback as obtained from the student</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($savedGroupStudentRows as $groupStudent)
                                                            <tr>
                                                                <td>{{ $groupStudent['first_name'] ?: 'Not recorded' }}</td>
                                                                <td>{{ $groupStudent['surname'] ?: 'Not recorded' }}</td>
                                                                <td>{{ $groupStudent['feedback'] ?: 'Not recorded' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <p>{{ $attendanceForm['group_students_feedback'] ?? 'Not recorded' }}</p>
                                            @endif
                                        </div>
                                        <div class="sheet-read" style="grid-column:1/-1;"><strong>Action Taken</strong><p>{{ $attendanceForm['action_taken'] ?? 'Not recorded' }}</p></div>
                                        <div class="sheet-read" style="grid-column:1/-1;"><strong>Plan of Action</strong><p>{{ $attendanceForm['plan_of_action'] ?? 'Not recorded' }}</p></div>
                                    </div>
                                @else
                                    <div class="sheet-note">SSD staff have not completed the attendance sheet for this student yet. Once they attend the case, the full form will appear here for follow-up.</div>
                                @endif
                            </section>

                            @if ($canUpdateStatus)
                                <div class="action-row mt-3">
                                    <form method="POST" action="{{ route('academic.referrals.status', $referral) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="reviewed">
                                        <button type="submit" class="status-btn review-btn">
                                            <i class="fas fa-eye"></i> Mark Reviewed
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('academic.referrals.status', $referral) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="resolved">
                                        <button type="submit" class="status-btn resolve-btn">
                                            <i class="fas fa-check-circle"></i> Mark Resolved
                                        </button>
                                    </form>
                                </div>
                            @elseif ($canRefer)
                                <div class="time mt-3">SSD staff review and resolve referred student cases after submission.</div>
                            @endif

                            <div class="conv">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <strong><i class="fas fa-comments me-2"></i>Follow-up Notes</strong>
                                    <small class="time">{{ $referral->comments->count() }} note{{ $referral->comments->count() === 1 ? '' : 's' }} recorded</small>
                                </div>

                                @if ($canCommentOnReferral)
                                    <form method="POST" action="{{ route('academic.referrals.comment', $referral) }}" class="mb-3">
                                        @csrf
                                        <textarea name="message" rows="2" class="form-control mb-2" placeholder="{{ $isAbsenceNotice ? 'Add an absence review note...' : 'Add a case update or follow-up note...' }}" required></textarea>
                                        <button type="submit" class="comment-btn">
                                            <i class="fas fa-comment-dots"></i> Save Note
                                        </button>
                                    </form>
                                @endif

                                @forelse ($referral->comments as $comment)
                                    <div class="comment">
                                        <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                            <strong>{{ optional($comment->user)->name ?? 'Support Staff' }}</strong>
                                            <small class="time">{{ $comment->created_at->format('M j, Y g:i A') }}</small>
                                        </div>
                                        <p class="mb-2">{{ $comment->message }}</p>

                                        @foreach ($comment->replies as $reply)
                                            <div class="reply">
                                                <div class="d-flex justify-content-between flex-wrap gap-2 mb-1">
                                                    <strong>{{ optional($reply->user)->name ?? 'Support Staff' }}</strong>
                                                    <small class="time">{{ $reply->created_at->format('M j, Y g:i A') }}</small>
                                                </div>
                                                <p class="mb-0">{{ $reply->message }}</p>
                                            </div>
                                        @endforeach

                                        @if ($canCommentOnReferral)
                                            <form method="POST" action="{{ route('academic.referrals.comment', $referral) }}" class="mt-3">
                                                @csrf
                                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                                <div class="input-group">
                                                    <input type="text" name="message" class="form-control" placeholder="Reply to this comment..." required>
                                                    <button type="submit" class="reply-btn">Reply</button>
                                                </div>
                                            </form>
                                        @endif
                                    </div>
                                @empty
                                    <div class="time">No follow-up notes yet for this referral.</div>
                                @endforelse
                            </div>
                        </article>
                    @endforeach
                @endif
                </div>
            </section>
        </div>

        <footer class="footer-note">&copy; {{ date('Y') }} SolidCare SSD. All rights reserved. | Academic Support Module</footer>
    </div>

    @if ($canRefer)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const students = @json($studentDirectory->keyBy('id'));
                const faculties = @json($limkokwingFaculties);
                const yearsOfStudy = @json($limkokwingYearsOfStudy);
                const studentUserIdField = document.getElementById('student-user-id');
                const studentIdentityField = document.getElementById('student-identity-number');
                const firstNameField = document.getElementById('student-first-name');
                const surnameField = document.getElementById('student-surname');
                const facultyField = document.getElementById('faculty-field');
                const programmeField = document.getElementById('programme-field');
                const yearOfStudyField = document.getElementById('year-of-study');
                const classField = document.getElementById('class-name');

                const fieldMap = {
                    'student-first-name': 'first_name',
                    'student-surname': 'surname',
                    'student-identity-number': 'student_identity_number',
                    'contact-number': 'contact_number',
                };

                const normalizeValue = (value) => (value || '').toString().trim().toLowerCase();
                const studentRecords = Object.values(students || {});
                const studentsByIdentity = studentRecords.reduce((lookup, student) => {
                    const identity = normalizeValue(student.student_identity_number);

                    if (identity) {
                        lookup[identity] = student;
                    }

                    return lookup;
                }, {});

                const setFieldValue = (fieldId, value, force = false) => {
                    const field = document.getElementById(fieldId);

                    if (! field) {
                        return;
                    }

                    if (force || ! field.value) {
                        field.value = value || '';
                    }
                };

                const optionExists = (field, value) => Array.from(field.options).some((option) => normalizeValue(option.value) === normalizeValue(value));

                const appendOption = (field, value, label, selected = false) => {
                    const option = document.createElement('option');
                    option.value = value;
                    option.textContent = label;
                    option.selected = selected;
                    field.appendChild(option);
                };

                const ensureOption = (field, value) => {
                    if (! field || ! value || optionExists(field, value)) {
                        return;
                    }

                    appendOption(field, value, value);
                };

                const findFacultyEntry = (value) => {
                    const normalizedValue = normalizeValue(value);

                    if (! normalizedValue) {
                        return null;
                    }

                    return Object.entries(faculties).find(([facultyKey, faculty]) => {
                        const aliases = [facultyKey, faculty.label].concat(faculty.aliases || []);

                        return aliases.some((alias) => normalizeValue(alias) === normalizedValue);
                    }) || null;
                };

                const normalizeYearOfStudy = (value) => {
                    const normalizedValue = normalizeValue(value);

                    if (! normalizedValue) {
                        return '';
                    }

                    const directMatch = yearsOfStudy.find((studyYear) => normalizeValue(studyYear) === normalizedValue);

                    if (directMatch) {
                        return directMatch;
                    }

                    const yearMap = {
                        '1': 'Year 1',
                        'year 1': 'Year 1',
                        'first year': 'Year 1',
                        '2': 'Year 2',
                        'year 2': 'Year 2',
                        'second year': 'Year 2',
                        '3': 'Year 3',
                        'year 3': 'Year 3',
                        'third year': 'Year 3',
                        '4': 'Year 4',
                        'year 4': 'Year 4',
                        'fourth year': 'Year 4',
                    };

                    return yearMap[normalizedValue] || value;
                };

                const findStudentRecord = () => {
                    const typedIdentity = normalizeValue(studentIdentityField ? studentIdentityField.value : '');

                    if (typedIdentity && studentsByIdentity[typedIdentity]) {
                        return studentsByIdentity[typedIdentity];
                    }

                    const typedFirstName = normalizeValue(firstNameField ? firstNameField.value : '');
                    const typedSurname = normalizeValue(surnameField ? surnameField.value : '');

                    if (typedFirstName || typedSurname) {
                        const combinedName = [typedFirstName, typedSurname].filter(Boolean).join(' ');

                        return studentRecords.find((student) => {
                            const studentFirstName = normalizeValue(student.first_name);
                            const studentSurname = normalizeValue(student.surname);
                            const studentFullName = normalizeValue(student.name);

                            return (
                                (typedFirstName && typedSurname && studentFirstName === typedFirstName && studentSurname === typedSurname) ||
                                (combinedName && studentFullName === combinedName)
                            );
                        }) || null;
                    }

                    return null;
                };

                const populateProgrammeOptions = (facultyValue, selectedProgramme = null) => {
                    if (! programmeField) {
                        return;
                    }

                    const facultyEntry = findFacultyEntry(facultyValue);
                    const programmes = facultyEntry ? facultyEntry[1].programmes || [] : [];
                    const programmeToSelect = selectedProgramme === null
                        ? (programmeField.dataset.currentValue || programmeField.value || '')
                        : (selectedProgramme || '');

                    programmeField.innerHTML = '<option value="">Select Programme</option>';

                    programmes.forEach((programme) => {
                        appendOption(programmeField, programme, programme, normalizeValue(programme) === normalizeValue(programmeToSelect));
                    });

                    ensureOption(programmeField, programmeToSelect);

                    if (programmeToSelect) {
                        programmeField.value = programmeToSelect;
                    }
                };

                const populateClassOptions = (selectedClass = null) => {
                    if (! classField) {
                        return;
                    }

                    const programmeValue = programmeField ? programmeField.value : '';
                    const yearValue = yearOfStudyField ? normalizeYearOfStudy(yearOfStudyField.value) : '';
                    const generatedClasses = [];

                    classField.innerHTML = '<option value="">Select Class</option>';

                    if (programmeValue && yearValue) {
                        generatedClasses.push(`${programmeValue} - ${yearValue}`);
                    }

                    if (programmeValue) {
                        yearsOfStudy.forEach((studyYear) => {
                            generatedClasses.push(`${programmeValue} - ${studyYear}`);
                        });
                    }

                    [...new Set(generatedClasses)].forEach((classOption) => {
                        appendOption(classField, classOption, classOption, normalizeValue(classOption) === normalizeValue(selectedClass));
                    });

                    const classToSelect = selectedClass === null
                        ? (classField.dataset.currentValue || classField.value || '')
                        : (selectedClass || '');
                    ensureOption(classField, classToSelect);

                    if (classToSelect) {
                        classField.value = classToSelect;
                    }
                };

                const syncFacultyField = (value) => {
                    if (! facultyField) {
                        return '';
                    }

                    const facultyEntry = findFacultyEntry(value);
                    const facultyValue = facultyEntry ? facultyEntry[1].label : (value || '');

                    ensureOption(facultyField, facultyValue);
                    facultyField.value = facultyValue;

                    return facultyValue;
                };

                const syncYearOfStudyField = (value) => {
                    if (! yearOfStudyField) {
                        return '';
                    }

                    const normalizedYear = normalizeYearOfStudy(value);

                    ensureOption(yearOfStudyField, normalizedYear);
                    yearOfStudyField.value = normalizedYear;

                    return normalizedYear;
                };

                const initializeAcademicDropdowns = () => {
                    const facultyValue = facultyField ? (facultyField.dataset.currentValue || facultyField.value) : '';
                    const programmeValue = programmeField ? (programmeField.dataset.currentValue || programmeField.value) : '';
                    const yearValue = yearOfStudyField ? (yearOfStudyField.dataset.currentValue || yearOfStudyField.value) : '';
                    const classValue = classField ? (classField.dataset.currentValue || classField.value) : '';

                    syncFacultyField(facultyValue);
                    populateProgrammeOptions(facultyValue, programmeValue);
                    syncYearOfStudyField(yearValue);
                    populateClassOptions(classValue);
                };

                const applyStudentRecord = (force = false) => {
                    const selectedStudent = findStudentRecord();

                    if (! selectedStudent) {
                        if (studentUserIdField) {
                            studentUserIdField.value = '';
                        }

                        return;
                    }

                    if (studentUserIdField) {
                        studentUserIdField.value = selectedStudent.id || '';
                    }

                    Object.entries(fieldMap).forEach(([fieldId, studentKey]) => {
                        setFieldValue(fieldId, selectedStudent[studentKey], force);
                    });

                    if (facultyField) {
                        const currentFaculty = facultyField.value || facultyField.dataset.currentValue || '';
                        const nextFaculty = selectedStudent.faculty || currentFaculty;

                        syncFacultyField(nextFaculty);
                        populateProgrammeOptions(nextFaculty, force ? (selectedStudent.programme || '') : (programmeField.value || selectedStudent.programme || null));
                    }

                    if (yearOfStudyField) {
                        syncYearOfStudyField(yearOfStudyField.value || yearOfStudyField.dataset.currentValue || '');
                    }

                    populateClassOptions(classField ? classField.value : null);
                };

                if (facultyField) {
                    facultyField.addEventListener('change', function () {
                        if (programmeField) {
                            programmeField.dataset.currentValue = '';
                        }

                        if (classField) {
                            classField.dataset.currentValue = '';
                        }

                        populateProgrammeOptions(this.value, '');
                        populateClassOptions('');
                    });
                }

                if (programmeField) {
                    programmeField.addEventListener('change', function () {
                        const suggestedClass = this.value && yearOfStudyField && yearOfStudyField.value
                            ? `${this.value} - ${yearOfStudyField.value}`
                            : '';

                        if (classField) {
                            classField.dataset.currentValue = '';
                        }

                        populateClassOptions(suggestedClass);
                    });
                }

                if (yearOfStudyField) {
                    yearOfStudyField.addEventListener('change', function () {
                        syncYearOfStudyField(this.value);
                        if (classField) {
                            classField.dataset.currentValue = '';
                        }

                        populateClassOptions(programmeField && programmeField.value ? `${programmeField.value} - ${yearOfStudyField.value}` : '');
                    });
                }

                if (studentIdentityField) {
                    studentIdentityField.addEventListener('change', function () {
                        applyStudentRecord(true);
                    });

                    studentIdentityField.addEventListener('blur', function () {
                        applyStudentRecord(true);
                    });
                }

                if (firstNameField) {
                    firstNameField.addEventListener('blur', function () {
                        applyStudentRecord(false);
                    });
                }

                if (surnameField) {
                    surnameField.addEventListener('blur', function () {
                        applyStudentRecord(false);
                    });
                }

                initializeAcademicDropdowns();

                applyStudentRecord(false);
            });
        </script>
    @endif

    {{-- Clickable Name List JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const nameItems = document.querySelectorAll('.student-name-item');
            const infoPanels = document.querySelectorAll('.student-info-panel');
            const refCards = document.querySelectorAll('.ref-card');

            // Initially hide all referral cards and info panels (show only name list)
            refCards.forEach(card => {
                card.classList.add('collapsed');
            });
            infoPanels.forEach(panel => {
                panel.classList.remove('show');
            });

            nameItems.forEach(item => {
                item.addEventListener('click', function () {
                    const studentId = this.dataset.studentId;
                    
                    // Toggle active state
                    const wasActive = this.classList.contains('active');
                    
                    // Remove active from all items
                    nameItems.forEach(i => i.classList.remove('active'));
                    
                    if (!wasActive) {
                        // Activate clicked item
                        this.classList.add('active');
                        
                        // Show corresponding info panel
                        infoPanels.forEach(panel => {
                            if (panel.dataset.studentId === studentId) {
                                panel.classList.add('show');
                            } else {
                                panel.classList.remove('show');
                            }
                        });
                        
                        // Show corresponding referral card
                        refCards.forEach(card => {
                            if (card.dataset.studentId === studentId) {
                                card.classList.add('show');
                                card.classList.remove('collapsed');
                            } else {
                                card.classList.add('collapsed');
                                card.classList.remove('show');
                            }
                        });
                    } else {
                        // Hide all panels and cards when clicking active item (return to name list only)
                        infoPanels.forEach(panel => panel.classList.remove('show'));
                        refCards.forEach(card => {
                            card.classList.add('collapsed');
                            card.classList.remove('show');
                        });
                    }
                });
            });

            // Function to close student details and return to name list
            window.closeStudentDetails = function() {
                // Remove active state from all name items
                nameItems.forEach(i => i.classList.remove('active'));
                
                // Hide all info panels
                infoPanels.forEach(panel => panel.classList.remove('show'));
                
                // Hide all referral cards
                refCards.forEach(card => {
                    card.classList.add('collapsed');
                    card.classList.remove('show');
                });
            };
        });
    </script>
</body>
</html>
