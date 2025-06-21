# Reports & Statistics

## Overview

The reports and statistics section of the Cashbox application provides users with comprehensive insights into team finances, member contributions, and overall financial health. This document outlines the design and implementation of the reports and statistics pages, which offer data visualization, exportable reports, and actionable insights.

## User Stories

1. As a team admin, I want to view financial statistics so that I can understand the team's financial health
2. As a team admin, I want to generate custom reports so that I can analyze specific aspects of team finances
3. As a team member, I want to view my contribution statistics so that I can understand my financial standing
4. As a team admin, I want to export financial data so that I can use it in external tools
5. As a team admin, I want to visualize financial trends so that I can identify patterns and make informed decisions

## Reports & Statistics Pages

The reports and statistics section consists of the following pages:

1. **Financial Dashboard**: Overview of key financial metrics and trends
2. **Team Reports**: Detailed reports on team finances with customization options
3. **Member Statistics**: Individual member contribution and payment statistics
4. **Financial Trends**: Visualizations of financial data over time
5. **Export Center**: Tools for exporting data in various formats

## Financial Dashboard Page

The financial dashboard provides an overview of key financial metrics, recent activity, and important trends.

### Design

```twig
{# templates/pages/reports/dashboard.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Financial Dashboard | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Financial Dashboard</h1>
        <div class="btn-group">
            <a href="{{ path('reports_team') }}" class="btn btn-outline-primary">
                <i class="fas fa-file-alt me-1"></i> Team Reports
            </a>
            <a href="{{ path('reports_export') }}" class="btn btn-outline-secondary">
                <i class="fas fa-file-export me-1"></i> Export Data
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Balance</h6>
                    <p class="h3 mb-0">{{ stats.totalBalance|format_currency('EUR') }}</p>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar {{ stats.totalBalance >= 0 ? 'bg-success' : 'bg-danger' }}" role="progressbar" style="width: {{ stats.totalBalance >= 0 ? stats.balancePercentage : 100 }}%"></div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <small class="text-muted">
                        {% if stats.balanceChange >= 0 %}
                            <i class="fas fa-arrow-up text-success me-1"></i>
                        {% else %}
                            <i class="fas fa-arrow-down text-danger me-1"></i>
                        {% endif %}
                        {{ stats.balanceChange|abs|format_currency('EUR') }} since last month
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Income (30 days)</h6>
                    <p class="h3 mb-0">{{ stats.monthlyIncome|format_currency('EUR') }}</p>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ stats.incomePercentage }}%"></div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <small class="text-muted">
                        {% if stats.incomeChange >= 0 %}
                            <i class="fas fa-arrow-up text-success me-1"></i>
                        {% else %}
                            <i class="fas fa-arrow-down text-danger me-1"></i>
                        {% endif %}
                        {{ stats.incomeChange|abs }}% compared to previous period
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3 mb-md-0">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Pending Payments</h6>
                    <p class="h3 mb-0">{{ stats.pendingAmount|format_currency('EUR') }}</p>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ stats.pendingPercentage }}%"></div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <small class="text-muted">{{ stats.pendingCount }} pending items</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Collection Rate</h6>
                    <p class="h3 mb-0">{{ stats.collectionRate }}%</p>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ stats.collectionRate }}%"></div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <small class="text-muted">
                        {% if stats.collectionRateChange >= 0 %}
                            <i class="fas fa-arrow-up text-success me-1"></i>
                        {% else %}
                            <i class="fas fa-arrow-down text-danger me-1"></i>
                        {% endif %}
                        {{ stats.collectionRateChange|abs }}% compared to previous period
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Income & Expenses</h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary active" data-period="30">30 Days</button>
                        <button type="button" class="btn btn-outline-secondary" data-period="90">90 Days</button>
                        <button type="button" class="btn btn-outline-secondary" data-period="365">1 Year</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="incomeExpensesChart" height="300"></canvas>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Transactions</h5>
                    <a href="{{ path('payment_history') }}" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {% for transaction in recent_transactions %}
                                    <tr>
                                        <td>
                                            <a href="{{ transaction.link }}" class="text-decoration-none">
                                                {{ transaction.description }}
                                            </a>
                                        </td>
                                        <td>
                                            {% if transaction.type == 'contribution' %}
                                                <span class="badge bg-primary">Contribution</span>
                                            {% elseif transaction.type == 'penalty' %}
                                                <span class="badge bg-warning text-dark">Penalty</span>
                                            {% elseif transaction.type == 'payment' %}
                                                <span class="badge bg-success">Payment</span>
                                            {% else %}
                                                <span class="badge bg-secondary">Other</span>
                                            {% endif %}
                                        </td>
                                        <td>{{ transaction.amount|format_currency('EUR') }}</td>
                                        <td>{{ transaction.date|date('M d, Y') }}</td>
                                        <td>
                                            {% if transaction.status == 'completed' %}
                                                <span class="badge bg-success">Completed</span>
                                            {% elseif transaction.status == 'pending' %}
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            {% elseif transaction.status == 'failed' %}
                                                <span class="badge bg-danger">Failed</span>
                                            {% endif %}
                                        </td>
                                    </tr>
                                {% else %}
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <p class="text-muted mb-0">No recent transactions found.</p>
                                        </td>
                                    </tr>
                                {% endfor %}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Income Sources</h5>
                </div>
                <div class="card-body">
                    <canvas id="incomeSourcesChart" height="260"></canvas>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Top Contributors</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        {% for contributor in top_contributors %}
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary me-3">
                                            {{ contributor.initials }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ contributor.name }}</h6>
                                            <small class="text-muted">{{ contributor.contributionCount }} contributions</small>
                                        </div>
                                    </div>
                                    <span class="fw-bold">{{ contributor.amount|format_currency('EUR') }}</span>
                                </div>
                            </li>
                        {% else %}
                            <li class="list-group-item text-center py-4">
                                <p class="text-muted mb-0">No contributor data available.</p>
                            </li>
                        {% endfor %}
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Quick Reports</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ path('reports_team', {'preset': 'monthly'}) }}" class="btn btn-outline-primary">
                            <i class="fas fa-calendar-alt me-1"></i> Monthly Summary
                        </a>
                        <a href="{{ path('reports_team', {'preset': 'member'}) }}" class="btn btn-outline-primary">
                            <i class="fas fa-users me-1"></i> Member Contributions
                        </a>
                        <a href="{{ path('reports_team', {'preset': 'outstanding'}) }}" class="btn btn-outline-primary">
                            <i class="fas fa-exclamation-circle me-1"></i> Outstanding Balances
                        </a>
                        <a href="{{ path('reports_team') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-sliders-h me-1"></i> Custom Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Income & Expenses Chart
            const incomeExpensesCtx = document.getElementById('incomeExpensesChart').getContext('2d');
            const incomeExpensesChart = new Chart(incomeExpensesCtx, {
                type: 'bar',
                data: {
                    labels: {{ chart_data.incomeExpenses.labels|json_encode|raw }},
                    datasets: [
                        {
                            label: 'Income',
                            data: {{ chart_data.incomeExpenses.income|json_encode|raw }},
                            backgroundColor: 'rgba(25, 135, 84, 0.5)',
                            borderColor: 'rgba(25, 135, 84, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Expenses',
                            data: {{ chart_data.incomeExpenses.expenses|json_encode|raw }},
                            backgroundColor: 'rgba(220, 53, 69, 0.5)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '€' + value;
                                }
                            }
                        }
                    }
                }
            });

            // Income Sources Chart
            const incomeSourcesCtx = document.getElementById('incomeSourcesChart').getContext('2d');
            const incomeSourcesChart = new Chart(incomeSourcesCtx, {
                type: 'doughnut',
                data: {
                    labels: {{ chart_data.incomeSources.labels|json_encode|raw }},
                    datasets: [{
                        data: {{ chart_data.incomeSources.data|json_encode|raw }},
                        backgroundColor: [
                            'rgba(13, 110, 253, 0.7)',
                            'rgba(25, 135, 84, 0.7)',
                            'rgba(255, 193, 7, 0.7)',
                            'rgba(220, 53, 69, 0.7)',
                            'rgba(13, 202, 240, 0.7)'
                        ],
                        borderColor: [
                            'rgba(13, 110, 253, 1)',
                            'rgba(25, 135, 84, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(220, 53, 69, 1)',
                            'rgba(13, 202, 240, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Period buttons for Income & Expenses chart
            const periodButtons = document.querySelectorAll('[data-period]');
            periodButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    periodButtons.forEach(btn => btn.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');

                    // Update chart data based on selected period
                    const period = this.getAttribute('data-period');
                    fetch(`/api/reports/income-expenses?period=${period}`)
                        .then(response => response.json())
                        .then(data => {
                            incomeExpensesChart.data.labels = data.labels;
                            incomeExpensesChart.data.datasets[0].data = data.income;
                            incomeExpensesChart.data.datasets[1].data = data.expenses;
                            incomeExpensesChart.update();
                        });
                });
            });
        });
    </script>
{% endblock %}
```

