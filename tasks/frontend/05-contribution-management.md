# Contribution Management

## Overview

The contribution management section of the Cashbox application allows users to create, view, and manage financial contributions within their teams. Contributions represent regular financial commitments that team members need to fulfill. This document outlines the design and implementation of the contribution management pages.

## User Stories

1. As a team admin, I want to create new contribution types so that I can define different financial obligations for my team
2. As a team admin, I want to assign contributions to team members so that they know their financial responsibilities
3. As a team member, I want to view my pending contributions so that I can plan my payments
4. As a team member, I want to mark contributions as paid so that I can fulfill my obligations
5. As a team admin, I want to view contribution statistics so that I can understand the financial health of the team

## Contribution Management Pages

The contribution management section consists of the following pages:

1. **Contribution List**: Overview of all contributions for a team or user
2. **Contribution Creation**: Form for creating a new contribution
3. **Contribution Details**: Detailed view of a specific contribution
4. **Contribution Settings**: Administrative settings for contribution types

## Contribution List Page

The contribution list page provides an overview of all contributions, with filtering options for pending, upcoming, and past contributions.

### Design

```twig
{# templates/pages/contributions/list.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Contributions | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Contributions</h1>
        {% if is_granted('ROLE_TEAM_ADMIN') %}
            <a href="{{ path('contribution_new') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create Contribution
            </a>
        {% endif %}
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link {{ active_tab == 'all' ? 'active' : '' }}" href="{{ path('contributions') }}">
                        All
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ active_tab == 'pending' ? 'active' : '' }}" href="{{ path('contributions', {'filter': 'pending'}) }}">
                        Pending
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ active_tab == 'upcoming' ? 'active' : '' }}" href="{{ path('contributions', {'filter': 'upcoming'}) }}">
                        Upcoming
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ active_tab == 'paid' ? 'active' : '' }}" href="{{ path('contributions', {'filter': 'paid'}) }}">
                        Paid
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Team</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {% for contribution in contributions %}
                            <tr>
                                <td>
                                    <a href="{{ path('contribution_details', {'id': contribution.id}) }}" class="text-decoration-none">
                                        {{ contribution.description }}
                                    </a>
                                </td>
                                <td>{{ contribution.amount|format_currency(contribution.currency) }}</td>
                                <td>
                                    <span class="{{ contribution.isOverdue ? 'text-danger' : '' }}">
                                        {{ contribution.dueDate|date('M d, Y') }}
                                    </span>
                                </td>
                                <td>
                                    {% if contribution.isPaid %}
                                        <span class="badge bg-success">Paid</span>
                                    {% elseif contribution.isOverdue %}
                                        <span class="badge bg-danger">Overdue</span>
                                    {% else %}
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    {% endif %}
                                </td>
                                <td>{{ contribution.team.name }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ path('contribution_details', {'id': contribution.id}) }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        {% if not contribution.isPaid and (is_granted('ROLE_TEAM_ADMIN') or app.user == contribution.user) %}
                                            <a href="{{ path('contribution_pay', {'id': contribution.id}) }}" class="btn btn-outline-success">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        {% endif %}
                                        {% if is_granted('ROLE_TEAM_ADMIN') %}
                                            <a href="{{ path('contribution_edit', {'id': contribution.id}) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        {% endif %}
                                    </div>
                                </td>
                            </tr>
                        {% else %}
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <p class="text-muted mb-0">No contributions found.</p>
                                </td>
                            </tr>
                        {% endfor %}
                    </tbody>
                </table>
            </div>
        </div>
        {% if contributions|length > 0 %}
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">Showing {{ contributions|length }} contributions</small>
                    </div>
                    {% include 'components/pagination.html.twig' with {
                        'current_page': current_page,
                        'total_pages': total_pages,
                        'route': 'contributions',
                        'route_params': {'filter': active_tab}
                    } %}
                </div>
            </div>
        {% endif %}
    </div>
</div>
{% endblock %}
```

## Contribution Creation Page

The contribution creation page allows team administrators to create new contributions for team members.

### Design

