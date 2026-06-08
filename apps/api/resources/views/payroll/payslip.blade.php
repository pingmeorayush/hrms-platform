<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip {{ $payslipNumber }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #0f172a;
            margin: 24px;
            line-height: 1.45;
        }
        .header,
        .summary,
        .totals,
        .section {
            margin-bottom: 24px;
        }
        .header h1,
        .section h2 {
            margin: 0 0 8px;
        }
        .meta,
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px 24px;
        }
        .pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            background: #e2e8f0;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 0.04em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th,
        td {
            border-bottom: 1px solid #cbd5e1;
            padding: 8px 0;
            text-align: left;
        }
        th:last-child,
        td:last-child {
            text-align: right;
        }
        .totals {
            border-top: 2px solid #0f172a;
            padding-top: 16px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .net {
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <section class="header">
        <span class="pill">Generated Payslip</span>
        <h1>{{ $companySnapshot['name'] ?? 'Company' }}</h1>
        <div class="meta">
            <div><strong>Payslip Number:</strong> {{ $payslipNumber }}</div>
            <div><strong>Payroll Period:</strong> {{ $periodName }}</div>
            <div><strong>Period Range:</strong> {{ $startDate }} to {{ $endDate }}</div>
            <div><strong>Payroll Date:</strong> {{ $payrollDate }}</div>
            <div><strong>Generated At:</strong> {{ $generatedAt }}</div>
            <div><strong>Currency:</strong> {{ $currency }}</div>
        </div>
    </section>

    <section class="summary">
        <h2>Employee Summary</h2>
        <div class="summary-grid">
            <div><strong>Name:</strong> {{ $employeeSnapshot['full_name'] ?? '' }}</div>
            <div><strong>Employee Code:</strong> {{ $employeeSnapshot['employee_code'] ?? '' }}</div>
            <div><strong>Email:</strong> {{ $employeeSnapshot['email'] ?? '' }}</div>
            <div><strong>Company Timezone:</strong> {{ $companySnapshot['timezone'] ?? '' }}</div>
        </div>
    </section>

    <section class="section">
        <h2>Earnings</h2>
        <table>
            <thead>
                <tr>
                    <th>Component</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($earningsBreakdown as $line)
                    <tr>
                        <td>{{ $line['name'] ?? $line['code'] ?? 'Component' }}</td>
                        <td>{{ number_format((float) ($line['prorated_amount'] ?? 0), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Deductions</h2>
        <table>
            <thead>
                <tr>
                    <th>Component</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deductionsBreakdown as $line)
                    <tr>
                        <td>{{ $line['name'] ?? $line['code'] ?? 'Component' }}</td>
                        <td>{{ number_format((float) ($line['prorated_amount'] ?? 0), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Employer Contributions</h2>
        <table>
            <thead>
                <tr>
                    <th>Component</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employerContributionBreakdown as $line)
                    <tr>
                        <td>{{ $line['name'] ?? $line['code'] ?? 'Component' }}</td>
                        <td>{{ number_format((float) ($line['prorated_amount'] ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td>No employer contributions</td>
                        <td>0.00</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="totals">
        <div class="totals-row"><span>Gross Salary</span><strong>{{ number_format($grossSalary, 2) }}</strong></div>
        <div class="totals-row"><span>Total Earnings</span><strong>{{ number_format($totalEarnings, 2) }}</strong></div>
        <div class="totals-row"><span>Total Deductions</span><strong>{{ number_format($totalDeductions, 2) }}</strong></div>
        <div class="totals-row net"><span>Net Salary</span><span>{{ number_format($netSalary, 2) }}</span></div>
        <div class="totals-row"><span>Employer Cost</span><strong>{{ number_format($employerCost, 2) }}</strong></div>
    </section>
</body>
</html>
