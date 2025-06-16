# Complete Financial Management System

## Introduction

This document provides a comprehensive overview of the financial management system for the Cashbox application, consolidating contribution management, penalty management, and payment processing into a unified user experience. The system enables teams to track contributions, manage penalties, process payments, and maintain complete financial oversight.

## User Needs and Goals

### Primary User Needs
1. **Contribution Management**: Create, track, and collect team contributions efficiently
2. **Penalty Management**: Issue, track, and collect penalty payments for rule violations
3. **Payment Processing**: Record, verify, and track all financial transactions
4. **Financial Oversight**: Maintain complete visibility into team financial status
5. **Member Accountability**: Track individual financial responsibilities and compliance
6. **Administrative Control**: Provide appropriate controls for team administrators

### User Roles and Permissions
- **Team Admin**: Full access to create, edit, delete, and verify all financial items
- **Team Member**: View own items, record payments, limited creation based on team settings
- **System Admin**: Full system access across all teams

## Contribution Management

### Overview
Contributions represent planned financial obligations for team expenses, events, or ongoing costs. They can be one-time or recurring and are assigned to team members based on admin decisions.

### User Stories
1. As a team admin, I want to create contributions for team expenses so members know what they need to pay
2. As a team member, I want to see my pending contributions and due dates so I can plan my payments
3. As a team admin, I want to track contribution payment progress to ensure full collection
4. As a team member, I want to mark my contributions as paid to update my status

### Features

#### Contribution List Page
- **Filtering**: By status (pending, completed, overdue), date range, member, category
- **Sorting**: By due date, amount, creation date, completion status
- **Search**: Find contributions by title or description
- **Bulk Actions**: Export, send reminders, archive old contributions

#### Contribution Creation
- **Basic Information**: Title, description, amount, due date, category
- **Member Selection**: Choose all members or specific individuals
- **Recurring Options**: Set frequency (weekly, monthly, quarterly, yearly)
- **Notification Settings**: Auto-notify members when created
- **Categories**: Organize contributions by type (equipment, events, fees, etc.)

#### Payment Tracking
- **Individual Status**: Track each member's payment status
- **Progress Visualization**: Show completion percentage and amounts
- **Payment Methods**: Support multiple payment methods
- **Verification Workflow**: Admin approval for large amounts

### Implementation