```twig
{# templates/pages/contributions/new.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Create Contribution | Cashbox{% endblock %}

{% block content %}
    <div class="mb-4">
        <h1 class="h3 mb-0">Create Contribution</h1>
        <p class="text-muted">Create a new financial contribution for team members</p>
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
                            <div class="form-text">Provide a clear description of what this contribution is for</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                {{ form_label(form.amount) }}
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    {{ form_widget(form.amount, {'attr': {'class': 'form-control' ~ (form.amount.vars.valid ? '' : ' is-invalid')}}) }}
                                    {{ form_errors(form.amount) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                {{ form_label(form.dueDate) }}
                                {{ form_widget(form.dueDate, {'attr': {'class': 'form-control' ~ (form.dueDate.vars.valid ? '' : ' is-invalid')}}) }}
                                {{ form_errors(form.dueDate) }}
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
                            <div class="form-text">Select the team members who need to pay this contribution</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.notes) }}
                            {{ form_widget(form.notes, {'attr': {'class': 'form-control' ~ (form.notes.vars.valid ? '' : ' is-invalid'), 'rows': '3'}}) }}
                            {{ form_errors(form.notes) }}
                            <div class="form-text">Optional additional information about this contribution</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ path('contributions') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Contribution</button>
                        </div>
                    {{ form_end(form) }}
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Contribution Guidelines</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">Provide a clear, descriptive name for the contribution</li>
                        <li class="mb-2">Set a reasonable due date to give members time to pay</li>
                        <li class="mb-2">Consider creating recurring contributions for regular payments</li>
                        <li>Add detailed notes to explain the purpose of the contribution</li>
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
            const teamSelect = document.querySelector('#contribution_team');
            const userSelect = document.querySelector('#contribution_users');

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
        });
    </script>
{% endblock %}
```

## Contribution Details Page

The contribution details page shows comprehensive information about a specific contribution, including payment status and history.

### Design

```twig
{# templates/pages/contributions/details.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}{{ contribution.description }} | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ contribution.description }}</h1>
            <p class="text-muted mb-0">Contribution Details</p>
        </div>
        <div class="btn-group">
            <a href="{{ path('contributions') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
            {% if not contribution.isPaid and (is_granted('ROLE_TEAM_ADMIN') or app.user == contribution.user) %}
                <a href="{{ path('contribution_pay', {'id': contribution.id}) }}" class="btn btn-success">
                    <i class="fas fa-check me-1"></i> Mark as Paid
                </a>
            {% endif %}
            {% if is_granted('ROLE_TEAM_ADMIN') %}
                <a href="{{ path('contribution_edit', {'id': contribution.id}) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            {% endif %}
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Contribution Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Amount</h6>
                            <p class="h4 mb-0">{{ contribution.amount|format_currency(contribution.currency) }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Status</h6>
                            <p class="mb-0">
                                {% if contribution.isPaid %}
                                    <span class="badge bg-success">Paid</span>
                                {% elseif contribution.isOverdue %}
                                    <span class="badge bg-danger">Overdue</span>
                                {% else %}
                                    <span class="badge bg-warning text-dark">Pending</span>
                                {% endif %}
                            </p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Due Date</h6>
                            <p class="mb-0 {{ contribution.isOverdue ? 'text-danger' : '' }}">
                                {{ contribution.dueDate|date('F d, Y') }}
                                {% if contribution.isOverdue %}
                                    <small class="ms-1">({{ contribution.daysOverdue }} days overdue)</small>
                                {% endif %}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Team</h6>
                            <p class="mb-0">
                                <a href="{{ path('team_details', {'id': contribution.team.id}) }}" class="text-decoration-none">
                                    {{ contribution.team.name }}
                                </a>
                            </p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Created By</h6>
                            <p class="mb-0">{{ contribution.createdBy.fullName }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Created On</h6>
                            <p class="mb-0">{{ contribution.createdAt|date('F d, Y') }}</p>
                        </div>
                    </div>
                    {% if contribution.notes %}
                        <div class="mb-0">
                            <h6 class="text-muted mb-1">Notes</h6>
                            <p class="mb-0">{{ contribution.notes|nl2br }}</p>
                        </div>
                    {% endif %}
                </div>
            </div>

            {% if contribution.isPaid %}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Paid On</h6>
                                <p class="mb-0">{{ contribution.paidAt|date('F d, Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Confirmed By</h6>
                                <p class="mb-0">{{ contribution.confirmedBy ? contribution.confirmedBy.fullName : 'Self-confirmed' }}</p>
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
                        {% for user in contribution.users %}
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
                                {% if contribution.isPaidByUser(user) %}
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
                        {% for activity in contribution.activities %}
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

## Contribution Settings Page

The contribution settings page allows team administrators to manage contribution types and default settings.

### Design

```twig
{# templates/pages/contributions/settings.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Contribution Settings | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Contribution Settings</h1>
            <p class="text-muted mb-0">Manage contribution types and defaults</p>
        </div>
        <a href="{{ path('contribution_type_new') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Contribution Type
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Contribution Types</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Default Amount</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {% for type in contribution_types %}
                                    <tr>
                                        <td>{{ type.name }}</td>
                                        <td>{{ type.defaultAmount|format_currency(type.currency) }}</td>
                                        <td>{{ type.description }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ path('contribution_type_edit', {'id': type.id}) }}" class="btn btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteTypeModal{{ type.id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="deleteTypeModal{{ type.id }}" tabindex="-1" aria-labelledby="deleteTypeModalLabel{{ type.id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteTypeModalLabel{{ type.id }}">Confirm Deletion</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete the contribution type <strong>{{ type.name }}</strong>?</p>
                                                            <p class="text-danger mb-0">This action cannot be undone and may affect existing contributions.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <a href="{{ path('contribution_type_delete', {'id': type.id}) }}" class="btn btn-danger">Delete</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                {% else %}
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <p class="text-muted mb-0">No contribution types defined yet.</p>
                                            <a href="{{ path('contribution_type_new') }}" class="btn btn-sm btn-primary mt-2">
                                                Create your first contribution type
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
                            <div class="form-text">Default currency for new contributions</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(settings_form.defaultDueDateDays) }}
                            {{ form_widget(settings_form.defaultDueDateDays, {'attr': {'class': 'form-control'}}) }}
                            <div class="form-text">Default number of days until due date</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(settings_form.sendReminders) }}
                            {{ form_widget(settings_form.sendReminders, {'attr': {'class': 'form-check-input'}}) }}
                            <div class="form-text">Send email reminders for upcoming and overdue contributions</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(settings_form.reminderDays) }}
                            {{ form_widget(settings_form.reminderDays, {'attr': {'class': 'form-control'}}) }}
                            <div class="form-text">Days before due date to send reminders</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save Settings</button>
                    {{ form_end(settings_form) }}
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Contribution Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">Create different contribution types for regular payments like membership fees, event fees, etc.</li>
                        <li class="mb-2">Set reasonable default amounts to make creating new contributions faster</li>
                        <li class="mb-2">Use clear, descriptive names for contribution types</li>
                        <li>Enable reminders to help members remember to pay on time</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

