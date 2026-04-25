<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Planting Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
            margin: 24px;
        }

        h1 {
            margin: 0;
            font-size: 24px;
        }

        p {
            margin: 0;
        }

        .header {
            width: 100%;
            margin-bottom: 14px;
        }

        .header-left {
            float: left;
            width: 66%;
        }

        .header-right {
            float: right;
            width: 34%;
            text-align: right;
            color: #4b5563;
            font-size: 9px;
        }

        .clearfix {
            clear: both;
        }

        .subheading {
            margin-top: 4px;
            color: #4b5563;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0 14px;
        }

        .summary td {
            width: 25%;
            border: 1px solid #d1d5db;
            padding: 10px;
            vertical-align: top;
        }

        .summary-label {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #6b7280;
        }

        .summary-value {
            margin-top: 6px;
            font-size: 16px;
            font-weight: bold;
            color: #111827;
        }

        .filters {
            margin-bottom: 14px;
        }

        .filter-pill {
            display: inline-block;
            margin: 0 8px 6px 0;
            padding: 4px 8px;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            background: #f8fafc;
            color: #334155;
            font-size: 9px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #d1d5db;
            padding: 6px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .report-table th {
            background: #f3f4f6;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #374151;
        }

        .muted {
            color: #6b7280;
        }
    </style>
</head>
<body>
    @php
        $activeFilters = collect([
            'Search' => $filters['search'] ?? null,
            'Municipality' => $filters['municipality'] ?? null,
            'Status' => isset($filters['status']) && filled($filters['status']) ? ucfirst($filters['status']) : null,
        ])->filter(fn ($value) => filled($value));
    @endphp

    <div class="header">
        <div class="header-left">
            <h1>Planting Report</h1>
            <p class="subheading">Filtered crop plan records exported from the admin planting report.</p>
        </div>
        <div class="header-right">
            <p>Generated: {{ $generatedAt->format('M d, Y h:i A') }}</p>
            <p>Records: {{ number_format($summary['total_records']) }}</p>
        </div>
        <div class="clearfix"></div>
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">Total Records</div>
                <div class="summary-value">{{ number_format($summary['total_records']) }}</div>
            </td>
            <td>
                <div class="summary-label">Planned Records</div>
                <div class="summary-value">{{ number_format($summary['planned_records']) }}</div>
            </td>
            <td>
                <div class="summary-label">Total Area</div>
                <div class="summary-value">{{ number_format($summary['total_area'], 2) }} ha</div>
            </td>
            <td>
                <div class="summary-label">Predicted Production</div>
                <div class="summary-value">{{ number_format($summary['total_predicted_production'], 2) }} MT</div>
            </td>
        </tr>
    </table>

    <div class="filters">
        @if ($activeFilters->isNotEmpty())
            @foreach ($activeFilters as $label => $value)
                <span class="filter-pill">{{ $label }}: {{ $value }}</span>
            @endforeach
        @else
            <span class="filter-pill">Filters: All records</span>
        @endif
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th>Farmer</th>
                <th>Farmer ID</th>
                <th>Municipality</th>
                <th>Contact</th>
                <th>Crop</th>
                <th>Planting</th>
                <th>Harvest</th>
                <th>Area (ha)</th>
                <th>Predicted MT</th>
                <th>Farm Type</th>
                <th>Material</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($plantingRecords as $record)
                @php
                    $farmer = $record->farmer;
                @endphp
                <tr>
                    <td>
                        <strong>{{ $farmer?->full_name ?? 'Farmer record unavailable' }}</strong><br>
                        <span class="muted">{{ $farmer?->cooperative_display ?? 'N/A' }}</span>
                        @if ($farmer?->trashed())
                            <br><span class="muted">Archived farmer</span>
                        @endif
                    </td>
                    <td>{{ $farmer?->farmer_id ?? 'N/A' }}</td>
                    <td>{{ ucwords(strtolower($record->municipality ?? $farmer?->municipality ?? 'N/A')) }}</td>
                    <td>
                        {{ $farmer?->mobile_number ?? 'No mobile number' }}<br>
                        <span class="muted">{{ $farmer?->email ?? 'No email address' }}</span>
                    </td>
                    <td>
                        <strong>{{ $record->crop_name }}</strong><br>
                        <span class="muted">{{ $record->notes ? \Illuminate\Support\Str::limit($record->notes, 60) : 'No notes' }}</span>
                    </td>
                    <td>{{ optional($record->planting_date)->format('M d, Y') }}</td>
                    <td>{{ optional($record->expected_harvest_date)->format('M d, Y') }}</td>
                    <td>{{ number_format((float) $record->area_hectares, 2) }}</td>
                    <td>{{ number_format((float) $record->predicted_production, 2) }}</td>
                    <td>{{ ucfirst(strtolower((string) $record->farm_type)) }}</td>
                    <td>{{ $record->planting_material_label ?? 'Not set' }}</td>
                    <td>{{ strtoupper($record->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>