#### Contribution List Template
```twig
{# templates/pages/contributions/list.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Contributions | {{ team.name }} | Cashbox{% endblock %}

{% block content %}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Contributions</h1>
        <p class="text-muted mb-0">Manage team financial contributions</p>
    </div>
    <div class="btn-group">
        {% if is_granted('ROLE_TEAM_ADMIN') or team_settings.membersCanCreateContributions %}
            <a href="{{ path('contribution_create', {'team': team.id}) }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Contribution
            </a>
        {% endif %}
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ path('contribution_export', {'team': team.id}) }}">
                    <i class="fas fa-file-export me-2"></i> Export Data
                </a></li>
                <li><a class="dropdown-item" href="{{ path('contribution_recurring', {'team': team.id}) }}">
                    <i class="fas fa-sync me-2"></i> Manage Recurring
                </a></li>
            </ul>
        </div>
    </div>
</div>

{# Filters Card #}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ app.request.query.get('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ app.request.query.get('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="overdue" {{ app.request.query.get('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_range" class="form-label">Date Range</label>
                <select id="date_range" name="date_range" class="form-select">
                    <option value="">All Time</option>
                    <option value="this_month" {{ app.request.query.get('date_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="this_year" {{ app.request.query.get('date_range') == 'this_year' ? 'selected' : '' }}>This Year</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label">Category</label>
                <select id="category" name="category" class="form-select">
                    <option value="">All Categories</option>
                    {% for category in contribution_categories %}
                        <option value="{{ category.id }}" {{ app.request.query.get('category') == category.id ? 'selected' : '' }}>
                            {{ category.name }}
                        </option>
                    {% endfor %}
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <a href="{{ path('contribution_list', {'team': team.id}) }}" class="btn btn-outline-secondary">
                    Clear
                </a>
            </div>
        </form>
    </div>
</div>

{# Contributions Table #}
<div class="card shadow-sm">
    <div class="card-body p-0">
        {% if contributions|length > 0 %}
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Contribution</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {% for contribution in contributions %}
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="mb-0">
                                                <a href="{{ path('contribution_detail', {'team': team.id, 'id': contribution.id}) }}" 
                                                   class="text-decoration-none">
                                                    {{ contribution.title }}
                                                </a>
                                            </h6>
                                            {% if contribution.description %}
                                                <small class="text-muted">{{ contribution.description|slice(0, 60) }}...</small>
                                            {% endif %}
                                            {% if contribution.isRecurring %}
                                                <span class="badge bg-info ms-2">
                                                    <i class="fas fa-sync-alt"></i> Recurring
                                                </span>
                                            {% endif %}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ contribution.amount|format_currency(team.currency) }}</span>
                                </td>
                                <td>
                                    {{ contribution.dueDate|date('M d, Y') }}
                                    {% if contribution.isOverdue %}
                                        <span class="badge bg-danger ms-1">Overdue</span>
                                    {% endif %}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <small>{{ contribution.paidCount }}/{{ contribution.totalCount }}</small>
                                        </div>
                                        <div class="progress flex-grow-1" style="height: 6px; min-width: 80px;">
                                            {% set progress = contribution.totalCount > 0 ? (contribution.paidCount / contribution.totalCount * 100) : 0 %}
                                            <div class="progress-bar {{ contribution.status == 'completed' ? 'bg-success' : (contribution.status == 'overdue' ? 'bg-danger' : 'bg-primary') }}" 
                                                 role="progressbar" 
                                                 style="width: {{ progress }}%">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {% if contribution.status == 'completed' %}
                                        <span class="badge bg-success">Completed</span>
                                    {% elseif contribution.status == 'overdue' %}
                                        <span class="badge bg-danger">Overdue</span>
                                    {% else %}
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    {% endif %}
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ path('contribution_detail', {'team': team.id, 'id': contribution.id}) }}" 
                                           class="btn btn-outline-secondary" 
                                           data-bs-toggle="tooltip" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        {% if is_granted('ROLE_TEAM_ADMIN') or (contribution.createdBy.id == app.user.id) %}
                                            <a href="{{ path('contribution_edit', {'team': team.id, 'id': contribution.id}) }}" 
                                               class="btn btn-outline-primary" 
                                               data-bs-toggle="tooltip" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        {% endif %}
                                        {% if contribution.userCanPay(app.user) and not contribution.userHasPaid(app.user) %}
                                            <a href="{{ path('payment_create', {'team': team.id, 'contribution': contribution.id}) }}" 
                                               class="btn btn-outline-success" 
                                               data-bs-toggle="tooltip" 
                                               title="Pay Now">
                                                <i class="fas fa-credit-card"></i>
                                            </a>
                                        {% endif %}
                                    </div>
                                </td>
                            </tr>
                        {% endfor %}
                    </tbody>
                </table>
            </div>
        {% else %}
            <div class="text-center py-5">
                <i class="fas fa-hand-holding-usd text-muted mb-3" style="font-size: 3rem;"></i>
                <h5>No Contributions Found</h5>
                <p class="text-muted mb-4">No contributions match your current filters or no contributions have been created yet.</p>
                {% if is_granted('ROLE_TEAM_ADMIN') or team_settings.membersCanCreateContributions %}
                    <a href="{{ path('contribution_create', {'team': team.id}) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create First Contribution
                    </a>
                {% endif %}
            </div>
        {% endif %}
    </div>
    {% if contributions|length > 0 and pagination.pageCount > 1 %}
        <div class="card-footer bg-white">
            {% include 'components/pagination.html.twig' with {'paginator': pagination} %}
        </div>
    {% endif %}
</div>
{% endblock %}
```

## Penalty Management

### Overview
Penalties are financial consequences for rule violations, missed obligations, or disciplinary actions. They provide accountability and help maintain team standards and compliance.

### User Stories
1. As a team admin, I want to create penalties for rule violations so members understand consequences
2. As a member, I want to view my penalties and understand why they were issued
3. As a team admin, I want to track penalty payments and compliance rates
4. As a member, I want to pay my penalties to resolve violations

### Features

#### Penalty Categories
- **Violation Types**: Late arrival, missed training, equipment damage, behavioral issues
- **Severity Levels**: Minor, major, serious violations with different amounts
- **Auto-calculation**: Progressive penalties for repeat violations
- **Custom Categories**: Team-specific violation types

#### Penalty Workflow
1. **Issue Penalty**: Admin creates penalty with reason and amount
2. **Notification**: Member receives email/app notification
3. **Payment Period**: Member has time to pay (configurable)
4. **Escalation**: Automatic reminders and escalation procedures
5. **Resolution**: Payment or administrative resolution

### Implementation

