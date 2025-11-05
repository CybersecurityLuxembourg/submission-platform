<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Submission {{ $submission['id'] }} - {{ $form->title }}</title>
    <meta name="author" content="NC3 Luxembourg - National Cybersecurity Competence Center"/>
    <meta name="subject" content="Form Submission Report"/>
    <style>
        /* ========================================
           MODERN PDF DESIGN - NC3 SUBMISSION PLATFORM
           Following best practices for professional documents
           ======================================== */
        
        /* Base Styling */
        @page {
            margin: 2cm 1.5cm;
            @bottom-right {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 9pt;
                color: #6b7280;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 10.5pt;
            line-height: 1.65;
            color: #1f2937;
            background: #ffffff;
        }

        /* Typography Hierarchy */
        h1, h2, h3, h4 {
            font-weight: 600;
            line-height: 1.3;
            margin-bottom: 0.5em;
        }

        h1 { font-size: 24pt; color: #111827; }
        h2 { font-size: 18pt; color: #1f2937; }
        h3 { font-size: 14pt; color: #374151; }
        h4 { font-size: 12pt; color: #4b5563; }

        /* Professional Header with Branding */
        .document-header {
            margin-bottom: 35px;
            padding-bottom: 20px;
            border-bottom: 3px solid #2563eb;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .logo-section {
            display: table-cell;
            width: 40%;
            vertical-align: middle;
        }

        .logo-text {
            font-size: 16pt;
            font-weight: 700;
            color: #2563eb;
            letter-spacing: -0.5px;
        }

        .logo-subtext {
            font-size: 8pt;
            color: #6b7280;
            margin-top: 3px;
        }

        .header-info {
            display: table-cell;
            width: 60%;
            vertical-align: middle;
            text-align: right;
        }

        .form-title {
            font-size: 20pt;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .document-type {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 9pt;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* Enhanced Submission Info Card */
        .submission-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-cell:last-child {
            text-align: right;
        }

        .info-label {
            font-size: 9pt;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 11pt;
            color: #111827;
            font-weight: 600;
        }

        .submission-id-badge {
            display: inline-block;
            background: #2563eb;
            color: #ffffff;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 11pt;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .status-badge {
            display: inline-block;
            background: #10b981;
            color: #ffffff;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: 600;
        }

        /* Enhanced Category Sections */
        .category-section {
            margin-bottom: 35px;
            page-break-inside: avoid;
        }

        .category-header {
            background: linear-gradient(90deg, #2563eb 0%, #3b82f6 100%);
            color: #ffffff;
            padding: 12px 18px;
            border-radius: 6px 6px 0 0;
            margin-bottom: 0;
        }

        .category-title {
            font-size: 14pt;
            font-weight: 700;
            margin: 0;
        }

        .category-body {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 6px 6px;
            padding: 18px;
        }

        .category-description {
            background: #fff;
            border-left: 4px solid #fbbf24;
            padding: 12px 16px;
            margin-bottom: 20px;
            color: #78716c;
            font-style: italic;
            font-size: 10pt;
            border-radius: 4px;
        }

        /* Bullet point styling */
        ul {
            padding-left: 25px;
            margin: 10px 0;
        }
        
        li {
            margin-bottom: 6px;
            line-height: 1.6;
        }

        /* Enhanced Field Display */
        .field-container {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 14px 16px;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        .field-header {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .field-label {
            display: table-cell;
            font-weight: 600;
            color: #374151;
            font-size: 10.5pt;
        }

        .field-type-indicator {
            display: table-cell;
            text-align: right;
            font-size: 8pt;
            color: #9ca3af;
            font-style: italic;
            width: 100px;
        }

        .field-content {
            color: #1f2937;
            font-size: 10.5pt;
            line-height: 1.65;
        }

        /* Field Type-Specific Styling */
        .field-value-text {
            padding: 8px 12px;
            background: #f9fafb;
            border-left: 3px solid #cbd5e1;
            border-radius: 4px;
        }

        .field-value-textarea {
            white-space: pre-wrap;
            background: #f9fafb;
            padding: 12px;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
            font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
            font-size: 9.5pt;
            line-height: 1.6;
        }

        .field-value-checkbox {
            display: inline-block;
            padding: 4px 12px;
            background: #10b981;
            color: #ffffff;
            border-radius: 4px;
            font-weight: 600;
            font-size: 9pt;
        }

        .field-value-checkbox-no {
            background: #ef4444;
        }

        .field-value-file {
            display: inline-block;
            background: #eff6ff;
            color: #1e40af;
            padding: 8px 14px;
            border-radius: 6px;
            border: 1px solid #bfdbfe;
            font-weight: 600;
            font-size: 10pt;
        }

        .file-icon {
            display: inline-block;
            margin-right: 6px;
        }

        .field-value-select {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 600;
            border: 1px solid #fde68a;
        }

        .empty-value {
            color: #9ca3af;
            font-style: italic;
            font-size: 9.5pt;
        }

        /* Professional Footer */
        .document-footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }

        .footer-content {
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .footer-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }

        .footer-section {
            margin-bottom: 12px;
        }

        .footer-heading {
            font-size: 9pt;
            font-weight: 700;
            color: #374151;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .footer-text {
            font-size: 8.5pt;
            color: #6b7280;
            line-height: 1.5;
        }

        .footer-link {
            color: #2563eb;
            text-decoration: none;
        }

        .security-notice {
            background: #fef3c7;
            border: 1px solid #fde68a;
            padding: 12px;
            border-radius: 6px;
            margin-top: 20px;
            text-align: center;
        }

        .security-text {
            font-size: 8pt;
            color: #92400e;
            font-weight: 600;
        }

        /* Responsive Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        table.data-table td {
            vertical-align: top;
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Accessibility & Print Optimization */
        strong, b {
            font-weight: 600;
            color: #111827;
        }

        em, i {
            font-style: italic;
            color: #4b5563;
        }

        a {
            color: #2563eb;
            text-decoration: underline;
        }

        /* Page break control */
        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

<!-- Document Header -->
<div class="document-header">
    <div class="header-content">
        <div class="logo-section">
            <div class="logo-text">NC3 Luxembourg</div>
            <div class="logo-subtext">National Cybersecurity Competence Center</div>
        </div>
        <div class="header-info">
            <div class="form-title">{{ $form->title }}</div>
            <span class="document-type">SUBMISSION REPORT</span>
        </div>
    </div>
</div>

<!-- Submission Information Card -->
<div class="submission-card no-break">
    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell">
                <div class="info-label">Submission ID</div>
                <div class="info-value">
                    <span class="submission-id-badge">#{{ $submission['id'] }}</span>
                </div>
            </div>
            <div class="info-cell">
                <div class="info-label">Status</div>
                <div class="info-value">
                    <span class="status-badge">SUBMITTED</span>
                </div>
            </div>
        </div>
        <div class="info-row">
            <div class="info-cell">
                <div class="info-label">Submitted On</div>
                <div class="info-value">{{ $submission['created_at'] }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Generated On</div>
                <div class="info-value">{{ now()->format('Y-m-d H:i:s') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Form Categories and Fields -->
@foreach($submission['categories'] as $category)
    <div class="category-section">
        <div class="category-header">
            <h3 class="category-title">{{ $category['name'] }}</h3>
        </div>
        <div class="category-body">
            @if($category['description'])
                <div class="category-description">
                    {!! \App\Helpers\MarkdownHelper::toHtml($category['description']) !!}
                </div>
            @endif

            @foreach($category['fields'] as $field)
                <div class="field-container">
                    <div class="field-header">
                        <div class="field-label">{{ $field['label'] }}</div>
                        <div class="field-type-indicator">{{ ucfirst($field['type']) }}</div>
                    </div>
                    <div class="field-content">
                        @if($field['type'] === 'file')
                            @if($field['displayValue'])
                                <div class="field-value-file">
                                    <span class="file-icon">📄</span>
                                    {{ basename($field['displayValue']) }}
                                </div>
                            @else
                                <span class="empty-value">No file uploaded</span>
                            @endif

                        @elseif($field['type'] === 'textarea')
                            @if($field['displayValue'])
                                <div class="field-value-textarea">{{ $field['displayValue'] }}</div>
                            @else
                                <span class="empty-value">No response provided</span>
                            @endif

                        @elseif($field['type'] === 'checkbox')
                            @if($field['displayValue'])
                                <span class="field-value-checkbox">✓ YES</span>
                            @else
                                <span class="field-value-checkbox field-value-checkbox-no">✗ NO</span>
                            @endif

                        @elseif($field['type'] === 'radio' || $field['type'] === 'select')
                            @if($field['displayValue'])
                                <span class="field-value-select">{{ $field['displayValue'] }}</span>
                            @else
                                <span class="empty-value">Not selected</span>
                            @endif

                        @else
                            @if($field['displayValue'])
                                <div class="field-value-text">{{ $field['displayValue'] }}</div>
                            @else
                                <span class="empty-value">No response provided</span>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach

<!-- Professional Footer -->
<div class="document-footer">
    <div class="footer-content">
        <div class="footer-left">
            <div class="footer-section">
                <div class="footer-heading">Contact Information</div>
                <div class="footer-text">
                    Luxembourg House of Cybersecurity<br>
                    122, Rue Adolphe Fischer<br>
                    L-1521 Luxembourg<br>
                    <a href="mailto:info@nc3.lu" class="footer-link">info@nc3.lu</a>
                </div>
            </div>
        </div>
        <div class="footer-right">
            <div class="footer-section">
                <div class="footer-heading">Document Information</div>
                <div class="footer-text">
                    Generated: {{ now()->format('Y-m-d H:i:s') }} UTC<br>
                    Document ID: {{ $submission['id'] }}<br>
                    Version: 2.0
                </div>
            </div>
        </div>
    </div>
    
    <div class="security-notice">
        <div class="security-text">
            🔒 This document contains confidential information. Handle according to your organization's security policies.
        </div>
    </div>
</div>

</body>
</html>