## Team Reports Page

The team reports page allows administrators to generate and customize detailed reports on team finances.

### Design

```twig
{# templates/pages/reports/team.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Team Reports | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Team Reports</h1>
        <div class="btn-group">
            <a href="{{ path('reports_dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
            <a href="{{ path('reports_export') }}" class="btn btn-outline-primary">
                <i class="fas fa-file-export me-1"></i> Export Data
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4 mb-lg-0">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Report Options</h5>
                </div>
                <div class="card-body">
                    {{ form_start(form, {'attr': {'id': 'report-form'}}) }}
                        <div class="mb-3">
                            {{ form_label(form.reportType) }}
                            {{ form_widget(form.reportType, {'attr': {'class': 'form-select'}}) }}
                            <div class="form-text">Select the type of report to generate</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.team) }}
                            {{ form_widget(form.team, {'attr': {'class': 'form-select'}}) }}
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.dateRange) }}
                            {{ form_widget(form.dateRange, {'attr': {'class': 'form-control', 'data-daterangepicker': 'true'}}) }}
                            <div class="form-text">Select the date range for the report</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.groupBy) }}
                            {{ form_widget(form.groupBy, {'attr': {'class': 'form-select'}}) }}
                            <div class="form-text">Choose how to group the report data</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.includeDetails) }}
                            {{ form_widget(form.includeDetails, {'attr': {'class': 'form-check-input'}}) }}
                            <div class="form-text">Include detailed breakdown in the report</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.showChart) }}
                            {{ form_widget(form.showChart, {'attr': {'class': 'form-check-input'}}) }}
                            <div class="form-text">Include visual chart in the report</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sync-alt me-1"></i> Generate Report
                            </button>
                        </div>
                    {{ form_end(form) }}
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ report.title }}</h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" id="print-report">
                            <i class="fas fa-print"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="export-pdf">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                        <button type="button" class="btn btn-outline-success" id="export-excel">
                            <i class="fas fa-file-excel"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="report-container">
                        <div class="report-header mb-4">
                            <h4>{{ report.title }}</h4>
                            <p class="text-muted">{{ report.subtitle }}</p>
                            <div class="d-flex justify-content-between">
                                <span>Team: {{ report.team }}</span>
                                <span>Period: {{ report.period }}</span>
                            </div>
                        </div>

                        {% if report.showChart and report.chartData %}
                            <div class="report-chart mb-4">
                                <canvas id="reportChart" height="300"></canvas>
                            </div>
                        {% endif %}

                        <div class="report-summary mb-4">
                            <h5>Summary</h5>
                            <div class="row">
                                {% for item in report.summary %}
                                    <div class="col-md-3 mb-3">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body py-2">
                                                <h6 class="text-muted mb-1">{{ item.label }}</h6>
                                                <p class="h5 mb-0">{{ item.value }}</p>
                                            </div>
                                        </div>
                                    </div>
                                {% endfor %}
                            </div>
                        </div>

                        {% if report.data|length > 0 %}
                            <div class="report-data">
                                <h5>{{ report.dataTitle }}</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                {% for header in report.headers %}
                                                    <th>{{ header }}</th>
                                                {% endfor %}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {% for row in report.data %}
                                                <tr>
                                                    {% for cell in row %}
                                                        <td>{{ cell }}</td>
                                                    {% endfor %}
                                                </tr>
                                            {% endfor %}
                                        </tbody>
                                        {% if report.totals|length > 0 %}
                                            <tfoot class="table-light">
                                                <tr>
                                                    {% for total in report.totals %}
                                                        <th>{{ total }}</th>
                                                    {% endfor %}
                                                </tr>
                                            </tfoot>
                                        {% endif %}
                                    </table>
                                </div>
                            </div>
                        {% else %}
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-1"></i> No data available for the selected criteria.
                            </div>
                        {% endif %}

                        {% if report.includeDetails and report.details|length > 0 %}
                            <div class="report-details mt-4">
                                <h5>Detailed Breakdown</h5>
                                <div class="accordion" id="detailsAccordion">
                                    {% for section in report.details %}
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading{{ loop.index }}">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ loop.index }}" aria-expanded="false" aria-controls="collapse{{ loop.index }}">
                                                    {{ section.title }}
                                                </button>
                                            </h2>
                                            <div id="collapse{{ loop.index }}" class="accordion-collapse collapse" aria-labelledby="heading{{ loop.index }}" data-bs-parent="#detailsAccordion">
                                                <div class="accordion-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    {% for header in section.headers %}
                                                                        <th>{{ header }}</th>
                                                                    {% endfor %}
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                {% for row in section.data %}
                                                                    <tr>
                                                                        {% for cell in row %}
                                                                            <td>{{ cell }}</td>
                                                                        {% endfor %}
                                                                    </tr>
                                                                {% endfor %}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    {% endfor %}
                                </div>
                            </div>
                        {% endif %}

                        <div class="report-footer mt-4 pt-3 border-top">
                            <small class="text-muted">Generated on {{ "now"|date("F d, Y H:i") }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize date range picker
            $('[data-daterangepicker="true"]').daterangepicker({
                opens: 'left',
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

            // Initialize report chart if data exists
            {% if report.showChart and report.chartData %}
                const reportChartCtx = document.getElementById('reportChart').getContext('2d');
                const reportChart = new Chart(reportChartCtx, {
                    type: '{{ report.chartType }}',
                    data: {
                        labels: {{ report.chartData.labels|json_encode|raw }},
                        datasets: {{ report.chartData.datasets|json_encode|raw }}
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            {% endif %}

            // Print report
            document.getElementById('print-report').addEventListener('click', function() {
                window.print();
            });

            // Export to PDF
            document.getElementById('export-pdf').addEventListener('click', function() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('p', 'mm', 'a4');
                const reportContainer = document.getElementById('report-container');

                html2canvas(reportContainer).then(canvas => {
                    const imgData = canvas.toDataURL('image/png');
                    const imgWidth = 210; // A4 width in mm
                    const pageHeight = 295; // A4 height in mm
                    const imgHeight = canvas.height * imgWidth / canvas.width;
                    let heightLeft = imgHeight;
                    let position = 0;

                    doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;

                    while (heightLeft >= 0) {
                        position = heightLeft - imgHeight;
                        doc.addPage();
                        doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                        heightLeft -= pageHeight;
                    }

                    doc.save('{{ report.title|replace({" ": "_"}) }}.pdf');
                });
            });

            // Export to Excel
            document.getElementById('export-excel').addEventListener('click', function() {
                window.location.href = '{{ path("reports_export_excel", {"id": report.id}) }}';
            });
        });
    </script>
{% endblock %}

{% block stylesheets %}
    {{ parent() }}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #report-container, #report-container * {
                visibility: visible;
            }
            #report-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            .card-header {
                background-color: white !important;
            }
        }
    </style>
{% endblock %}
```

