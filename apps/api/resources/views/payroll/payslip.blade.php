@php
    $money = static fn (float|int|string|null $value): string => number_format((float) ($value ?? 0), 2);
    $displayDate = static function (?string $value): string {
        if (blank($value)) {
            return '—';
        }

        $timestamp = strtotime($value);

        return $timestamp ? date('d M Y', $timestamp) : $value;
    };
    $displayText = static fn (?string $value): string => filled($value) ? $value : '—';
    $displayFrequency = static fn (?string $value): string => filled($value) ? ucwords(str_replace('_', ' ', $value)) : '—';
    $displayDays = static fn (float|int|string|null $value): string => number_format((float) ($value ?? 0), 2);
    $displayMinutes = static function (?int $minutes): string {
        if (! $minutes) {
            return '0 min';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0 && $remainingMinutes > 0) {
            return sprintf('%d hr %d min', $hours, $remainingMinutes);
        }

        if ($hours > 0) {
            return sprintf('%d hr', $hours);
        }

        return sprintf('%d min', $remainingMinutes);
    };

    $salaryStructureLabel = trim(collect([
        $salaryStructureCode ?? null,
        filled($salaryStructureVersion ?? null) ? 'v'.$salaryStructureVersion : null,
    ])->filter()->implode(' '));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip {{ $payslipNumber }}</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #172033;
            --muted: #5f6b85;
            --line: #d8dfeb;
            --soft: #eef3f9;
            --card: #ffffff;
            --accent: #0f4c81;
            --accent-soft: #e4effa;
            --success: #1f7a46;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            background: #f3f6fb;
            color: var(--ink);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.45;
        }

        .document {
            max-width: 960px;
            margin: 0 auto;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 22px 50px rgba(23, 32, 51, 0.08);
        }

        .hero {
            padding: 28px 32px 24px;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 32%),
                linear-gradient(135deg, #0f4c81 0%, #0d355a 100%);
            color: #ffffff;
        }

        .hero-top {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            align-items: flex-start;
            margin-bottom: 22px;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .hero h1 {
            margin: 10px 0 4px;
            font-size: 31px;
            line-height: 1.1;
        }

        .hero p {
            margin: 0;
            color: rgba(255, 255, 255, 0.82);
            max-width: 520px;
        }

        .net-pay-card {
            min-width: 230px;
            padding: 18px 20px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(10px);
        }

        .net-pay-card .label {
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.72);
        }

        .net-pay-card .amount {
            margin-top: 8px;
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .hero-meta {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .hero-meta-card {
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.16);
        }

        .hero-meta-card .label,
        .panel .label,
        .stat-card .label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .hero-meta-card .label {
            color: rgba(255, 255, 255, 0.72);
        }

        .hero-meta-card .value {
            display: block;
            margin-top: 6px;
            font-size: 15px;
            font-weight: 600;
        }

        .body {
            padding: 28px 32px 32px;
        }

        .grid-two {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 18px;
        }

        .panel {
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 20px;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .panel h2 {
            margin: 0 0 16px;
            font-size: 16px;
            line-height: 1.2;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px 18px;
        }

        .detail-item .label,
        .stat-card .label {
            color: var(--muted);
        }

        .detail-item .value {
            display: block;
            margin-top: 4px;
            font-size: 14px;
            font-weight: 600;
            color: var(--ink);
            word-break: break-word;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin: 18px 0;
        }

        .stat-card {
            padding: 16px;
            border-radius: 18px;
            background: var(--soft);
            border: 1px solid #e0e8f3;
        }

        .stat-card .value {
            display: block;
            margin-top: 8px;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .tables {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }

        .stack {
            display: grid;
            gap: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            padding: 11px 12px;
            background: var(--soft);
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            text-align: left;
        }

        tbody td,
        tfoot td {
            padding: 12px;
            border-bottom: 1px solid var(--line);
            font-size: 14px;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        td.amount,
        th.amount {
            text-align: right;
            white-space: nowrap;
        }

        .summary-panel {
            display: grid;
            gap: 14px;
        }

        .summary-card {
            border-radius: 20px;
            border: 1px solid var(--line);
            background: #ffffff;
            padding: 20px;
        }

        .summary-card h2 {
            margin: 0 0 16px;
            font-size: 16px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 10px 0;
            border-bottom: 1px solid var(--line);
        }

        .summary-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .summary-row .name {
            color: var(--muted);
        }

        .summary-row .value {
            font-weight: 700;
        }

        .summary-highlight {
            margin-top: 18px;
            padding: 18px 20px;
            border-radius: 18px;
            background: linear-gradient(180deg, var(--accent-soft) 0%, #f4f9ff 100%);
            border: 1px solid #cde0f4;
        }

        .summary-highlight .label {
            color: var(--accent);
        }

        .summary-highlight .amount {
            display: block;
            margin-top: 8px;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0d355a;
        }

        .note {
            margin-top: 18px;
            padding: 16px 18px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px dashed #cbd6e3;
            color: var(--muted);
            font-size: 13px;
        }

        .footer {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            gap: 20px;
            color: var(--muted);
            font-size: 12px;
        }

        .footer strong {
            color: var(--ink);
        }

        @page {
            size: A4;
            margin: 16mm;
        }

        @media (max-width: 860px) {
            body {
                padding: 12px;
            }

            .hero,
            .body {
                padding: 22px;
            }

            .hero-top,
            .hero-meta,
            .grid-two,
            .stats,
            .tables,
            .details-grid {
                grid-template-columns: 1fr;
            }

            .footer {
                flex-direction: column;
            }
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .document {
                box-shadow: none;
                border: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <main class="document">
        <section class="hero">
            <div class="hero-top">
                <div>
                    <span class="hero-eyebrow">Salary Slip</span>
                    <h1>{{ $companySnapshot['name'] ?? 'Company' }}</h1>
                    <p>Payroll release statement for {{ $displayText($employeeSnapshot['full_name'] ?? null) }} covering the period {{ $displayDate($startDate) }} to {{ $displayDate($endDate) }}.</p>
                </div>
                <div class="net-pay-card">
                    <span class="label">Net Pay</span>
                    <span class="amount">{{ $currency }} {{ $money($netSalary) }}</span>
                </div>
            </div>

            <div class="hero-meta">
                <div class="hero-meta-card">
                    <span class="label">Payslip Number</span>
                    <span class="value">{{ $payslipNumber }}</span>
                </div>
                <div class="hero-meta-card">
                    <span class="label">Payroll Period</span>
                    <span class="value">{{ $displayText($periodName) }}</span>
                </div>
                <div class="hero-meta-card">
                    <span class="label">Payroll Date</span>
                    <span class="value">{{ $displayDate($payrollDate) }}</span>
                </div>
                <div class="hero-meta-card">
                    <span class="label">Generated On</span>
                    <span class="value">{{ $displayDate($generatedAt) }}</span>
                </div>
            </div>
        </section>

        <section class="body">
            <div class="grid-two">
                <section class="panel">
                    <h2>Employee Details</h2>
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="label">Employee Name</span>
                            <span class="value">{{ $displayText($employeeSnapshot['full_name'] ?? null) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Employee Code</span>
                            <span class="value">{{ $displayText($employeeSnapshot['employee_code'] ?? null) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Department</span>
                            <span class="value">{{ $displayText($employeeSnapshot['department_name'] ?? null) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Designation</span>
                            <span class="value">{{ $displayText($employeeSnapshot['designation_name'] ?? null) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Location</span>
                            <span class="value">{{ $displayText($employeeSnapshot['location_name'] ?? null) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Date of Joining</span>
                            <span class="value">{{ $displayDate($employeeSnapshot['date_of_joining'] ?? null) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Employment Type</span>
                            <span class="value">{{ $displayFrequency($employeeSnapshot['employment_type'] ?? null) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Email</span>
                            <span class="value">{{ $displayText($employeeSnapshot['email'] ?? null) }}</span>
                        </div>
                    </div>
                </section>

                <section class="panel">
                    <h2>Payroll Details</h2>
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="label">Period Range</span>
                            <span class="value">{{ $displayDate($startDate) }} to {{ $displayDate($endDate) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Pay Frequency</span>
                            <span class="value">{{ $displayFrequency($payFrequency ?? null) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Salary Structure</span>
                            <span class="value">{{ filled($salaryStructureLabel) ? $salaryStructureLabel : '—' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Structure Name</span>
                            <span class="value">{{ $displayText($salaryStructureName ?? null) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Basic Salary</span>
                            <span class="value">{{ $currency }} {{ $money($basicSalaryAmount ?? 0) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Annual CTC</span>
                            <span class="value">{{ $currency }} {{ $money($annualCtcAmount ?? 0) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Currency</span>
                            <span class="value">{{ $displayText($currency) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Company Timezone</span>
                            <span class="value">{{ $displayText($companySnapshot['timezone'] ?? null) }}</span>
                        </div>
                    </div>
                </section>
            </div>

            <section class="stats">
                <div class="stat-card">
                    <span class="label">Payable Days</span>
                    <span class="value">{{ $displayDays($employmentDays ?? 0) }}</span>
                </div>
                <div class="stat-card">
                    <span class="label">Unpaid Days</span>
                    <span class="value">{{ $displayDays($unpaidDays ?? 0) }}</span>
                </div>
                <div class="stat-card">
                    <span class="label">LOP Days</span>
                    <span class="value">{{ $displayDays($lopDays ?? 0) }}</span>
                </div>
                <div class="stat-card">
                    <span class="label">Overtime</span>
                    <span class="value">{{ $displayMinutes($overtimeMinutes ?? 0) }}</span>
                </div>
            </section>

            <section class="tables">
                <div class="stack">
                    <section class="panel">
                        <h2>Earnings Breakdown</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Component</th>
                                    <th class="amount">Base</th>
                                    <th class="amount">Payable</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($earningsBreakdown as $line)
                                    <tr>
                                        <td>{{ $line['name'] ?? $line['code'] ?? 'Component' }}</td>
                                        <td class="amount">{{ $money($line['base_amount'] ?? $line['prorated_amount'] ?? 0) }}</td>
                                        <td class="amount">{{ $money($line['prorated_amount'] ?? 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No earning components were captured for this payslip.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </section>

                    <section class="panel">
                        <h2>Deductions Breakdown</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Component</th>
                                    <th class="amount">Base</th>
                                    <th class="amount">Applied</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($deductionsBreakdown as $line)
                                    <tr>
                                        <td>{{ $line['name'] ?? $line['code'] ?? 'Component' }}</td>
                                        <td class="amount">{{ $money($line['base_amount'] ?? $line['prorated_amount'] ?? 0) }}</td>
                                        <td class="amount">{{ $money($line['prorated_amount'] ?? 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No deduction components were captured for this payslip.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </section>
                </div>

                <div class="summary-panel">
                    <section class="summary-card">
                        <h2>Payroll Summary</h2>
                        <div class="summary-row">
                            <span class="name">Gross Salary</span>
                            <span class="value">{{ $currency }} {{ $money($grossSalary) }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="name">Total Earnings</span>
                            <span class="value">{{ $currency }} {{ $money($totalEarnings) }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="name">Total Deductions</span>
                            <span class="value">{{ $currency }} {{ $money($totalDeductions) }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="name">Employer Cost</span>
                            <span class="value">{{ $currency }} {{ $money($employerCost) }}</span>
                        </div>

                        <div class="summary-highlight">
                            <span class="label">Net Pay Released</span>
                            <span class="amount">{{ $currency }} {{ $money($netSalary) }}</span>
                        </div>
                    </section>

                    <section class="panel">
                        <h2>Employer Contributions</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Component</th>
                                    <th class="amount">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($employerContributionBreakdown as $line)
                                    <tr>
                                        <td>{{ $line['name'] ?? $line['code'] ?? 'Component' }}</td>
                                        <td class="amount">{{ $money($line['prorated_amount'] ?? 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2">No employer contribution entries were recorded for this payslip.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </section>
                </div>
            </section>

            <section class="note">
                This is a system-generated salary slip. Please review the earning, deduction, and payroll-period details above and contact your payroll administrator if any item needs clarification before statutory or reimbursement cutoffs.
            </section>

            <footer class="footer">
                <div>
                    <strong>Confidential payroll document.</strong>
                    Share only through approved employee or payroll channels.
                </div>
                <div>
                    Generated on <strong>{{ $displayDate($generatedAt) }}</strong> for payroll date <strong>{{ $displayDate($payrollDate) }}</strong>.
                </div>
            </footer>
        </section>
    </main>
</body>
</html>