#### Penalty Creation Form
```twig
{# templates/pages/penalties/create.html.twig #}
{% extends 'layouts/form.html.twig' %}

{% block title %}Create Penalty | {{ team.name }} | Cashbox{% endblock %}

{% block form_content %}
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Penalty Details</h5>
            </div>
            <div class="card-body">
                {{ form_start(form, {'attr': {'id': 'penalty-form', 'novalidate': 'novalidate'}}) }}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            {{ form_label(form.member, 'Team Member', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(form.member, {'attr': {'class': 'form-select'}}) }}
                            {{ form_errors(form.member) }}
                            <div class="form-text">Select the member who received the penalty</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            {{ form_label(form.category, 'Penalty Category', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(form.category, {'attr': {'class': 'form-select', 'data-auto-amount': 'true'}}) }}
                            {{ form_errors(form.category) }}
                            <div class="form-text">Choose the type of violation</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            {{ form_label(form.amount, 'Penalty Amount', {'label_attr': {'class': 'form-label'}}) }}
                            <div class="input-group">
                                <span class="input-group-text">{{ team.currency }}</span>
                                {{ form_widget(form.amount, {'attr': {'class': 'form-control', 'placeholder': '0.00'}}) }}
                            </div>
                            {{ form_errors(form.amount) }}
                            <div class="form-text" id="amount-suggestion">Standard amount will be suggested based on category</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            {{ form_label(form.dueDate, 'Due Date', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(form.dueDate, {'attr': {'class': 'form-control'}}) }}
                            {{ form_errors(form.dueDate) }}
                            <div class="form-text">When the penalty should be paid</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        {{ form_label(form.reason, 'Reason for Penalty', {'label_attr': {'class': 'form-label'}}) }}
                        {{ form_widget(form.reason, {'attr': {'class': 'form-control', 'placeholder': 'Provide a clear reason for the penalty'}}) }}
                        {{ form_errors(form.reason) }}
                        <div class="form-text">Be specific about the violation or reason</div>
                    </div>

                    <div class="mb-3">
                        {{ form_label(form.description, 'Additional Details (Optional)', {'label_attr': {'class': 'form-label'}}) }}
                        {{ form_widget(form.description, {'attr': {'class': 'form-control', 'rows': 3, 'placeholder': 'Any additional context or details'}}) }}
                        {{ form_errors(form.description) }}
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            {{ form_widget(form.sendNotification, {'attr': {'class': 'form-check-input'}}) }}
                            {{ form_label(form.sendNotification, 'Send notification to member', {'label_attr': {'class': 'form-check-label'}}) }}
                            <div class="form-text">Member will receive an email notification about the penalty</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ path('penalty_list', {'team': team.id}) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-exclamation-circle me-1"></i> Create Penalty
                        </button>
                    </div>
                {{ form_end(form) }}
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0">Penalty Guidelines</h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li class="mb-2">Be fair and consistent with penalty amounts</li>
                    <li class="mb-2">Provide clear, specific reasons for penalties</li>
                    <li class="mb-2">Allow reasonable time for payment</li>
                    <li class="mb-2">Consider progressive penalties for repeat violations</li>
                    <li>Document incidents thoroughly</li>
                </ul>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0">Recent Penalties</h6>
            </div>
            <div class="card-body p-0">
                {% if recent_penalties|length > 0 %}
                    <div class="list-group list-group-flush">
                        {% for penalty in recent_penalties|slice(0, 5) %}
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ penalty.member.fullName }}</h6>
                                        <p class="mb-1 small">{{ penalty.reason }}</p>
                                        <small class="text-muted">{{ penalty.createdAt|time_diff }}</small>
                                    </div>
                                    <span class="badge {{ penalty.isPaid ? 'bg-success' : 'bg-warning' }}">
                                        {{ penalty.amount|format_currency(team.currency) }}
                                    </span>
                                </div>
                            </div>
                        {% endfor %}
                    </div>
                {% else %}
                    <div class="text-center py-3">
                        <small class="text-muted">No recent penalties</small>
                    </div>
                {% endif %}
            </div>
        </div>
    </div>
</div>
{% endblock %}

{% block javascripts %}
{{ parent() }}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill amount based on category
    const categorySelect = document.querySelector('select[name="penalty[category]"]');
    const amountInput = document.querySelector('input[name="penalty[amount]"]');
    const amountSuggestion = document.getElementById('amount-suggestion');

    if (categorySelect && amountInput) {
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            if (categoryId) {
                fetch(`/api/penalty-categories/${categoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        amountInput.value = data.defaultAmount;
                        amountSuggestion.textContent = `Standard amount for ${data.name}: ${new Intl.NumberFormat('de-DE', { style: 'currency', currency: '{{ team.currency }}' }).format(data.defaultAmount)}`;
                    });
            }
        });
    }
});
</script>
{% endblock %}
```

## Payment Management

### Overview
Payment management handles the recording, verification, and tracking of all financial transactions within the team. It provides a complete audit trail and ensures financial transparency.

### User Stories
1. As a member, I want to record payments for contributions and penalties
2. As a team admin, I want to verify and approve payments
3. As a user, I want to view complete payment history
4. As a team admin, I want to generate payment reports

### Features

#### Payment Recording
- **Multiple Types**: Contribution payments, penalty payments, general payments
- **Payment Methods**: Cash, bank transfer, credit card, digital payments
- **Proof Upload**: Receipt images, transaction screenshots
- **Automatic Matching**: Link payments to specific contributions/penalties

#### Verification Workflow
- **Admin Review**: Payments require admin verification for larger amounts
- **Automatic Approval**: Small payments can be auto-approved
- **Dispute Resolution**: Handle payment disputes and corrections
- **Audit Trail**: Complete history of all payment actions

### Implementation

#### Payment Dashboard
```twig
{# templates/pages/payments/dashboard.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Payment Dashboard | {{ team.name }} | Cashbox{% endblock %}

{% block content %}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Payment Dashboard</h1>
        <p class="text-muted mb-0">Track and manage all team payments</p>
    </div>
    <div class="btn-group">
        <a href="{{ path('payment_create', {'team': team.id}) }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Record Payment
        </a>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ path('payment_export', {'team': team.id}) }}">
                    <i class="fas fa-file-export me-2"></i> Export Payments
                </a></li>
                <li><a class="dropdown-item" href="{{ path('payment_reports', {'team': team.id}) }}">
                    <i class="fas fa-chart-bar me-2"></i> Payment Reports
                </a></li>
            </ul>
        </div>
    </div>
