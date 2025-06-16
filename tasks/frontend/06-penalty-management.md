# Penalty Management

## Overview

The penalty management section of the Cashbox application allows users to create, view, and manage penalties within their teams. Penalties represent financial consequences for rule violations or missed obligations. This document outlines the design and implementation of the penalty management pages.

## User Stories

1. As a team admin, I want to create penalty types so that I can define different categories of rule violations
2. As a team admin, I want to assign penalties to team members so that they are aware of their financial obligations
3. As a team member, I want to view my pending penalties so that I can plan my payments
4. As a team member, I want to mark penalties as paid so that I can fulfill my obligations
5. As a team admin, I want to view penalty statistics so that I can understand rule compliance within the team

## Penalty Management Pages

The penalty management section consists of the following pages:

1. **Penalty List**: Overview of all penalties for a team or user
2. **Penalty Creation**: Form for creating a new penalty
3. **Penalty Details**: Detailed view of a specific penalty
4. **Penalty Settings**: Administrative settings for penalty types

## Penalty List Page

The penalty list page provides an overview of all penalties, with filtering options for pending, paid, and categories.

### Design

```twig
{# templates/pages/penalties/list.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Penalties | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Penalties</h1>
        {% if is_granted('ROLE_TEAM_ADMIN') %}
            <a href="{{ path('penalty_new') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create Penalty
            </a>
        {% endif %}
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link {{ active_tab == 'all' ? 'active' : '' }}" href="{{ path('penalties') }}">
                        All
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ active_tab == 'pending' ? 'active' : '' }}" href="{{ path('penalties', {'filter': 'pending'}) }}">
                        Pending
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ active_tab == 'paid' ? 'active' : '' }}" href="{{ path('penalties', {'filter': 'paid'}) }}">
                        Paid
                    </a>
                </li>
                {% if penalty_categories|length > 0 %}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ active_tab starts with 'category_' ? 'active' : '' }}"
                           data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                            Categories
                        </a>
                        <ul class="dropdown-menu">
                            {% for category in penalty_categories %}
                                <li>
                                    <a class="dropdown-item {{ active_tab == 'category_' ~ category.id ? 'active' : '' }}"
                                       href="{{ path('penalties', {'category': category.id}) }}">
                                        {{ category.name }}
                                    </a>
                                </li>
                            {% endfor %}
                        </ul>
                    </li>
                {% endif %}
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Team</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {% for penalty in penalties %}
                            <tr>
                                <td>
                                    <a href="{{ path('penalty_details', {'id': penalty.id}) }}" class="text-decoration-none">
                                        {{ penalty.description }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge rounded-pill" style="background-color: {{ penalty.category.color }}">
                                        {{ penalty.category.name }}
                                    </span>
                                </td>
                                <td>{{ penalty.amount|format_currency(penalty.currency) }}</td>
                                <td>{{ penalty.createdAt|date('M d, Y') }}</td>
                                <td>
                                    {% if penalty.isPaid %}
                                        <span class="badge bg-success">Paid</span>
                                    {% else %}
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    {% endif %}
                                </td>
                                <td>{{ penalty.team.name }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ path('penalty_details', {'id': penalty.id}) }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        {% if not penalty.isPaid and (is_granted('ROLE_TEAM_ADMIN') or app.user == penalty.user) %}
                                            <a href="{{ path('penalty_pay', {'id': penalty.id}) }}" class="btn btn-outline-success">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        {% endif %}
                                        {% if is_granted('ROLE_TEAM_ADMIN') %}
                                            <a href="{{ path('penalty_edit', {'id': penalty.id}) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        {% endif %}
                                    </div>
                                </td>
                            </tr>
                        {% else %}
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-0">No penalties found.</p>
                                </td>
                            </tr>
                        {% endfor %}
                    </tbody>
                </table>
            </div>
        </div>
        {% if penalties|length > 0 %}
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">Showing {{ penalties|length }} penalties</small>
                    </div>
                    {% include 'components/pagination.html.twig' with {
                        'current_page': current_page,
                        'total_pages': total_pages,
                        'route': 'penalties',
                        'route_params': {'filter': active_tab}
                    } %}
                </div>
            </div>
        {% endif %}
    </div>

    {% if is_granted('ROLE_TEAM_ADMIN') %}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Penalty Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Total Penalties</h6>
                                <p class="h3 mb-0">{{ stats.total }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Pending Amount</h6>
                                <p class="h3 mb-0">{{ stats.pendingAmount|format_currency('EUR') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3 mb-md-0">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Collected Amount</h6>
                                <p class="h3 mb-0">{{ stats.paidAmount|format_currency('EUR') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Most Common Category</h6>
                                <p class="h5 mb-0">{{ stats.mostCommonCategory }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {% endif %}
{% endblock %}
```