## Member Statistics Page

The member statistics page provides detailed information about individual member contributions and payments.

### Design

```twig
{# templates/pages/reports/member.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Member Statistics | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Member Statistics</h1>
        <div class="btn-group">
            <a href="{{ path('reports_dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
            <a href="{{ path('reports_export', {'type': 'member'}) }}" class="btn btn-outline-primary">
                <i class="fas fa-file-export me-1"></i> Export Data
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Member Selection</h5>
                </div>
                <div class="card-body">
                    <form id="member-selection-form" class="row g-3">
                        <div class="col-md-6">
                            <label for="team-select" class="form-label">Team</label>
                            <select id="team-select" name="team" class="form-select">
                                <option value="">Select Team</option>
                                {% for team in teams %}
                                    <option value="{{ team.id }}" {{ selected_team == team.id ? 'selected' : '' }}>{{ team.name }}</option>
                                {% endfor %}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="member-select" class="form-label">Member</label>
                            <select id="member-select" name="member" class="form-select" {{ selected_team ? '' : 'disabled' }}>
                                <option value="">Select Member</option>
                                {% for member in members %}
                                    <option value="{{ member.id }}" {{ selected_member == member.id ? 'selected' : '' }}>{{ member.fullName }}</option>
                                {% endfor %}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="date-from" class="form-label">Date From</label>
                            <input type="date" id="date-from" name="dateFrom" class="form-control" value="{{ date_from }}">
                        </div>
                        <div class="col-md-6">
                            <label for="date-to" class="form-label">Date To</label>
                            <input type="date" id="date-to" name="dateTo" class="form-control" value="{{ date_to }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> View Statistics
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {% if selected_member %}
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Member Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-circle bg-primary me-3" style="width: 60px; height: 60px; font-size: 24px;">
                                {{ member_stats.initials }}
                            </div>
                            <div>
                                <h4 class="mb-0">{{ member_stats.name }}</h4>
                                <p class="text-muted mb-0">{{ member_stats.email }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <h6 class="text-muted mb-1">Member Since</h6>
                                <p>{{ member_stats.joinDate|date('M d, Y') }}</p>
                            </div>
                            <div class="col-6">
                                <h6 class="text-muted mb-1">Status</h6>
                                <p>
                                    {% if member_stats.isActive %}
                                        <span class="badge bg-success">Active</span>
                                    {% else %}
                                        <span class="badge bg-secondary">Inactive</span>
                                    {% endif %}
                                </p>
                            </div>
                            <div class="col-6">
                                <h6 class="text-muted mb-1">Total Contributions</h6>
                                <p>{{ member_stats.totalContributions|format_currency('EUR') }}</p>
                            </div>
                            <div class="col-6">
                                <h6 class="text-muted mb-1">Payment Rate</h6>
                                <p>{{ member_stats.paymentRate }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {% endif %}
    </div>

    {% if selected_member %}
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Contribution History</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="contributionHistoryChart" height="300"></canvas>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Transaction History</h5>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary active" data-filter="all">All</button>
                            <button type="button" class="btn btn-outline-secondary" data-filter="contribution">Contributions</button>
                            <button type="button" class="btn btn-outline-secondary" data-filter="penalty">Penalties</button>
                            <button type="button" class="btn btn-outline-secondary" data-filter="payment">Payments</button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="transaction-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Description</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {% for transaction in member_stats.transactions %}
                                        <tr data-type="{{ transaction.type }}">
                                            <td>
                                                <a href="{{ transaction.link }}" class="text-decoration-none">
                                                    {{ transaction.description }}
                                                </a>
                                            </td>
                                            <td>
                                                {% if transaction.type == 'contribution' %}
                                                    <span class="badge bg-primary">Contribution</span>
                                                {% elseif transaction.type == 'penalty' %}
                                                    <span class="badge bg-warning text-dark">Penalty</span>
                                                {% elseif transaction.type == 'payment' %}
                                                    <span class="badge bg-success">Payment</span>
                                                {% endif %}
                                            </td>
                                            <td>{{ transaction.amount|format_currency('EUR') }}</td>
                                            <td>{{ transaction.date|date('M d, Y') }}</td>
                                            <td>
                                                {% if transaction.status == 'completed' %}
                                                    <span class="badge bg-success">Completed</span>
                                                {% elseif transaction.status == 'pending' %}
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                {% elseif transaction.status == 'failed' %}
                                                    <span class="badge bg-danger">Failed</span>
                                                {% endif %}
                                            </td>
                                        </tr>
                                    {% else %}
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <p class="text-muted mb-0">No transactions found for this member.</p>
                                            </td>
                                        </tr>
                                    {% endfor %}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Financial Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">Balance</h6>
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar {{ member_stats.balance >= 0 ? 'bg-success' : 'bg-danger' }}" role="progressbar" style="width: {{ member_stats.balance >= 0 ? member_stats.balancePercentage : 100 }}%"></div>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <span class="h5 {{ member_stats.balance >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ member_stats.balance|format_currency('EUR') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <h6 class="text-muted mb-1">Total Paid</h6>
                                <p class="h5 mb-0 text-success">{{ member_stats.totalPaid|format_currency('EUR') }}</p>
                            </div>
                            <div class="col-6 mb-3">
                                <h6 class="text-muted mb-1">Total Due</h6>
                                <p class="h5 mb-0 text-danger">{{ member_stats.totalDue|format_currency('EUR') }}</p>
                            </div>
                            <div class="col-6 mb-3">
                                <h6 class="text-muted mb-1">Contributions</h6>
                                <p class="mb-0">{{ member_stats.contributionCount }}</p>
                            </div>
                            <div class="col-6 mb-3">
                                <h6 class="text-muted mb-1">Penalties</h6>
                                <p class="mb-0">{{ member_stats.penaltyCount }}</p>
                            </div>
                            <div class="col-6">
                                <h6 class="text-muted mb-1">On-time Rate</h6>
                                <p class="mb-0">{{ member_stats.onTimeRate }}%</p>
                            </div>
                            <div class="col-6">
                                <h6 class="text-muted mb-1">Avg. Payment Time</h6>
                                <p class="mb-0">{{ member_stats.avgPaymentDays }} days</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Contribution Types</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="contributionTypesChart" height="260"></canvas>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Upcoming Payments</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            {% for payment in member_stats.upcomingPayments %}
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">{{ payment.description }}</h6>
                                            <small class="text-muted">Due: {{ payment.dueDate|date('M d, Y') }}</small>
                                        </div>
                                        <span class="fw-bold">{{ payment.amount|format_currency('EUR') }}</span>
                                    </div>
                                </li>
                            {% else %}
                                <li class="list-group-item text-center py-4">
                                    <p class="text-muted mb-0">No upcoming payments.</p>
                                </li>
                            {% endfor %}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    {% elseif selected_team %}
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-1"></i> Please select a member to view their statistics.
        </div>
    {% endif %}
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Team selection change handler
            const teamSelect = document.getElementById('team-select');
            const memberSelect = document.getElementById('member-select');

            teamSelect.addEventListener('change', function() {
                const teamId = this.value;

                // Reset member select
                memberSelect.innerHTML = '<option value="">Select Member</option>';
                memberSelect.disabled = !teamId;

                if (teamId) {
                    // Fetch members for selected team
                    fetch(`/api/teams/${teamId}/members`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(member => {
                                const option = document.createElement('option');
                                option.value = member.id;
                                option.textContent = member.fullName;
                                memberSelect.appendChild(option);
                            });
                        });
                }
            });

            {% if selected_member %}
                // Initialize contribution history chart
                const contributionHistoryCtx = document.getElementById('contributionHistoryChart').getContext('2d');
                const contributionHistoryChart = new Chart(contributionHistoryCtx, {
                    type: 'line',
                    data: {
                        labels: {{ member_stats.chart.labels|json_encode|raw }},
                        datasets: [
                            {
                                label: 'Contributions',
                                data: {{ member_stats.chart.contributions|json_encode|raw }},
                                borderColor: 'rgba(13, 110, 253, 1)',
                                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                                borderWidth: 2,
                                fill: true
                            },
                            {
                                label: 'Payments',
                                data: {{ member_stats.chart.payments|json_encode|raw }},
                                borderColor: 'rgba(25, 135, 84, 1)',
                                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                                borderWidth: 2,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '€' + value;
                                    }
                                }
                            }
                        }
                    }
                });

                // Initialize contribution types chart
                const contributionTypesCtx = document.getElementById('contributionTypesChart').getContext('2d');
                const contributionTypesChart = new Chart(contributionTypesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: {{ member_stats.contributionTypes.labels|json_encode|raw }},
                        datasets: [{
                            data: {{ member_stats.contributionTypes.data|json_encode|raw }},
                            backgroundColor: [
                                'rgba(13, 110, 253, 0.7)',
                                'rgba(25, 135, 84, 0.7)',
                                'rgba(255, 193, 7, 0.7)',
                                'rgba(220, 53, 69, 0.7)',
                                'rgba(13, 202, 240, 0.7)'
                            ],
                            borderColor: [
                                'rgba(13, 110, 253, 1)',
                                'rgba(25, 135, 84, 1)',
                                'rgba(255, 193, 7, 1)',
                                'rgba(220, 53, 69, 1)',
                                'rgba(13, 202, 240, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });

                // Transaction table filtering
                const filterButtons = document.querySelectorAll('[data-filter]');
                const transactionRows = document.querySelectorAll('#transaction-table tbody tr[data-type]');

                filterButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        // Remove active class from all buttons
                        filterButtons.forEach(btn => btn.classList.remove('active'));
                        // Add active class to clicked button
                        this.classList.add('active');

                        const filter = this.getAttribute('data-filter');

                        // Show/hide rows based on filter
                        transactionRows.forEach(row => {
                            if (filter === 'all' || row.getAttribute('data-type') === filter) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    });
                });
            {% endif %}
        });
    </script>
{% endblock %}
```

