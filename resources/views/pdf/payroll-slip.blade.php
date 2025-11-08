<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payslip - {{ $payroll->employee->name }} - {{ \Carbon\Carbon::parse($payroll->month)->format('F Y') }}</title>
  <style>
    /* Company Payslip Styles */
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 14px;
      background: #f5f5f5;
      margin: 0;
      padding: 20px;
    }
    .payslip-container {
      max-width: 800px;
      margin: 0 auto;
      background: #fff;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.15);
    }
    .header {
      text-align: center;
      border-bottom: 2px solid #333;
      padding-bottom: 10px;
      margin-bottom: 20px;
    }
    .header h1 {
      margin: 0;
      font-size: 24px;
    }
    .header p {
      margin: 5px 0;
      font-size: 12px;
    }
    .section {
      margin-bottom: 20px;
    }
    .section h2 {
      font-size: 18px;
      border-bottom: 1px solid #ddd;
      padding-bottom: 5px;
      margin-bottom: 10px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    table td {
      padding: 8px;
      vertical-align: top;
    }
    table tr:nth-child(odd) {
      background: #f9f9f9;
    }
    .summary td {
      border-top: 1px solid #ddd;
      font-weight: bold;
    }
    .footer {
      text-align: center;
      font-size: 12px;
      color: #777;
      border-top: 1px solid #ddd;
      padding-top: 10px;
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <div class="payslip-container">
    <!-- Header -->
    <div class="header">
      <h1>{{ $payroll->more_config_name }}</h1>
    </div>
    <!-- Employee & Payroll Info -->
    <div class="section">
      <h2>Employee Information</h2>
      <table>
        <tr>
          <td>
            <strong>Employee Name:</strong><br>
            {{ $payroll->employee->name }}
          </td>
          <td>
            <strong>Department:</strong><br>
            {{ $payroll->employee->department->name }}
          </td>
        </tr>
        <tr>
          <td>
            <strong>Payroll Month:</strong><br>
            {{ \Carbon\Carbon::parse($payroll->month)->format('F Y') }}
          </td>
          <td>
            <strong>Payroll ID:</strong><br>
            {{ $payroll->id }}
          </td>
        </tr>
      </table>
    </div>
    <!-- Allowances -->
    @if($payroll->current_allowances && count($payroll->current_allowances))
      <div class="section">
        <h2>Allowances</h2>
        <table>
          @foreach($payroll->current_allowances as $allowance)
          <tr>
            <td>{{ $allowance['name'] }}</td>
            <td style="text-align:right;">₦ {{ number_format($allowance['amount'], 2) }}</td>
          </tr>
          @endforeach
        </table>
      </div>
    @endif
    <!-- Deductions -->
    @if($payroll->current_deductions && count($payroll->current_deductions))
      <div class="section">
        <h2>Deductions</h2>
        <table>
          @foreach($payroll->current_deductions as $deduction)
          <tr>
            <td>{{ $deduction['name'] }}</td>
            <td style="text-align:right;">₦ {{ number_format($deduction['amount'], 2) }}</td>
          </tr>
          @endforeach
        </table>
      </div>
    @endif
    <!-- Bonuses -->
    @if($payroll->current_bonuses && count($payroll->current_bonuses))
      <div class="section">
        <h2>Bonuses</h2>
        <table>
          @foreach($payroll->current_bonuses as $bonus)
          <tr>
            <td>{{ $bonus['name'] }}</td>
            <td style="text-align:right;">₦ {{ number_format($bonus['amount'], 2) }}</td>
          </tr>
          @endforeach
        </table>
      </div>
    @endif
 <!-- Salary Breakdown -->
    <div class="section">
      <h2>Salary Breakdown</h2>
      <table>
        <tr>
          <td>Basic Salary</td>
          <td style="text-align:right;">₦ {{ number_format($payroll->basic_salary, 2) }}</td>
        </tr>
        <tr>
          <td>Total Allowances</td>
          <td style="text-align:right;">+ ₦ {{ number_format($payroll->total_allowances, 2) }}</td>
        </tr>
        <tr>
          <td>Total Deductions</td>
          <td style="text-align:right;">- ₦ {{ number_format($payroll->total_deductions, 2) }}</td>
        </tr>
        <tr class="summary">
          <td>Net Salary</td>
          <td style="text-align:right;">₦ {{ number_format($payroll->net_salary, 2) }}</td>
        </tr>
      </table>
    </div>
    <!-- Footer -->
    <div class="footer">
      <p>Thank you for your hard work!</p>
    </div>
  </div>
</body>
</html>