## Penalty Creation Page

The penalty creation page allows team administrators to create new penalties for team members.

### Design

```twig
{# templates/pages/penalties/new.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Create Penalty | Cashbox{% endblock %}

{% block content %}
    <div class="mb-4">
        <h1 class="h3 mb-0">Create Penalty</h1>
        <p class="text-muted">Assign a penalty to team members</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    {{ form_start(form, {'attr': {'class': 'needs-validation', 'novalidate': 'novalidate'}}) }}
                        <div class="mb-3">
                            {{ form_label(form.description) }}
                            {{ form_widget(form.description, {'attr': {'class': 'form-control' ~ (form.description.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(form.description) }}
                            <div class="form-text">Provide a clear description of the rule violation</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                {{ form_label(form.category) }}
                                {{ form_widget(form.category, {'attr': {'class': 'form-select' ~ (form.category.vars.valid ? '' : ' is-invalid')}}) }}
                                {{ form_errors(form.category) }}
                                <div class="form-text">Select the category of the penalty</div>
                            </div>
                            <div class="col-md-6">
                                {{ form_label(form.amount) }}
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    {{ form_widget(form.amount, {'attr': {'class': 'form-control' ~ (form.amount.vars.valid ? '' : ' is-invalid')}}) }}
                                    {{ form_errors(form.amount) }}
                                </div>
                                <div class="form-text penalty-amount-hint">Standard amount for this category</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.team) }}
                            {{ form_widget(form.team, {'attr': {'class': 'form-select' ~ (form.team.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(form.team) }}
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.users) }}
                            {{ form_widget(form.users, {'attr': {'class': 'form-select' ~ (form.users.vars.valid ? '' : ' is-invalid'), 'data-choices': 'true'}}) }}
                            {{ form_errors(form.users) }}
                            <div class="form-text">Select the team members who need to pay this penalty</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.notes) }}
                            {{ form_widget(form.notes, {'attr': {'class': 'form-control' ~ (form.notes.vars.valid ? '' : ' is-invalid'), 'rows': '3'}}) }}
                            {{ form_errors(form.notes) }}
                            <div class="form-text">Optional additional information about this penalty</div>
                        </div>

                        <div class="form-check mb-3">
                            {{ form_widget(form.notifyUsers, {'attr': {'class': 'form-check-input'}}) }}
                            {{ form_label(form.notifyUsers, null, {'label_attr': {'class': 'form-check-label'}}) }}
                            <div class="form-text">Send email notifications to the affected users</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ path('penalties') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Penalty</button>
                        </div>
                    {{ form_end(form) }}
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Penalty Guidelines</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">Provide a clear, descriptive reason for the penalty</li>
                        <li class="mb-2">Select the appropriate category for better organization</li>
                        <li class="mb-2">Use the standard amount for the category when applicable</li>
                        <li>Notify users to ensure they're aware of the penalty</li>
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
            // Dynamic user loading based on selected team
            const teamSelect = document.querySelector('#penalty_team');
            const userSelect = document.querySelector('#penalty_users');

            teamSelect.addEventListener('change', function() {
                const teamId = this.value;
                if (!teamId) return;

                fetch(`/api/teams/${teamId}/members`)
                    .then(response => response.json())
                    .then(data => {
                        // Clear current options
                        userSelect.innerHTML = '';

                        // Add new options
                        data.forEach(user => {
                            const option = document.createElement('option');
                            option.value = user.id;
                            option.textContent = user.fullName;
                            userSelect.appendChild(option);
                        });

                        // Trigger change event to refresh Choices.js
                        const event = new Event('change');
                        userSelect.dispatchEvent(event);
                    });
            });

            // Auto-fill amount based on selected category
            const categorySelect = document.querySelector('#penalty_category');
            const amountInput = document.querySelector('#penalty_amount');
            const amountHint = document.querySelector('.penalty-amount-hint');

            categorySelect.addEventListener('change', function() {
                const categoryId = this.value;
                if (!categoryId) return;

                fetch(`/api/penalty-categories/${categoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        amountInput.value = data.defaultAmount;
                        amountHint.textContent = `Standard amount for ${data.name}: ${new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(data.defaultAmount)}`;
                    });
            });
        });
    </script>
{% endblock %}
```

## Penalty Details Page

The penalty details page shows comprehensive information about a specific penalty, including payment status and history.

### Design

```twig
{# templates/pages/penalties/details.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}{{ penalty.description }} | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ penalty.description }}</h1>
            <p class="text-muted mb-0">Penalty Details</p>
        </div>
        <div class="btn-group">
            <a href="{{ path('penalties') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
            {% if not penalty.isPaid and (is_granted('ROLE_TEAM_ADMIN') or app.user == penalty.user) %}
                <a href="{{ path('penalty_pay', {'id': penalty.id}) }}" class="btn btn-success">
                    <i class="fas fa-check me-1"></i> Mark as Paid
                </a>
            {% endif %}
            {% if is_granted('ROLE_TEAM_ADMIN') %}
                <a href="{{ path('penalty_edit', {'id': penalty.id}) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            {% endif %}
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Penalty Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Amount</h6>
                            <p class="h4 mb-0">{{ penalty.amount|format_currency(penalty.currency) }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Status</h6>
                            <p class="mb-0">
                                {% if penalty.isPaid %}
                                    <span class="badge bg-success">Paid</span>
                                {% else %}
                                    <span class="badge bg-warning text-dark">Pending</span>
                                {% endif %}
                            </p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Category</h6>
                            <p class="mb-0">
                                <span class="badge rounded-pill" style="background-color: {{ penalty.category.color }}">
                                    {{ penalty.category.name }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Team</h6>
                            <p class="mb-0">
                                <a href="{{ path('team_details', {'id': penalty.team.id}) }}" class="text-decoration-none">
                                    {{ penalty.team.name }}
                                </a>
                            </p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Created By</h6>
                            <p class="mb-0">{{ penalty.createdBy.fullName }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Created On</h6>
                            <p class="mb-0">{{ penalty.createdAt|date('F d, Y') }}</p>
                        </div>
                    </div>
                    {% if penalty.notes %}
                        <div class="mb-0">
                            <h6 class="text-muted mb-1">Notes</h6>
                            <p class="mb-0">{{ penalty.notes|nl2br }}</p>
                        </div>
                    {% endif %}
                </div>
            </div>

            {% if penalty.isPaid %}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Paid On</h6>
                                <p class="mb-0">{{ penalty.paidAt|date('F d, Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Confirmed By</h6>
                                <p class="mb-0">{{ penalty.confirmedBy ? penalty.confirmedBy.fullName : 'Self-confirmed' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            {% endif %}
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Assigned To</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        {% for user in penalty.users %}
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary me-3">
                                        {{ user.initials }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ user.fullName }}</h6>
                                        <small class="text-muted">{{ user.email }}</small>
                                    </div>
                                </div>
                                {% if penalty.isPaidByUser(user) %}
                                    <span class="badge bg-success rounded-pill">Paid</span>
                                {% else %}
                                    <span class="badge bg-warning text-dark rounded-pill">Pending</span>
                                {% endif %}
                            </li>
                        {% endfor %}
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Activity Timeline</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        {% for activity in penalty.activities %}
                            <li class="list-group-item">
                                <div class="d-flex">
                                    <div class="timeline-icon me-3">
                                        {% if activity.type == 'created' %}
                                            <i class="fas fa-plus-circle text-primary"></i>
                                        {% elseif activity.type == 'updated' %}
                                            <i class="fas fa-edit text-info"></i>
                                        {% elseif activity.type == 'paid' %}
                                            <i class="fas fa-check-circle text-success"></i>
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

## Penalty Settings Page

The penalty settings page allows team administrators to manage penalty categories and default settings.

### Design

```twig
{# templates/pages/penalties/settings.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Penalty Settings | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Penalty Settings</h1>
            <p class="text-muted mb-0">Manage penalty categories and defaults</p>
        </div>
        <a href="{{ path('penalty_category_new') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Penalty Category
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Penalty Categories</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Default Amount</th>
                                    <th>Color</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {% for category in penalty_categories %}
                                    <tr>
                                        <td>{{ category.name }}</td>
                                        <td>{{ category.defaultAmount|format_currency(category.currency) }}</td>
                                        <td>
                                            <span class="color-swatch" style="background-color: {{ category.color }}"></span>
                                        </td>
                                        <td>{{ category.description }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ path('penalty_category_edit', {'id': category.id}) }}" class="btn btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal{{ category.id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="deleteCategoryModal{{ category.id }}" tabindex="-1" aria-labelledby="deleteCategoryModalLabel{{ category.id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteCategoryModalLabel{{ category.id }}">Confirm Deletion</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete the penalty category <strong>{{ category.name }}</strong>?</p>
                                                            <p class="text-danger mb-0">This action cannot be undone and may affect existing penalties.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <a href="{{ path('penalty_category_delete', {'id': category.id}) }}" class="btn btn-danger">Delete</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                {% else %}
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <p class="text-muted mb-0">No penalty categories defined yet.</p>
                                            <a href="{{ path('penalty_category_new') }}" class="btn btn-sm btn-primary mt-2">
                                                Create your first penalty category
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
                    <h5 class="card-title mb-0">Default Settings</h5>
                </div>
                <div class="card-body">
                    {{ form_start(settings_form) }}
                        <div class="mb-3">
                            {{ form_label(settings_form.defaultCurrency) }}
                            {{ form_widget(settings_form.defaultCurrency, {'attr': {'class': 'form-select'}}) }}
                            <div class="form-text">Default currency for new penalties</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(settings_form.notifyByDefault) }}
                            {{ form_widget(settings_form.notifyByDefault, {'attr': {'class': 'form-check-input'}}) }}
                            <div class="form-text">Send notifications by default when creating penalties</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(settings_form.showInDashboard) }}
                            {{ form_widget(settings_form.showInDashboard, {'attr': {'class': 'form-check-input'}}) }}
                            <div class="form-text">Show penalty statistics on the dashboard</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save Settings</button>
                    {{ form_end(settings_form) }}
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Penalty Category Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">Create different categories for different types of rule violations</li>
                        <li class="mb-2">Use distinct colors to make categories easily identifiable</li>
                        <li class="mb-2">Set appropriate default amounts based on the severity of the violation</li>
                        <li>Use clear, descriptive names for penalty categories</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
{% endblock %}

{% block stylesheets %}
    {{ parent() }}
    <style>
        .color-swatch {
            display: inline-block;
            width: 24px;
            height: 24px;
            border-radius: 4px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
    </style>
{% endblock %}
```

## Integration with Backend

The penalty management frontend will integrate with the backend API endpoints:

- `GET /penalties`: Retrieve all penalties
- `GET /penalties/pending`: Retrieve pending penalties
- `GET /penalties/team/{teamId}`: Retrieve penalties for a specific team
- `GET /penalties/user/{userId}`: Retrieve penalties for a specific user
- `GET /penalties/category/{categoryId}`: Retrieve penalties for a specific category
- `GET /penalties/{id}`: Retrieve a specific penalty
- `POST /penalties`: Create a new penalty
- `PUT /penalties/{id}`: Update a penalty
- `POST /penalties/{id}/pay`: Mark a penalty as paid
- `DELETE /penalties/{id}`: Delete a penalty

Additionally, for penalty categories:

- `GET /penalty-categories`: Retrieve all penalty categories
- `GET /penalty-categories/{id}`: Retrieve a specific penalty category
- `POST /penalty-categories`: Create a new penalty category
- `PUT /penalty-categories/{id}`: Update a penalty category
- `DELETE /penalty-categories/{id}`: Delete a penalty category

## JavaScript Enhancements

The penalty management pages will include JavaScript enhancements for a better user experience:

1. **Dynamic User Loading**: When creating a penalty, users will be loaded dynamically based on the selected team
2. **Auto-fill Amount**: When selecting a penalty category, the default amount will be automatically filled in
3. **Color Picker**: A color picker for selecting category colors
4. **Form Validation**: Client-side validation for penalty forms
5. **Payment Confirmation**: Confirmation dialogs for payment actions
6. **Filtering and Sorting**: Dynamic filtering and sorting of penalty lists

## Responsive Design Considerations

The penalty management pages will be fully responsive:

1. **Mobile View**: On small screens, the table view will adapt to a card-based layout for better readability
2. **Touch-Friendly Controls**: Larger touch targets for mobile users
3. **Simplified Forms**: Multi-step forms on mobile for better usability
4. **Optimized Statistics**: Stacked statistics cards on smaller screens

## Accessibility Features

The penalty management pages will include the following accessibility features:

1. **ARIA Labels**: Proper ARIA labels for all interactive elements
2. **Keyboard Navigation**: Full keyboard navigation support
3. **Screen Reader Support**: Semantic HTML and appropriate text alternatives
4. **Color Contrast**: High contrast for all text elements
5. **Focus Indicators**: Clear focus indicators for keyboard navigation

## Next Steps

After implementing the penalty management pages, the next step will be to implement the payment management pages as outlined in [07-payment-management.md](07-payment-management.md).
