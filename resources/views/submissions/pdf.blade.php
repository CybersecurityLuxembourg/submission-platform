<!-- resources/views/submissions/pdf-single.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .form-title {
            font-size: 24px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 5px;
        }

        .submission-info {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 5px;
        }

        .submission-id {
            font-weight: bold;
            color: #2563eb;
            font-size: 14px;
        }

        .submission-date {
            color: #666;
        }

        .category {
            margin-bottom: 30px;
        }

        .category-name {
            font-size: 16px;
            font-weight: bold;
            background-color: #f3f4f6;
            padding: 8px 12px;
            margin-bottom: 15px;
            border-left: 4px solid #2563eb;
            color: #1a1a1a;
        }

        .category-description {
            color: #666;
            margin-bottom: 15px;
            font-style: italic;
        }

        .field {
            margin-bottom: 15px;
            padding-left: 10px;
        }

        .field-label {
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
        }

        .field-value {
            margin-top: 3px;
            padding: 3px 0;
        }

        .file-link {
            color: #2563eb;
            text-decoration: underline;
        }

        .empty-value {
            color: #999;
            font-style: italic;
        }

        .footer {
            margin-top: 40px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
            text-align: center;
        }

        .textarea-value {
            white-space: pre-wrap;
            background-color: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
            padding: 4px 8px;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="form-title">{{ $form->title }}</div>
</div>

<div class="submission-info">
    <table>
        <tr>
            <td width="50%">
                <span class="submission-id">Submission #{{ $submission['id'] }}</span>
            </td>
            <td width="50%" style="text-align: right;">
                <span class="submission-date">Submitted: {{ $submission['created_at'] }}</span>
            </td>
        </tr>
    </table>
</div>

@foreach($submission['categories'] as $category)
    <div class="category">
        <div class="category-name">{{ $category['name'] }}</div>

        @if($category['description'])
            <div class="category-description">{{ $category['description'] }}</div>
        @endif

        @foreach($category['fields'] as $field)
            <div class="field">
                <div class="field-label">{{ $field['label'] }}</div>
                <div class="field-value">
                    @if($field['type'] === 'file')
                        @if($field['displayValue'])
                            <span class="file-link">[File: {{ basename($field['displayValue']) }}]</span>
                        @else
                            <span class="empty-value">No file uploaded</span>
                        @endif
                    @elseif($field['type'] === 'textarea')
                        <div class="textarea-value">{{ $field['displayValue'] ?: 'N/A' }}</div>
                    @elseif($field['type'] === 'checkbox')
                        {{ $field['displayValue'] ?: 'No' }}
                    @elseif($field['type'] === 'radio' || $field['type'] === 'select')
                        {{ $field['displayValue'] ?: 'Not selected' }}
                    @else
                        {{ $field['displayValue'] ?: 'N/A' }}
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endforeach

<div class="footer">
    Generated on {{ now()->format('Y-m-d H:i:s') }}
</div>
</body>
</html>