## Financial Trends Page

The financial trends page provides visualizations of financial data over time to identify patterns and trends.

### Design

```twig
{# templates/pages/reports/trends.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Financial Trends | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Financial Trends</h1>
        <div class="btn-group">
            <a href="{{ path('reports_dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
            <a href="{{ path('reports_export', {'type': 'trends'}) }}" class="btn btn-outline-primary">
                <i class="fas fa-file-export me-1"></i> Export Data
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title mb-0">Trend Analysis</h5>
                        </div>
                        <div class="col-md-6">
                            <form id="trends-filter-form" class="row g-3">
                                <div class="col-md-6">
                                    <select name="team" class="form-select form-select-sm">
                                        <option value="">All Teams</option>
                                        {% for team in teams %}
                                            <option value="{{ team.id }}" {{ selected_team == team.id ? 'selected' : '' }}>{{ team.name }}</option>
                                        {% endfor %}
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select name="period" class="form-select form-select-sm">
                                        <option value="30" {{ period == 30 ? 'selected' : '' }}>Last 30 Days</option>
                                        <option value="90" {{ period == 90 ? 'selected' : '' }}>Last 90 Days</option>
                                        <option value="180" {{ period == 180 ? 'selected' : '' }}>Last 6 Months</option>
                                        <option value="365" {{ period == 365 ? 'selected' : '' }}>Last Year</option>
                                        <option value="all" {{ period == 'all' ? 'selected' : '' }}>All Time</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Income vs Expenses Over Time</h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary active" data-chart-view="monthly">Monthly</button>
                        <button type="button" class="btn btn-outline-secondary" data-chart-view="quarterly">Quarterly</button>
                        <button type="button" class="btn btn-outline-secondary" data-chart-view="yearly">Yearly</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="incomeExpensesChart" height="300"></canvas>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Payment Trends</h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary active" data-payment-metric="count">Count</button>
                        <button type="button" class="btn btn-outline-secondary" data-payment-metric="amount">Amount</button>
                        <button type="button" class="btn btn-outline-secondary" data-payment-metric="rate">Payment Rate</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="paymentTrendsChart" height="300"></canvas>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Trend Insights</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        {% for insight in trend_insights %}
                            <div class="col-md-6 mb-3">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="insight-icon me-2">
                                                <i class="{{ insight.icon }} {{ insight.type == 'positive' ? 'text-success' : (insight.type == 'negative' ? 'text-danger' : 'text-info') }}"></i>
                                            </div>
                                            <h6 class="mb-0">{{ insight.title }}</h6>
                                        </div>
                                        <p class="mb-0 small">{{ insight.description }}</p>
                                    </div>
                                </div>
                            </div>
                        {% endfor %}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Payment Method Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodChart" height="260"></canvas>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Contribution Type Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="contributionTypeChart" height="260"></canvas>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Key Metrics</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        {% for metric in key_metrics %}
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">{{ metric.label }}</h6>
                                        <small class="text-muted">{{ metric.period }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="d-block fw-bold">{{ metric.value }}</span>
                                        <small class="{{ metric.change >= 0 ? 'text-success' : 'text-danger' }}">
                                            <i class="fas fa-{{ metric.change >= 0 ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                            {{ metric.change|abs }}%
                                        </small>
                                    </div>
                                </div>
                            </li>
                        {% endfor %}
                    </ul>
                </div>
            </div>
        </div>
    </div>
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form change handlers
            const filterForm = document.getElementById('trends-filter-form');
            const formInputs = filterForm.querySelectorAll('select');

            formInputs.forEach(input => {
                input.addEventListener('change', function() {
                    filterForm.submit();
                });
            });

            // Income vs Expenses Chart
            const incomeExpensesCtx = document.getElementById('incomeExpensesChart').getContext('2d');
            const incomeExpensesChart = new Chart(incomeExpensesCtx, {
                type: 'line',
                data: {
                    labels: {{ chart_data.incomeExpenses.labels|json_encode|raw }},
                    datasets: [
                        {
                            label: 'Income',
                            data: {{ chart_data.incomeExpenses.income|json_encode|raw }},
                            borderColor: 'rgba(25, 135, 84, 1)',
                            backgroundColor: 'rgba(25, 135, 84, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.1
                        },
                        {
                            label: 'Expenses',
                            data: {{ chart_data.incomeExpenses.expenses|json_encode|raw }},
                            borderColor: 'rgba(220, 53, 69, 1)',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.1
                        },
                        {
                            label: 'Net',
                            data: {{ chart_data.incomeExpenses.net|json_encode|raw }},
                            borderColor: 'rgba(13, 110, 253, 1)',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            fill: false,
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '€' + value;
                                }
                            }
                        }
                    }
                }
            });

            // Chart view buttons
            const chartViewButtons = document.querySelectorAll('[data-chart-view]');
            chartViewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    chartViewButtons.forEach(btn => btn.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');

                    // Update chart data based on selected view
                    const view = this.getAttribute('data-chart-view');
                    fetch(`/api/reports/income-expenses-trend?view=${view}&team={{ selected_team }}&period={{ period }}`)
                        .then(response => response.json())
                        .then(data => {
                            incomeExpensesChart.data.labels = data.labels;
                            incomeExpensesChart.data.datasets[0].data = data.income;
                            incomeExpensesChart.data.datasets[1].data = data.expenses;
                            incomeExpensesChart.data.datasets[2].data = data.net;
                            incomeExpensesChart.update();
                        });
                });
            });

            // Payment Trends Chart
            const paymentTrendsCtx = document.getElementById('paymentTrendsChart').getContext('2d');
            const paymentTrendsChart = new Chart(paymentTrendsCtx, {
                type: 'bar',
                data: {
                    labels: {{ chart_data.paymentTrends.labels|json_encode|raw }},
                    datasets: [
                        {
                            label: 'On-time Payments',
                            data: {{ chart_data.paymentTrends.onTime|json_encode|raw }},
                            backgroundColor: 'rgba(25, 135, 84, 0.7)',
                            borderColor: 'rgba(25, 135, 84, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Late Payments',
                            data: {{ chart_data.paymentTrends.late|json_encode|raw }},
                            backgroundColor: 'rgba(255, 193, 7, 0.7)',
                            borderColor: 'rgba(255, 193, 7, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Unpaid',
                            data: {{ chart_data.paymentTrends.unpaid|json_encode|raw }},
                            backgroundColor: 'rgba(220, 53, 69, 0.7)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true
                        }
                    }
                }
            });

            // Payment metric buttons
            const paymentMetricButtons = document.querySelectorAll('[data-payment-metric]');
            paymentMetricButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    paymentMetricButtons.forEach(btn => btn.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');

                    // Update chart data based on selected metric
                    const metric = this.getAttribute('data-payment-metric');
                    fetch(`/api/reports/payment-trends?metric=${metric}&team={{ selected_team }}&period={{ period }}`)
                        .then(response => response.json())
                        .then(data => {
                            paymentTrendsChart.data.datasets[0].data = data.onTime;
                            paymentTrendsChart.data.datasets[1].data = data.late;
                            paymentTrendsChart.data.datasets[2].data = data.unpaid;

                            // Update y-axis label format for amount metric
                            if (metric === 'amount') {
                                paymentTrendsChart.options.scales.y.ticks = {
                                    callback: function(value) {
                                        return '€' + value;
                                    }
                                };
                            } else if (metric === 'rate') {
                                paymentTrendsChart.options.scales.y.ticks = {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                };
                            } else {
                                paymentTrendsChart.options.scales.y.ticks = {
                                    callback: function(value) {
                                        return value;
                                    }
                                };
                            }

                            paymentTrendsChart.update();
                        });
                });
            });

            // Payment Method Chart
            const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
            const paymentMethodChart = new Chart(paymentMethodCtx, {
                type: 'doughnut',
                data: {
                    labels: {{ chart_data.paymentMethods.labels|json_encode|raw }},
                    datasets: [{
                        data: {{ chart_data.paymentMethods.data|json_encode|raw }},
                        backgroundColor: [
                            'rgba(13, 110, 253, 0.7)',
                            'rgba(25, 135, 84, 0.7)',
                            'rgba(255, 193, 7, 0.7)',
                            'rgba(220, 53, 69, 0.7)',
                            'rgba(13, 202, 240, 0.7)'
                        ],
                        borderColor: [
                            'rgba(13, 110, 253, 1)',
                            'rgba(25, 135, 84, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(220, 53, 69, 1)',
                            'rgba(13, 202, 240, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Contribution Type Chart
            const contributionTypeCtx = document.getElementById('contributionTypeChart').getContext('2d');
            const contributionTypeChart = new Chart(contributionTypeCtx, {
                type: 'line',
                data: {
                    labels: {{ chart_data.contributionTypes.labels|json_encode|raw }},
                    datasets: {{ chart_data.contributionTypes.datasets|json_encode|raw }}
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
{% endblock %}

{% block stylesheets %}
    {{ parent() }}
    <style>
        .insight-icon {
            font-size: 1.5rem;
            width: 30px;
            text-align: center;
        }
    </style>
{% endblock %}
```

