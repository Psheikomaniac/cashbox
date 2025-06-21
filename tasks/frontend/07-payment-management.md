# Payment Management

## Overview

The payment management section of the Cashbox application allows users to process, track, and manage payments for contributions and penalties. This document outlines the design and implementation of the payment management pages, which provide a comprehensive interface for handling financial transactions within teams.

## User Stories

1. As a team member, I want to record payments for my contributions and penalties so that I can fulfill my financial obligations
2. As a team admin, I want to confirm payments made by team members so that I can maintain accurate financial records
3. As a team admin, I want to view payment history so that I can track financial activity within the team
4. As a team member, I want to view my payment history so that I can keep track of my financial contributions
5. As a team admin, I want to generate payment reports so that I can share financial information with the team

## Payment Management Pages

The payment management section consists of the following pages:

1. **Payment Dashboard**: Overview of payment status and recent activity
2. **Payment Processing**: Interface for recording and confirming payments
3. **Payment History**: Detailed view of all payments with filtering options
4. **Payment Reports**: Tools for generating and exporting payment reports

## Payment Dashboard Page

The payment dashboard provides an overview of payment status, recent activity, and quick actions for processing payments.

### Design

```twig
{# templates/pages/payments/dashboard.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Payment Dashboard | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Payment Dashboard</h1>
        <div class="btn-group">
            <a href="{{ path('payment_process') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Record Payment
            </a>
            {% if is_granted('ROLE_TEAM_ADMIN') %}
                <a href="{{ path('payment_reports') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-file-export me-1"></i> Reports
                </a>
            {% endif %}
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Pending Payments</h6>
                    <p class="h3 mb-0">{{ stats.pendingCount }}</p>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ stats.pendingPercentage }}%"></div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="{{ path('payment_history', {'filter': 'pending'}) }}" class="btn btn-sm btn-outline-secondary w-100">View Pending</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Completed Payments</h6>
                    <p class="h3 mb-0">{{ stats.completedCount }}</p>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ stats.completedPercentage }}%"></div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="{{ path('payment_history', {'filter': 'completed'}) }}" class="btn btn-sm btn-outline-secondary w-100">View Completed</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3 mb-md-0">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Amount Due</h6>
                    <p class="h3 mb-0">{{ stats.totalDue|format_currency('EUR') }}</p>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ stats.duePercentage }}%"></div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="{{ path('payment_process') }}" class="btn btn-sm btn-outline-primary w-100">Make Payment</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Collected</h6>
                    <p class="h3 mb-0">{{ stats.totalCollected|format_currency('EUR') }}</p>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ stats.collectedPercentage }}%"></div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    {% if is_granted('ROLE_TEAM_ADMIN') %}
                        <a href="{{ path('payment_reports') }}" class="btn btn-sm btn-outline-secondary w-100">Generate Report</a>
                    {% else %}
                        <a href="{{ path('payment_history') }}" class="btn btn-sm btn-outline-secondary w-100">View History</a>
                    {% endif %}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Payments</h5>
                    <a href="{{ path('payment_history') }}" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Reference</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {% for payment in recent_payments %}
                                    <tr>
                                        <td>
                                            <a href="{{ path('payment_details', {'id': payment.id}) }}" class="text-decoration-none">
                                                {{ payment.reference }}
                                            </a>
                                        </td>
                                        <td>
                                            {% if payment.type == 'contribution' %}
                                                <span class="badge bg-primary">Contribution</span>
                                            {% elseif payment.type == 'penalty' %}
                                                <span class="badge bg-warning text-dark">Penalty</span>
                                            {% else %}
                                                <span class="badge bg-secondary">Other</span>
                                            {% endif %}
                                        </td>
                                        <td>{{ payment.amount|format_currency(payment.currency) }}</td>
                                        <td>{{ payment.createdAt|date('M d, Y') }}</td>
                                        <td>
                                            {% if payment.status == 'completed' %}
                                                <span class="badge bg-success">Completed</span>
                                            {% elseif payment.status == 'pending' %}
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            {% elseif payment.status == 'failed' %}
                                                <span class="badge bg-danger">Failed</span>
                                            {% endif %}
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ path('payment_details', {'id': payment.id}) }}" class="btn btn-outline-secondary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                {% if payment.status == 'pending' and is_granted('ROLE_TEAM_ADMIN') %}
                                                    <a href="{{ path('payment_confirm', {'id': payment.id}) }}" class="btn btn-outline-success">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                {% endif %}
                                            </div>
                                        </td>
                                    </tr>
                                {% else %}
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <p class="text-muted mb-0">No recent payments found.</p>
                                            <a href="{{ path('payment_process') }}" class="btn btn-sm btn-primary mt-2">
                                                Record your first payment
                                            </a>
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
                    <h5 class="card-title mb-0">Pending Approvals</h5>
                </div>
                <div class="card-body p-0">
                    {% if is_granted('ROLE_TEAM_ADMIN') %}
                        <ul class="list-group list-group-flush">
                            {% for payment in pending_approvals %}
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">{{ payment.user.fullName }}</h6>
                                            <small class="text-muted">{{ payment.amount|format_currency(payment.currency) }} - {{ payment.createdAt|date('M d, Y') }}</small>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ path('payment_details', {'id': payment.id}) }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ path('payment_confirm', {'id': payment.id}) }}" class="btn btn-outline-success">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="{{ path('payment_reject', {'id': payment.id}) }}" class="btn btn-outline-danger">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            {% else %}
                                <li class="list-group-item text-center py-4">
                                    <p class="text-muted mb-0">No pending approvals.</p>
                                </li>
                            {% endfor %}
                        </ul>
                    {% else %}
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Only team administrators can approve payments.</p>
                        </div>
                    {% endif %}
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ path('payment_process') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Record Payment
                        </a>
                        <a href="{{ path('payment_history') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-history me-1"></i> Payment History
                        </a>
                        {% if is_granted('ROLE_TEAM_ADMIN') %}
                            <a href="{{ path('payment_reports') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-file-export me-1"></i> Generate Reports
                            </a>
                        {% endif %}
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

## Payment Processing Page

The payment processing page allows users to record payments for contributions and penalties.

### Design

```twig
{# templates/pages/payments/process.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Record Payment | Cashbox{% endblock %}

{% block content %}
    <div class="mb-4">
        <h1 class="h3 mb-0">Record Payment</h1>
        <p class="text-muted">Record a payment for contributions or penalties</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    {{ form_start(form, {'attr': {'class': 'needs-validation', 'novalidate': 'novalidate'}}) }}
                        <div class="mb-3">
                            {{ form_label(form.paymentType) }}
                            {{ form_widget(form.paymentType, {'attr': {'class': 'form-select' ~ (form.paymentType.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(form.paymentType) }}
                            <div class="form-text">Select what you're paying for</div>
                        </div>

                        <div class="mb-3 payment-section" id="contribution-section">
                            {{ form_label(form.contributions) }}
                            {{ form_widget(form.contributions, {'attr': {'class': 'form-select' ~ (form.contributions.vars.valid ? '' : ' is-invalid'), 'data-choices': 'true', 'multiple': 'multiple'}}) }}
                            {{ form_errors(form.contributions) }}
                            <div class="form-text">Select the contributions you want to pay</div>
                        </div>

                        <div class="mb-3 payment-section" id="penalty-section" style="display: none;">
                            {{ form_label(form.penalties) }}
                            {{ form_widget(form.penalties, {'attr': {'class': 'form-select' ~ (form.penalties.vars.valid ? '' : ' is-invalid'), 'data-choices': 'true', 'multiple': 'multiple'}}) }}
                            {{ form_errors(form.penalties) }}
                            <div class="form-text">Select the penalties you want to pay</div>
                        </div>

                        <div class="mb-3 payment-section" id="custom-section" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    {{ form_label(form.amount) }}
                                    <div class="input-group">
                                        <span class="input-group-text">€</span>
                                        {{ form_widget(form.amount, {'attr': {'class': 'form-control' ~ (form.amount.vars.valid ? '' : ' is-invalid')}}) }}
                                        {{ form_errors(form.amount) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    {{ form_label(form.team) }}
                                    {{ form_widget(form.team, {'attr': {'class': 'form-select' ~ (form.team.vars.valid ? '' : ' is-invalid')}}) }}
                                    {{ form_errors(form.team) }}
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.paymentMethod) }}
                            {{ form_widget(form.paymentMethod, {'attr': {'class': 'form-select' ~ (form.paymentMethod.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(form.paymentMethod) }}
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.notes) }}
                            {{ form_widget(form.notes, {'attr': {'class': 'form-control' ~ (form.notes.vars.valid ? '' : ' is-invalid'), 'rows': '3'}}) }}
                            {{ form_errors(form.notes) }}
                            <div class="form-text">Optional additional information about this payment</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.proofOfPayment) }}
                            {{ form_widget(form.proofOfPayment, {'attr': {'class': 'form-control' ~ (form.proofOfPayment.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(form.proofOfPayment) }}
                            <div class="form-text">Optional: Upload a receipt or screenshot of the payment</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ path('payment_dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Record Payment</button>
                        </div>
                    {{ form_end(form) }}
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Payment Summary</h5>
                </div>
                <div class="card-body">
                    <div id="payment-summary">
                        <p class="text-muted text-center mb-0">Select items to pay to see the summary</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Payment Guidelines</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">Select the type of payment you want to make</li>
                        <li class="mb-2">Choose the specific items you're paying for</li>
                        <li class="mb-2">Provide proof of payment when possible</li>
                        <li>Add notes to clarify any special circumstances</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle payment sections based on payment type
            const paymentTypeSelect = document.querySelector('#payment_paymentType');
            const contributionSection = document.querySelector('#contribution-section');
            const penaltySection = document.querySelector('#penalty-section');
            const customSection = document.querySelector('#custom-section');
            const paymentSummary = document.querySelector('#payment-summary');

            paymentTypeSelect.addEventListener('change', function() {
                // Hide all sections first
                contributionSection.style.display = 'none';
                penaltySection.style.display = 'none';
                customSection.style.display = 'none';

                // Show the appropriate section
                if (this.value === 'contribution') {
                    contributionSection.style.display = 'block';
                } else if (this.value === 'penalty') {
                    penaltySection.style.display = 'block';
                } else if (this.value === 'custom') {
                    customSection.style.display = 'block';
                }

                // Reset the payment summary
                paymentSummary.innerHTML = '<p class="text-muted text-center mb-0">Select items to pay to see the summary</p>';
            });

            // Update payment summary when contributions are selected
            const contributionSelect = document.querySelector('#payment_contributions');
            contributionSelect.addEventListener('change', function() {
                updatePaymentSummary('contribution');
            });

            // Update payment summary when penalties are selected
            const penaltySelect = document.querySelector('#payment_penalties');
            penaltySelect.addEventListener('change', function() {
                updatePaymentSummary('penalty');
            });

            // Update payment summary when custom amount changes
            const amountInput = document.querySelector('#payment_amount');
            amountInput.addEventListener('input', function() {
                updatePaymentSummary('custom');
            });

            // Function to update the payment summary
            function updatePaymentSummary(type) {
                let summaryHTML = '';
                let totalAmount = 0;

                if (type === 'contribution') {
                    const selectedOptions = Array.from(contributionSelect.selectedOptions);
                    if (selectedOptions.length > 0) {
                        summaryHTML += '<h6 class="mb-3">Selected Contributions:</h6>';
                        summaryHTML += '<ul class="list-group mb-3">';

                        selectedOptions.forEach(option => {
                            const contribution = JSON.parse(option.dataset.details);
                            summaryHTML += `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span>${contribution.description}</span>
                                        <small class="d-block text-muted">Due: ${contribution.dueDate}</small>
                                    </div>
                                    <span>${contribution.amount}</span>
                                </li>
                            `;
                            totalAmount += parseFloat(contribution.amountValue);
                        });

                        summaryHTML += '</ul>';
                    }
                } else if (type === 'penalty') {
                    const selectedOptions = Array.from(penaltySelect.selectedOptions);
                    if (selectedOptions.length > 0) {
                        summaryHTML += '<h6 class="mb-3">Selected Penalties:</h6>';
                        summaryHTML += '<ul class="list-group mb-3">';

                        selectedOptions.forEach(option => {
                            const penalty = JSON.parse(option.dataset.details);
                            summaryHTML += `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span>${penalty.description}</span>
                                        <small class="d-block text-muted">${penalty.category}</small>
                                    </div>
                                    <span>${penalty.amount}</span>
                                </li>
                            `;
                            totalAmount += parseFloat(penalty.amountValue);
                        });

                        summaryHTML += '</ul>';
                    }
                } else if (type === 'custom') {
                    totalAmount = parseFloat(amountInput.value) || 0;
                }

                if (totalAmount > 0) {
                    summaryHTML += `
                        <div class="d-flex justify-content-between align-items-center fw-bold">
                            <span>Total Amount:</span>
                            <span>${new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(totalAmount)}</span>
                        </div>
                    `;
                } else {
                    summaryHTML = '<p class="text-muted text-center mb-0">Select items to pay to see the summary</p>';
                }

                paymentSummary.innerHTML = summaryHTML;
            }
        });
    </script>
{% endblock %}
```

## Payment History Page

The payment history page provides a detailed view of all payments with filtering and sorting options.

### Design

```twig
{# templates/pages/payments/history.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Payment History | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Payment History</h1>
        <div class="btn-group">
            <a href="{{ path('payment_process') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Record Payment
            </a>
            {% if is_granted('ROLE_TEAM_ADMIN') %}
                <a href="{{ path('payment_reports') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-file-export me-1"></i> Reports
                </a>
            {% endif %}
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <form class="row g-3" method="get" action="{{ path('payment_history') }}">
                <div class="col-md-3">
                    <label for="filter-status" class="form-label">Status</label>
                    <select id="filter-status" name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ app.request.query.get('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ app.request.query.get('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ app.request.query.get('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter-type" class="form-label">Type</label>
                    <select id="filter-type" name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="contribution" {{ app.request.query.get('type') == 'contribution' ? 'selected' : '' }}>Contribution</option>
                        <option value="penalty" {{ app.request.query.get('type') == 'penalty' ? 'selected' : '' }}>Penalty</option>
                        <option value="custom" {{ app.request.query.get('type') == 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter-date-from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="filter-date-from" name="dateFrom" value="{{ app.request.query.get('dateFrom') }}">
                </div>
                <div class="col-md-3">
                    <label for="filter-date-to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="filter-date-to" name="dateTo" value="{{ app.request.query.get('dateTo') }}">
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ path('payment_history') }}" class="btn btn-outline-secondary ms-2">Reset</a>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {% for payment in payments %}
                            <tr>
                                <td>
                                    <a href="{{ path('payment_details', {'id': payment.id}) }}" class="text-decoration-none">
                                        {{ payment.reference }}
                                    </a>
                                </td>
                                <td>
                                    {% if payment.type == 'contribution' %}
                                        <span class="badge bg-primary">Contribution</span>
                                    {% elseif payment.type == 'penalty' %}
                                        <span class="badge bg-warning text-dark">Penalty</span>
                                    {% else %}
                                        <span class="badge bg-secondary">Custom</span>
                                    {% endif %}
                                </td>
                                <td>{{ payment.amount|format_currency(payment.currency) }}</td>
                                <td>{{ payment.createdAt|date('M d, Y') }}</td>
                                <td>{{ payment.method }}</td>
                                <td>
                                    {% if payment.status == 'completed' %}
                                        <span class="badge bg-success">Completed</span>
                                    {% elseif payment.status == 'pending' %}
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    {% elseif payment.status == 'failed' %}
                                        <span class="badge bg-danger">Failed</span>
                                    {% endif %}
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ path('payment_details', {'id': payment.id}) }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        {% if payment.status == 'pending' %}
                                            {% if is_granted('ROLE_TEAM_ADMIN') %}
                                                <a href="{{ path('payment_confirm', {'id': payment.id}) }}" class="btn btn-outline-success">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="{{ path('payment_reject', {'id': payment.id}) }}" class="btn btn-outline-danger">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            {% elseif app.user == payment.user %}
                                                <a href="{{ path('payment_cancel', {'id': payment.id}) }}" class="btn btn-outline-danger">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            {% endif %}
                                        {% endif %}
                                    </div>
                                </td>
                            </tr>
                        {% else %}
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-0">No payments found matching your criteria.</p>
                                </td>
                            </tr>
                        {% endfor %}
                    </tbody>
                </table>
            </div>
        </div>
        {% if payments|length > 0 %}
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">Showing {{ payments|length }} of {{ total_payments }} payments</small>
                    </div>
                    {% include 'components/pagination.html.twig' with {
                        'current_page': current_page,
                        'total_pages': total_pages,
                        'route': 'payment_history',
                        'route_params': app.request.query.all
                    } %}
                </div>
            </div>
        {% endif %}
    </div>
{% endblock %}
```

## Payment Details Page

The payment details page shows comprehensive information about a specific payment.

### Design

```twig
{# templates/pages/payments/details.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Payment Details | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Payment Details</h1>
            <p class="text-muted mb-0">Reference: {{ payment.reference }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ path('payment_history') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to History
            </a>
            {% if payment.status == 'pending' %}
                {% if is_granted('ROLE_TEAM_ADMIN') %}
                    <a href="{{ path('payment_confirm', {'id': payment.id}) }}" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Confirm Payment
                    </a>
                    <a href="{{ path('payment_reject', {'id': payment.id}) }}" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i> Reject Payment
                    </a>
                {% elseif app.user == payment.user %}
                    <a href="{{ path('payment_cancel', {'id': payment.id}) }}" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i> Cancel Payment
                    </a>
                {% endif %}
            {% endif %}
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Amount</h6>
                            <p class="h4 mb-0">{{ payment.amount|format_currency(payment.currency) }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Status</h6>
                            <p class="mb-0">
                                {% if payment.status == 'completed' %}
                                    <span class="badge bg-success">Completed</span>
                                {% elseif payment.status == 'pending' %}
                                    <span class="badge bg-warning text-dark">Pending</span>
                                {% elseif payment.status == 'failed' %}
                                    <span class="badge bg-danger">Failed</span>
                                {% endif %}
                            </p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Type</h6>
                            <p class="mb-0">
                                {% if payment.type == 'contribution' %}
                                    <span class="badge bg-primary">Contribution</span>
                                {% elseif payment.type == 'penalty' %}
                                    <span class="badge bg-warning text-dark">Penalty</span>
                                {% else %}
                                    <span class="badge bg-secondary">Custom</span>
                                {% endif %}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Payment Method</h6>
                            <p class="mb-0">{{ payment.method }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Created By</h6>
                            <p class="mb-0">{{ payment.user.fullName }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Created On</h6>
                            <p class="mb-0">{{ payment.createdAt|date('F d, Y H:i') }}</p>
                        </div>
                    </div>
                    {% if payment.notes %}
                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Notes</h6>
                            <p class="mb-0">{{ payment.notes|nl2br }}</p>
                        </div>
                    {% endif %}
                    {% if payment.status == 'completed' %}
                        <div class="row mb-0">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Confirmed By</h6>
                                <p class="mb-0">{{ payment.confirmedBy ? payment.confirmedBy.fullName : 'System' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Confirmed On</h6>
                                <p class="mb-0">{{ payment.confirmedAt|date('F d, Y H:i') }}</p>
                            </div>
                        </div>
                    {% endif %}
                </div>
            </div>

            {% if payment.items|length > 0 %}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Payment Items</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Description</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {% for item in payment.items %}
                                        <tr>
                                            <td>
                                                {% if item.type == 'contribution' %}
                                                    <a href="{{ path('contribution_details', {'id': item.itemId}) }}" class="text-decoration-none">
                                                        {{ item.description }}
                                                    </a>
                                                {% elseif item.type == 'penalty' %}
                                                    <a href="{{ path('penalty_details', {'id': item.itemId}) }}" class="text-decoration-none">
                                                        {{ item.description }}
                                                    </a>
                                                {% else %}
                                                    {{ item.description }}
                                                {% endif %}
                                            </td>
                                            <td>
                                                {% if item.type == 'contribution' %}
                                                    <span class="badge bg-primary">Contribution</span>
                                                {% elseif item.type == 'penalty' %}
                                                    <span class="badge bg-warning text-dark">Penalty</span>
                                                {% else %}
                                                    <span class="badge bg-secondary">Custom</span>
                                                {% endif %}
                                            </td>
                                            <td>{{ item.amount|format_currency(payment.currency) }}</td>
                                        </tr>
                                    {% endfor %}
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="2" class="text-end">Total:</th>
                                        <th>{{ payment.amount|format_currency(payment.currency) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            {% endif %}
        </div>

        <div class="col-lg-4">
            {% if payment.proofOfPayment %}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Proof of Payment</h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ asset('uploads/payment_proofs/' ~ payment.proofOfPayment) }}" alt="Proof of Payment" class="img-fluid mb-3 rounded">
                        <a href="{{ asset('uploads/payment_proofs/' ~ payment.proofOfPayment) }}" class="btn btn-outline-primary btn-sm" download>
                            <i class="fas fa-download me-1"></i> Download
                        </a>
                    </div>
                </div>
            {% endif %}

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Team Information</h5>
                </div>
                <div class="card-body">
                    <h6 class="text-muted mb-1">Team</h6>
                    <p class="mb-3">
                        <a href="{{ path('team_details', {'id': payment.team.id}) }}" class="text-decoration-none">
                            {{ payment.team.name }}
                        </a>
                    </p>

                    <h6 class="text-muted mb-1">Payment Instructions</h6>
                    <p class="mb-0">{{ payment.team.paymentInstructions|nl2br }}</p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Activity Timeline</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        {% for activity in payment.activities %}
                            <li class="list-group-item">
                                <div class="d-flex">
                                    <div class="timeline-icon me-3">
                                        {% if activity.type == 'created' %}
                                            <i class="fas fa-plus-circle text-primary"></i>
                                        {% elseif activity.type == 'confirmed' %}
                                            <i class="fas fa-check-circle text-success"></i>
                                        {% elseif activity.type == 'rejected' %}
                                            <i class="fas fa-times-circle text-danger"></i>
                                        {% elseif activity.type == 'cancelled' %}
                                            <i class="fas fa-ban text-secondary"></i>
                                        {% endif %}
                                    </div>
                                    <div>
                                        <p class="mb-0">{{ activity.description }}</p>
                                        <small class="text-muted">
                                            {{ activity.createdAt|date('M d, Y H:i') }} by {{ activity.user.fullName }}
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
```

## Payment Reports Page

The payment reports page allows team administrators to generate and export payment reports.

### Design

```twig
{# templates/pages/payments/reports.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Payment Reports | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Payment Reports</h1>
        <a href="{{ path('payment_history') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to History
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Generate Report</h5>
                </div>
                <div class="card-body">
                    {{ form_start(form, {'attr': {'class': 'needs-validation', 'novalidate': 'novalidate'}}) }}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                {{ form_label(form.reportType) }}
                                {{ form_widget(form.reportType, {'attr': {'class': 'form-select' ~ (form.reportType.vars.valid ? '' : ' is-invalid')}}) }}
                                {{ form_errors(form.reportType) }}
                                <div class="form-text">Select the type of report to generate</div>
                            </div>
                            <div class="col-md-6">
                                {{ form_label(form.team) }}
                                {{ form_widget(form.team, {'attr': {'class': 'form-select' ~ (form.team.vars.valid ? '' : ' is-invalid')}}) }}
                                {{ form_errors(form.team) }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                {{ form_label(form.dateFrom) }}
                                {{ form_widget(form.dateFrom, {'attr': {'class': 'form-control' ~ (form.dateFrom.vars.valid ? '' : ' is-invalid')}}) }}
                                {{ form_errors(form.dateFrom) }}
                            </div>
                            <div class="col-md-6">
                                {{ form_label(form.dateTo) }}
                                {{ form_widget(form.dateTo, {'attr': {'class': 'form-control' ~ (form.dateTo.vars.valid ? '' : ' is-invalid')}}) }}
                                {{ form_errors(form.dateTo) }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                {{ form_label(form.includeDetails) }}
                                {{ form_widget(form.includeDetails, {'attr': {'class': 'form-check-input' ~ (form.includeDetails.vars.valid ? '' : ' is-invalid')}}) }}
                                {{ form_errors(form.includeDetails) }}
                                <div class="form-text">Include detailed breakdown of payments</div>
                            </div>
                            <div class="col-md-6">
                                {{ form_label(form.format) }}
                                {{ form_widget(form.format, {'attr': {'class': 'form-select' ~ (form.format.vars.valid ? '' : ' is-invalid')}}) }}
                                {{ form_errors(form.format) }}
                                <div class="form-text">Select the export format</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Generate Report</button>
                        </div>
                    {{ form_end(form) }}
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Recent Reports</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Report Name</th>
                                    <th>Type</th>
                                    <th>Date Range</th>
                                    <th>Generated On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {% for report in recent_reports %}
                                    <tr>
                                        <td>{{ report.name }}</td>
                                        <td>{{ report.type }}</td>
                                        <td>{{ report.dateFrom|date('M d, Y') }} - {{ report.dateTo|date('M d, Y') }}</td>
                                        <td>{{ report.createdAt|date('M d, Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ path('payment_report_download', {'id': report.id}) }}" class="btn btn-outline-primary">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <a href="{{ path('payment_report_delete', {'id': report.id}) }}" class="btn btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                {% else %}
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <p class="text-muted mb-0">No reports generated yet.</p>
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
                    <h5 class="card-title mb-0">Report Types</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0">
                            <h6 class="mb-1">Payment Summary</h6>
                            <p class="text-muted mb-0 small">Overview of all payments with totals by status and type</p>
                        </li>
                        <li class="list-group-item px-0">
                            <h6 class="mb-1">Member Contributions</h6>
                            <p class="text-muted mb-0 small">Breakdown of payments by team member</p>
                        </li>
                        <li class="list-group-item px-0">
                            <h6 class="mb-1">Payment Methods</h6>
                            <p class="text-muted mb-0 small">Analysis of payment methods used</p>
                        </li>
                        <li class="list-group-item px-0">
                            <h6 class="mb-1">Outstanding Balances</h6>
                            <p class="text-muted mb-0 small">List of pending payments and outstanding amounts</p>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Report Guidelines</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">Select a specific date range for more focused reports</li>
                        <li class="mb-2">Include details for comprehensive information</li>
                        <li class="mb-2">Choose PDF format for sharing with team members</li>
                        <li>Use CSV format for further data analysis</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

## Integration with Backend

The payment management frontend will integrate with the backend API endpoints:

- `GET /payments`: Retrieve all payments
- `GET /payments/pending`: Retrieve pending payments
- `GET /payments/team/{teamId}`: Retrieve payments for a specific team
- `GET /payments/user/{userId}`: Retrieve payments for a specific user
- `GET /payments/{id}`: Retrieve a specific payment
- `POST /payments`: Create a new payment
- `POST /payments/{id}/confirm`: Confirm a payment
- `POST /payments/{id}/reject`: Reject a payment
- `POST /payments/{id}/cancel`: Cancel a payment
- `GET /payments/reports`: Retrieve payment reports
- `POST /payments/reports`: Generate a new payment report
- `GET /payments/reports/{id}/download`: Download a payment report
- `DELETE /payments/reports/{id}`: Delete a payment report

## JavaScript Enhancements

The payment management pages will include JavaScript enhancements for a better user experience:

1. **Dynamic Payment Summary**: Real-time calculation of payment totals when selecting items
2. **Form Validation**: Client-side validation for payment forms
3. **Date Range Pickers**: Enhanced date selection for filtering and reporting
4. **File Upload Preview**: Preview of payment proof images before upload
5. **Confirmation Dialogs**: Confirmation dialogs for payment actions
6. **Filtering and Sorting**: Dynamic filtering and sorting of payment lists

## Responsive Design Considerations

The payment management pages will be fully responsive:

1. **Mobile View**: On small screens, the table view will adapt to a card-based layout for better readability
2. **Touch-Friendly Controls**: Larger touch targets for mobile users
3. **Simplified Forms**: Multi-step forms on mobile for better usability
4. **Optimized Dashboard**: Stacked statistics cards on smaller screens

## Accessibility Features

The payment management pages will include the following accessibility features:

1. **ARIA Labels**: Proper ARIA labels for all interactive elements
2. **Keyboard Navigation**: Full keyboard navigation support
3. **Screen Reader Support**: Semantic HTML and appropriate text alternatives
4. **Color Contrast**: High contrast for all text elements
5. **Focus Indicators**: Clear focus indicators for keyboard navigation

## Next Steps

After implementing the payment management pages, the next step will be to implement the reports and statistics pages as outlined in [08-reports-statistics.md](08-reports-statistics.md).