## Integration with Backend

The contribution management frontend will integrate with the backend API endpoints as defined in the API Platform implementation:

- `GET /contributions`: Retrieve all contributions
- `GET /contributions/unpaid`: Retrieve unpaid contributions
- `GET /contributions/upcoming`: Retrieve upcoming contributions
- `GET /contributions/team/{teamId}`: Retrieve contributions for a specific team
- `GET /contributions/user/{userId}`: Retrieve contributions for a specific user
- `GET /contributions/{id}`: Retrieve a specific contribution
- `POST /contributions`: Create a new contribution
- `PUT /contributions/{id}`: Update a contribution
- `POST /contributions/{id}/pay`: Mark a contribution as paid
- `DELETE /contributions/{id}`: Deactivate a contribution

## JavaScript Enhancements

The contribution management pages will include JavaScript enhancements for a better user experience:

1. **Dynamic User Loading**: When creating a contribution, users will be loaded dynamically based on the selected team
2. **Form Validation**: Client-side validation for contribution forms
3. **Payment Confirmation**: Confirmation dialogs for payment actions
4. **Filtering and Sorting**: Dynamic filtering and sorting of contribution lists

## Responsive Design Considerations

The contribution management pages will be fully responsive:

1. **Mobile View**: On small screens, the table view will adapt to a card-based layout for better readability
2. **Touch-Friendly Controls**: Larger touch targets for mobile users
3. **Simplified Forms**: Multi-step forms on mobile for better usability

## Accessibility Features

The contribution management pages will include the following accessibility features:

1. **ARIA Labels**: Proper ARIA labels for all interactive elements
2. **Keyboard Navigation**: Full keyboard navigation support
3. **Screen Reader Support**: Semantic HTML and appropriate text alternatives
4. **Color Contrast**: High contrast for all text elements

## Next Steps

After implementing the contribution management pages, the next step will be to implement the penalty management pages as outlined in [06-penalty-management.md](06-penalty-management.md).