## Export Center Page

The export center page provides tools for exporting financial data in various formats.

### Design

```twig
{# templates/pages/reports/export.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Export Center | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Export Center</h1>
        <a href="{{ path('reports_dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4 mb-lg-0">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Export Options</h5>
                </div>
                <div class="card-body">
                    {{ form_start(form, {'attr': {'id': 'export-form'}}) }}
                        <div class="mb-3">
                            {{ form_label(form.exportType) }}
                            {{ form_widget(form.exportType, {'attr': {'class': 'form-select'}}) }}
                            <div class="form-text">Select the type of data to export</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.team) }}
                            {{ form_widget(form.team, {'attr': {'class': 'form-select'}}) }}
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.dateRange) }}
                            {{ form_widget(form.dateRange, {'attr': {'class': 'form-control', 'data-daterangepicker': 'true'}}) }}
                            <div class="form-text">Select the date range for the export</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.format) }}
                            {{ form_widget(form.format, {'attr': {'class': 'form-select'}}) }}
                            <div class="form-text">Choose the export format</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.includeDetails) }}
                            {{ form_widget(form.includeDetails, {'attr': {'class': 'form-check-input'}}) }}
                            <div class="form-text">Include detailed information in the export</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-download me-1"></i> Generate Export
                            </button>
                        </div>
                    {{ form_end(form) }}
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Recent Exports</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Format</th>
                                    <th>Date</th>
                                    <th>Size</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {% for export in recent_exports %}
                                    <tr>
                                        <td>{{ export.name }}</td>
                                        <td>{{ export.type }}</td>
                                        <td>
                                            <span class="badge {{ export.format == 'PDF' ? 'bg-danger' : (export.format == 'Excel' ? 'bg-success' : 'bg-primary') }}">
                                                {{ export.format }}
                                            </span>
                                        </td>
                                        <td>{{ export.date|date('M d, Y H:i') }}</td>
                                        <td>{{ export.size }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ path('reports_download_export', {'id': export.id}) }}" class="btn btn-outline-primary">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <a href="{{ path('reports_delete_export', {'id': export.id}) }}" class="btn btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                {% else %}
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <p class="text-muted mb-0">No recent exports found.</p>
                                        </td>
                                    </tr>
                                {% endfor %}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Export Templates</h5>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                {% for template in export_templates %}
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">{{ template.name }}</h6>
                                                <small class="text-muted">{{ template.description }}</small>
                                            </div>
                                            <a href="{{ path('reports_export_template', {'id': template.id}) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download me-1"></i> Export
                                            </a>
                                        </div>
                                    </li>
                                {% else %}
                                    <li class="list-group-item text-center py-4">
                                        <p class="text-muted mb-0">No templates available.</p>
                                    </li>
                                {% endfor %}
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Scheduled Exports</h5>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                {% for schedule in scheduled_exports %}
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">{{ schedule.name }}</h6>
                                                <small class="text-muted">{{ schedule.frequency }} | Next: {{ schedule.nextRun|date('M d, Y') }}</small>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ path('reports_edit_schedule', {'id': schedule.id}) }}" class="btn btn-outline-secondary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ path('reports_delete_schedule', {'id': schedule.id}) }}" class="btn btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                {% else %}
                                    <li class="list-group-item text-center py-4">
                                        <p class="text-muted mb-0">No scheduled exports.</p>
                                        <a href="{{ path('reports_schedule_export') }}" class="btn btn-sm btn-primary mt-2">
                                            Schedule an Export
                                        </a>
                                    </li>
                                {% endfor %}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Export Guidelines</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>File Formats</h6>
                            <ul class="mb-3">
                                <li><strong>PDF</strong> - Best for sharing and printing reports</li>
                                <li><strong>Excel</strong> - Best for data analysis and manipulation</li>
                                <li><strong>CSV</strong> - Best for importing into other systems</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Best Practices</h6>
                            <ul class="mb-0">
                                <li>Use specific date ranges for more focused exports</li>
                                <li>Include details for comprehensive information</li>
                                <li>Schedule regular exports for important reports</li>
                                <li>Use templates for consistent reporting</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize date range picker
            $('[data-daterangepicker="true"]').daterangepicker({
                opens: 'left',
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

            // Export type change handler
            const exportTypeSelect = document.getElementById('export_exportType');
            const teamSelect = document.getElementById('export_team');

            exportTypeSelect.addEventListener('change', function() {
                const exportType = this.value;

                // Enable/disable team select based on export type
                if (exportType === 'all_teams' || exportType === 'system') {
                    teamSelect.disabled = true;
                    teamSelect.value = '';
                } else {
                    teamSelect.disabled = false;
                }
            });
        });
    </script>
{% endblock %}

{% block stylesheets %}
    {{ parent() }}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
{% endblock %}
```