</div>

{# Payment Statistics Cards #}
<div class="row mb-4">
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-0">Verified Payments</h6>
                        <h4 class="mb-0">{{ payment_stats.verified_count }}</h4>
                        <small class="text-success">
                            {{ payment_stats.verified_amount|format_currency(team.currency) }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-clock text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-0">Pending Verification</h6>
                        <h4 class="mb-0">{{ payment_stats.pending_count }}</h4>
                        <small class="text-warning">
                            {{ payment_stats.pending_amount|format_currency(team.currency) }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-calendar-day text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-0">This Month</h6>
                        <h4 class="mb-0">{{ payment_stats.monthly_count }}</h4>
                        <small class="text-primary">
                            {{ payment_stats.monthly_amount|format_currency(team.currency) }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-chart-line text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-0">Total Collected</h6>
                        <h4 class="mb-0">{{ payment_stats.total_count }}</h4>
                        <small class="text-info">
                            {{ payment_stats.total_amount|format_currency(team.currency) }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{# Recent Payments and Pending Approvals #}
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Payments</h5>
                <a href="{{ path('payment_list', {'team': team.id}) }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                {% if recent_payments|length > 0 %}
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Member</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {% for payment in recent_payments|slice(0, 8) %}
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary text-white me-2">
                                                    {{ payment.member.initials }}
                                                </div>
                                                <span>{{ payment.member.fullName }}</span>
                                            </div>
                                        </td>
                                        <td class="fw-medium">{{ payment.amount|format_currency(team.currency) }}</td>
                                        <td>
                                            {% if payment.type == 'contribution' %}
                                                <span class="badge bg-primary">Contribution</span>
                                            {% elseif payment.type == 'penalty' %}
                                                <span class="badge bg-warning">Penalty</span>
                                            {% else %}
                                                <span class="badge bg-secondary">Other</span>
                                            {% endif %}
                                        </td>
                                        <td>{{ payment.method|title|replace({'_': ' '}) }}</td>
                                        <td>{{ payment.date|date('M d') }}</td>
                                        <td>
                                            {% if payment.status == 'verified' %}
                                                <span class="badge bg-success">Verified</span>
                                            {% elseif payment.status == 'rejected' %}
                                                <span class="badge bg-danger">Rejected</span>
                                            {% else %}
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            {% endif %}
                                        </td>
                                    </tr>
                                {% endfor %}
                            </tbody>
                        </table>
                    </div>
                {% else %}
                    <div class="text-center py-4">
                        <i class="fas fa-credit-card text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0">No recent payments</p>
                    </div>
                {% endif %}
            </div>
        </div>
    </div>

    {% if is_granted('ROLE_TEAM_ADMIN') %}
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Pending Approvals</h5>
                    {% if pending_payments|length > 0 %}
                        <span class="badge bg-warning rounded-pill">{{ pending_payments|length }}</span>
                    {% endif %}
                </div>
                <div class="card-body p-0">
                    {% if pending_payments|length > 0 %}
                        <div class="list-group list-group-flush">
                            {% for payment in pending_payments|slice(0, 5) %}
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ payment.member.fullName }}</h6>
                                            <p class="mb-1 small text-muted">
                                                {{ payment.type|title }} • {{ payment.method|title|replace({'_': ' '}) }}
                                            </p>
                                            <small class="text-muted">{{ payment.date|time_diff }}</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-medium mb-1">{{ payment.amount|format_currency(team.currency) }}</div>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" 
                                                        class="btn btn-outline-success btn-sm"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#approveModal{{ payment.id }}">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <a href="{{ path('payment_detail', {'team': team.id, 'id': payment.id}) }}" 
                                                   class="btn btn-outline-secondary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {% endfor %}
                        </div>
                        {% if pending_payments|length > 5 %}
                            <div class="card-footer bg-white border-0 text-center">
                                <a href="{{ path('payment_list', {'team': team.id, 'status': 'pending'}) }}" class="btn btn-sm btn-outline-primary">
                                    View All {{ pending_payments|length }} Pending
                                </a>
                            </div>
                        {% endif %}
                    {% else %}
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-muted mb-2" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0">No pending approvals</p>
                        </div>
                    {% endif %}
                </div>
            </div>
        </div>
    {% endif %}
</div>
{% endblock %}
```

## Integration and API Endpoints

### Backend API Integration
The financial management system integrates with the following API endpoints:

#### Contribution APIs
- `GET /api/teams/{id}/contributions` - List team contributions
- `POST /api/teams/{id}/contributions` - Create new contribution
- `GET /api/contributions/{id}` - Get contribution details
- `PUT /api/contributions/{id}` - Update contribution
- `DELETE /api/contributions/{id}` - Delete contribution
- `POST /api/contributions/{id}/members/{userId}/pay` - Record member payment

#### Penalty APIs
- `GET /api/teams/{id}/penalties` - List team penalties
- `POST /api/teams/{id}/penalties` - Create new penalty
- `GET /api/penalties/{id}` - Get penalty details
- `PUT /api/penalties/{id}` - Update penalty
- `POST /api/penalties/{id}/pay` - Record penalty payment

#### Payment APIs
- `GET /api/teams/{id}/payments` - List team payments
- `POST /api/teams/{id}/payments` - Record new payment
- `GET /api/payments/{id}` - Get payment details
- `POST /api/payments/{id}/verify` - Verify payment (admin)
- `POST /api/payments/{id}/reject` - Reject payment (admin)

## Security and Permissions

### Role-Based Access Control
- **Team Admin**: Full CRUD access to all financial items
- **Team Member**: View own items, create payments, limited creation based on settings
- **Team Contributor**: Read-only access to team financial information

### Data Validation
- **Amount Validation**: Positive numbers, reasonable limits, currency formatting
- **Date Validation**: Due dates must be future, payment dates must be valid
- **File Upload**: Secure handling of proof-of-payment uploads
- **Input Sanitization**: Prevent XSS and injection attacks

### Audit Trail
- **Action Logging**: All financial actions are logged with user, timestamp, details
- **Change History**: Track modifications to contributions, penalties, payments
- **Export Capabilities**: Generate audit reports for compliance

## Testing and Quality Assurance

### Test Coverage Requirements
- **Unit Tests**: 90% coverage for all financial logic
- **Integration Tests**: API integration and data flow testing
- **UI Tests**: User interface and workflow testing
- **Performance Tests**: Handle large datasets and concurrent users

### Accessibility Compliance
- **WCAG 2.1 AA**: Full compliance with accessibility standards
- **Keyboard Navigation**: Complete keyboard accessibility
- **Screen Reader**: Semantic HTML and ARIA attributes
- **Color Contrast**: Appropriate contrast ratios for all text

## Conclusion

This comprehensive financial management system provides teams with powerful tools to manage contributions, penalties, and payments while maintaining transparency, accountability, and ease of use. The implementation combines modern web technologies with intuitive user interfaces to deliver a complete solution for team financial management.

The system is designed to scale with team growth, adapt to different use cases, and maintain high standards of security and accessibility. Regular updates and user feedback will ensure the system continues to meet evolving needs and requirements.