## Integration with Backend

The reports and statistics frontend will integrate with the backend API endpoints:

- `GET /api/reports/dashboard`: Retrieve dashboard statistics and chart data
- `GET /api/reports/team`: Generate team reports with various parameters
- `GET /api/reports/member/{id}`: Retrieve member statistics
- `GET /api/reports/trends`: Retrieve financial trend data
- `GET /api/reports/income-expenses`: Retrieve income and expenses data
- `GET /api/reports/income-expenses-trend`: Retrieve income and expenses trend data
- `GET /api/reports/payment-trends`: Retrieve payment trend data
- `POST /api/reports/export`: Generate and download data exports
- `GET /api/reports/export/{id}/download`: Download a previously generated export
- `DELETE /api/reports/export/{id}`: Delete an export
- `GET /api/reports/export-templates`: Retrieve export templates
- `GET /api/reports/export-template/{id}`: Generate an export from a template
- `GET /api/reports/scheduled-exports`: Retrieve scheduled exports
- `POST /api/reports/schedule-export`: Create a scheduled export
- `PUT /api/reports/scheduled-export/{id}`: Update a scheduled export
- `DELETE /api/reports/scheduled-export/{id}`: Delete a scheduled export

## JavaScript Enhancements

The reports and statistics pages will include JavaScript enhancements for a better user experience:

1. **Interactive Charts**: Dynamic, interactive charts with filtering and time period selection
2. **Date Range Pickers**: Enhanced date selection for filtering and reporting
3. **Real-time Data Updates**: Ability to refresh data without reloading the page
4. **Export Functionality**: Client-side export to PDF and other formats
5. **Responsive Visualizations**: Charts and graphs that adapt to different screen sizes
6. **Data Filtering**: Dynamic filtering of tabular data

## Responsive Design Considerations

The reports and statistics pages will be fully responsive:

1. **Mobile-Friendly Charts**: Charts and graphs that adapt to smaller screens
2. **Stacked Layouts**: Vertical stacking of content on smaller screens
3. **Touch-Friendly Controls**: Larger touch targets for mobile users
4. **Simplified Views**: Condensed data views for mobile devices
5. **Horizontal Scrolling**: For data tables that are too wide for mobile screens

## Accessibility Features

The reports and statistics pages will include the following accessibility features:

1. **ARIA Labels**: Proper ARIA labels for all interactive elements
2. **Keyboard Navigation**: Full keyboard navigation support for all interactive elements
3. **Screen Reader Support**: Semantic HTML and appropriate text alternatives for charts
4. **Color Contrast**: High contrast for all text elements and chart colors
5. **Focus Indicators**: Clear focus indicators for keyboard navigation
6. **Alternative Text**: Text alternatives for all charts and visualizations

## Next Steps

After implementing the reports and statistics pages, the next step will be to implement the user profile and settings pages as outlined in [09-user-profile-settings.md](09-user-profile-settings.md).
