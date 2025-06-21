# Team Management

## Overview

The team management section of the Cashbox application allows users to create, view, and manage teams. Teams are a central concept in the application, as they group users together for financial tracking purposes. This document outlines the design and implementation of the team management pages.

## User Stories

1. As a user, I want to create a new team so that I can start tracking finances for my group
2. As a team admin, I want to invite members to my team so that they can participate in financial activities
3. As a team admin, I want to manage team member roles so that I can control who has administrative privileges
4. As a team admin, I want to view team details and statistics so that I can understand the team's financial health
5. As a team member, I want to view teams I belong to so that I can switch between different financial contexts

## Team Management Pages

The team management section consists of the following pages:

1. **Team List**: Overview of all teams the user belongs to
2. **Team Creation**: Form for creating a new team
3. **Team Details**: Detailed view of a specific team, including members and statistics
4. **Team Settings**: Administrative settings for a team
5. **Member Management**: Interface for adding, removing, and managing team members

## Team List Page

The team list page provides an overview of all teams the user belongs to, with options to create a new team or access existing teams.

### Design

```twig
{# templates/pages/teams/list.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}My Teams | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">My Teams</h1>
        <a href="{{ path('team_new') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Team
        </a>
    </div>

    <div class="row">
        {% if user_teams|length > 0 %}
            {% for team in user_teams %}
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="{{ path('team_details', {'id': team.id}) }}" class="text-decoration-none">
                                    {{ team.name }}
                                </a>
                            </h5>
                            <p class="text-muted mb-3">{{ team.description }}</p>
                            <div class="d-flex justify-content-between mb-3">
                                <div>
                                    <small class="text-muted d-block">Members</small>
                                    <span class="fw-medium">{{ team.memberCount }}</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Your Role</small>
                                    <span class="badge {% if team.userRole == 'ADMIN' %}bg-primary{% else %}bg-secondary{% endif %}">
                                        {{ team.userRole }}
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Balance</small>
                                    <span class="amount {% if team.balance >= 0 %}positive{% else %}negative{% endif %} monospace">
                                        {{ team.balance|format_currency('EUR') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light d-flex justify-content-between">
                            <a href="{{ path('dashboard', {'team': team.id}) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                            </a>
                            <a href="{{ path('team_details', {'id': team.id}) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-info-circle me-1"></i> Details
                            </a>
                        </div>
                    </div>
                </div>
            {% endfor %}
        {% else %}
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5>No Teams Yet</h5>
                        <p class="text-muted">You don't belong to any teams yet. Create your first team to get started!</p>
                        <a href="{{ path('team_new') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Create Team
                        </a>
                    </div>
                </div>
            </div>
        {% endif %}
    </div>

    {% if team_invitations|length > 0 %}
        <div class="mt-5">
            <h2 class="h4 mb-3">Team Invitations</h2>
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Team</th>
                                    <th>Invited By</th>
                                    <th>Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {% for invitation in team_invitations %}
                                    <tr>
                                        <td>{{ invitation.teamName }}</td>
                                        <td>{{ invitation.inviterName }}</td>
                                        <td>{{ invitation.createdAt|date('d.m.Y') }}</td>
                                        <td class="text-end">
                                            <form method="post" action="{{ path('team_invitation_accept', {'id': invitation.id}) }}" class="d-inline">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check me-1"></i> Accept
                                                </button>
                                            </form>
                                            <form method="post" action="{{ path('team_invitation_decline', {'id': invitation.id}) }}" class="d-inline">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-times me-1"></i> Decline
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                {% endfor %}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    {% endif %}
{% endblock %}
```

### Features

- Card-based layout for teams with key information
- Visual indicators for team role and balance
- Quick access to team dashboard and details
- Empty state for users without teams
- Team invitation section with accept/decline actions

## Team Creation Page

The team creation page allows users to create a new team with basic information.

### Design

```twig
{# templates/pages/teams/new.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Create Team | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Create Team</h1>
        <a href="{{ path('team_list') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Teams
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    {{ form_start(form) }}
                        <div class="mb-3">
                            {{ form_label(form.name, 'Team Name', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(form.name, {'attr': {'class': 'form-control' ~ (form.name.vars.valid ? '' : ' is-invalid'), 'placeholder': 'e.g. FC Barcelona'}}) }}
                            {{ form_errors(form.name, {'attr': {'class': 'invalid-feedback'}}) }}
                            <div class="form-text">Choose a clear, recognizable name for your team.</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.description, 'Description', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(form.description, {'attr': {'class': 'form-control' ~ (form.description.vars.valid ? '' : ' is-invalid'), 'placeholder': 'Brief description of your team', 'rows': 3}}) }}
                            {{ form_errors(form.description, {'attr': {'class': 'invalid-feedback'}}) }}
                            <div class="form-text">Provide a short description to help members identify the team.</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.currency, 'Currency', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(form.currency, {'attr': {'class': 'form-select' ~ (form.currency.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(form.currency, {'attr': {'class': 'invalid-feedback'}}) }}
                            <div class="form-text">Select the currency that will be used for all financial transactions.</div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                {{ form_widget(form.isPublic, {'attr': {'class': 'form-check-input' ~ (form.isPublic.vars.valid ? '' : ' is-invalid')}}) }}
                                {{ form_label(form.isPublic, 'Make this team public', {'label_attr': {'class': 'form-check-label'}}) }}
                                {{ form_errors(form.isPublic, {'attr': {'class': 'invalid-feedback'}}) }}
                            </div>
                            <div class="form-text">Public teams can be found by other users. Private teams are accessible by invitation only.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-users me-1"></i> Create Team
                            </button>
                        </div>
                    {{ form_end(form) }}
                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

### Features

- Clean form layout with appropriate validation
- Helpful descriptions for each field
- Currency selection for financial transactions
- Public/private team option
- Responsive design

## Team Details Page

The team details page provides comprehensive information about a specific team, including members, statistics, and recent activity.

### Design

```twig
{# templates/pages/teams/details.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}{{ team.name }} | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ team.name }}</h1>
            <p class="text-muted mb-0">{{ team.description }}</p>
        </div>
        <div class="d-flex">
            <a href="{{ path('dashboard', {'team': team.id}) }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
            </a>
            {% if is_team_admin %}
                <a href="{{ path('team_settings', {'id': team.id}) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-cog me-1"></i> Settings
                </a>
            {% endif %}
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Team Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Created</small>
                        <div>{{ team.createdAt|date('d.m.Y') }}</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Members</small>
                        <div>{{ team.memberCount }}</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Currency</small>
                        <div>{{ team.currency }}</div>
                    </div>
                    <div>
                        <small class="text-muted d-block">Visibility</small>
                        <div>
                            {% if team.isPublic %}
                                <span class="badge bg-success">Public</span>
                            {% else %}
                                <span class="badge bg-secondary">Private</span>
                            {% endif %}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Financial Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Total Balance</small>
                        <div class="amount {% if team.balance >= 0 %}positive{% else %}negative{% endif %} monospace">
                            {{ team.balance|format_currency(team.currency) }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Unpaid Contributions</small>
                        <div class="amount {% if team.unpaidContributions > 0 %}negative{% else %}positive{% endif %} monospace">
                            {{ team.unpaidContributions|format_currency(team.currency) }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Unpaid Penalties</small>
                        <div class="amount {% if team.unpaidPenalties > 0 %}negative{% else %}positive{% endif %} monospace">
                            {{ team.unpaidPenalties|format_currency(team.currency) }}
                        </div>
                    </div>
                    <div>
                        <small class="text-muted d-block">Monthly Income</small>
                        <div class="amount positive monospace">
                            {{ team.monthlyIncome|format_currency(team.currency) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>Team Members
                    </h5>
                    {% if is_team_admin %}
                        <a href="{{ path('team_members', {'id': team.id}) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-user-plus me-1"></i> Manage Members
                        </a>
                    {% endif %}
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Unpaid Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                {% for member in team_members %}
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary text-white me-2">
                                                    {{ member.firstName|first }}{{ member.lastName|first }}
                                                </div>
                                                <div>
                                                    {{ member.firstName }} {{ member.lastName }}
                                                    {% if member.id == app.user.id %}
                                                        <span class="badge bg-secondary ms-1">You</span>
                                                    {% endif %}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {% if member.role == 'ADMIN' %}bg-primary{% else %}bg-secondary{% endif %}">
                                                {{ member.role }}
                                            </span>
                                        </td>
                                        <td>{{ member.joinedAt|date('d.m.Y') }}</td>
                                        <td class="amount {% if member.unpaidAmount > 0 %}negative{% else %}positive{% endif %} monospace">
                                            {{ member.unpaidAmount|format_currency(team.currency) }}
                                        </td>
                                    </tr>
                                {% endfor %}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Recent Activity
                    </h5>
                    <a href="{{ path('payment_list', {'team': team.id}) }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                {% if recent_activity|length > 0 %}
                                    {% for activity in recent_activity %}
                                        <tr>
                                            <td>{{ activity.date|date('d.m.Y') }}</td>
                                            <td>{{ activity.userName }}</td>
                                            <td>
                                                {% if activity.type == 'contribution' %}
                                                    <span class="badge bg-primary">Contribution</span>
                                                {% elseif activity.type == 'penalty' %}
                                                    <span class="badge bg-warning text-dark">Penalty</span>
                                                {% elseif activity.type == 'payment' %}
                                                    <span class="badge bg-success">Payment</span>
                                                {% endif %}
                                            </td>
                                            <td>{{ activity.description }}</td>
                                            <td class="text-end amount {% if activity.type == 'payment' %}positive{% else %}negative{% endif %} monospace">
                                                {{ activity.amount|format_currency(team.currency) }}
                                            </td>
                                        </tr>
                                    {% endfor %}
                                {% else %}
                                    <tr>
                                        <td colspan="5" class="text-center py-3">No recent activity</td>
                                    </tr>
                                {% endif %}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

### Features

- Comprehensive team information display
- Financial summary with key metrics
- Team member list with roles and financial status
- Recent activity table
- Role-based access to administrative functions
- Responsive layout that works on all devices

## Team Settings Page

The team settings page allows team administrators to manage team configuration.

### Design

```twig
{# templates/pages/teams/settings.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Team Settings | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Team Settings</h1>
            <p class="text-muted mb-0">{{ team.name }}</p>
        </div>
        <a href="{{ path('team_details', {'id': team.id}) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Team
        </a>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="list-group">
                <a href="#general" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                    <i class="fas fa-cog me-2"></i>General Settings
                </a>
                <a href="#members" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-users me-2"></i>Member Management
                </a>
                <a href="#invitations" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-envelope me-2"></i>Invitations
                </a>
                <a href="#danger" class="list-group-item list-group-item-action text-danger" data-bs-toggle="list">
                    <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                </a>
            </div>
        </div>

        <div class="col-md-9">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="general">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cog me-2"></i>General Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            {{ form_start(generalForm) }}
                                <div class="mb-3">
                                    {{ form_label(generalForm.name, 'Team Name', {'label_attr': {'class': 'form-label'}}) }}
                                    {{ form_widget(generalForm.name, {'attr': {'class': 'form-control' ~ (generalForm.name.vars.valid ? '' : ' is-invalid')}}) }}
                                    {{ form_errors(generalForm.name, {'attr': {'class': 'invalid-feedback'}}) }}
                                </div>

                                <div class="mb-3">
                                    {{ form_label(generalForm.description, 'Description', {'label_attr': {'class': 'form-label'}}) }}
                                    {{ form_widget(generalForm.description, {'attr': {'class': 'form-control' ~ (generalForm.description.vars.valid ? '' : ' is-invalid'), 'rows': 3}}) }}
                                    {{ form_errors(generalForm.description, {'attr': {'class': 'invalid-feedback'}}) }}
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        {{ form_widget(generalForm.isPublic, {'attr': {'class': 'form-check-input' ~ (generalForm.isPublic.vars.valid ? '' : ' is-invalid')}}) }}
                                        {{ form_label(generalForm.isPublic, 'Make this team public', {'label_attr': {'class': 'form-check-label'}}) }}
                                        {{ form_errors(generalForm.isPublic, {'attr': {'class': 'invalid-feedback'}}) }}
                                    </div>
                                    <div class="form-text">Public teams can be found by other users. Private teams are accessible by invitation only.</div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Save Changes
                                    </button>
                                </div>
                            {{ form_end(generalForm) }}
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="members">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-users me-2"></i>Team Members
                            </h5>
                            <a href="{{ path('team_members', {'id': team.id}) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-user-plus me-1"></i> Manage Members
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Joined</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {% for member in team_members %}
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-circle bg-primary text-white me-2">
                                                            {{ member.firstName|first }}{{ member.lastName|first }}
                                                        </div>
                                                        <div>
                                                            {{ member.firstName }} {{ member.lastName }}
                                                            {% if member.id == app.user.id %}
                                                                <span class="badge bg-secondary ms-1">You</span>
                                                            {% endif %}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ member.email }}</td>
                                                <td>
                                                    <span class="badge {% if member.role == 'ADMIN' %}bg-primary{% else %}bg-secondary{% endif %}">
                                                        {{ member.role }}
                                                    </span>
                                                </td>
                                                <td>{{ member.joinedAt|date('d.m.Y') }}</td>
                                                <td class="text-end">
                                                    {% if member.id != app.user.id %}
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                                Actions
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                {% if member.role != 'ADMIN' %}
                                                                    <li>
                                                                        <form method="post" action="{{ path('team_member_promote', {'teamId': team.id, 'userId': member.id}) }}">
                                                                            <button type="submit" class="dropdown-item">
                                                                                <i class="fas fa-arrow-up me-1"></i> Promote to Admin
                                                                            </button>
                                                                        </form>
                                                                    </li>
                                                                {% else %}
                                                                    <li>
                                                                        <form method="post" action="{{ path('team_member_demote', {'teamId': team.id, 'userId': member.id}) }}">
                                                                            <button type="submit" class="dropdown-item">
                                                                                <i class="fas fa-arrow-down me-1"></i> Demote to Member
                                                                            </button>
                                                                        </form>
                                                                    </li>
                                                                {% endif %}
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <form method="post" action="{{ path('team_member_remove', {'teamId': team.id, 'userId': member.id}) }}" onsubmit="return confirm('Are you sure you want to remove this member?');">
                                                                        <button type="submit" class="dropdown-item text-danger">
                                                                            <i class="fas fa-user-minus me-1"></i> Remove from Team
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    {% endif %}
                                                </td>
                                            </tr>
                                        {% endfor %}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="invitations">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-envelope me-2"></i>Invitations
                            </h5>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#inviteModal">
                                <i class="fas fa-user-plus me-1"></i> Invite Member
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Email</th>
                                            <th>Invited By</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {% if invitations|length > 0 %}
                                            {% for invitation in invitations %}
                                                <tr>
                                                    <td>{{ invitation.email }}</td>
                                                    <td>{{ invitation.inviterName }}</td>
                                                    <td>{{ invitation.createdAt|date('d.m.Y') }}</td>
                                                    <td>
                                                        <span class="badge {% if invitation.status == 'PENDING' %}bg-warning text-dark{% elseif invitation.status == 'ACCEPTED' %}bg-success{% else %}bg-danger{% endif %}">
                                                            {{ invitation.status }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        {% if invitation.status == 'PENDING' %}
                                                            <form method="post" action="{{ path('team_invitation_cancel', {'id': invitation.id}) }}" class="d-inline">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                    <i class="fas fa-times me-1"></i> Cancel
                                                                </button>
                                                            </form>
                                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#resendModal{{ invitation.id }}">
                                                                <i class="fas fa-paper-plane me-1"></i> Resend
                                                            </button>
                                                        {% endif %}
                                                    </td>
                                                </tr>
                                            {% endfor %}
                                        {% else %}
                                            <tr>
                                                <td colspan="5" class="text-center py-3">No invitations found</td>
                                            </tr>
                                        {% endif %}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="danger">
                    <div class="card shadow-sm border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Warning:</strong> The actions in this section can have permanent consequences.
                            </div>

                            <div class="mb-4">
                                <h6>Leave Team</h6>
                                <p class="text-muted">You will no longer have access to this team's data.</p>
                                <form method="post" action="{{ path('team_leave', {'id': team.id}) }}" onsubmit="return confirm('Are you sure you want to leave this team?');">
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-sign-out-alt me-1"></i> Leave Team
                                    </button>
                                </form>
                            </div>

                            {% if is_team_admin %}
                                <div class="mb-4">
                                    <h6>Transfer Ownership</h6>
                                    <p class="text-muted">Transfer ownership of this team to another admin.</p>
                                    <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#transferModal">
                                        <i class="fas fa-exchange-alt me-1"></i> Transfer Ownership
                                    </button>
                                </div>

                                <div>
                                    <h6>Delete Team</h6>
                                    <p class="text-muted">Permanently delete this team and all its data. This action cannot be undone.</p>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="fas fa-trash-alt me-1"></i> Delete Team
                                    </button>
                                </div>
                            {% endif %}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {# Invite Member Modal #}
    <div class="modal fade" id="inviteModal" tabindex="-1" aria-labelledby="inviteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inviteModalLabel">Invite Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{ form_start(inviteForm) }}
                        <div class="mb-3">
                            {{ form_label(inviteForm.email, 'Email Address', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(inviteForm.email, {'attr': {'class': 'form-control' ~ (inviteForm.email.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(inviteForm.email, {'attr': {'class': 'invalid-feedback'}}) }}
                            <div class="form-text">Enter the email address of the person you want to invite.</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(inviteForm.role, 'Role', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(inviteForm.role, {'attr': {'class': 'form-select' ~ (inviteForm.role.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(inviteForm.role, {'attr': {'class': 'invalid-feedback'}}) }}
                        </div>

                        <div class="mb-3">
                            {{ form_label(inviteForm.message, 'Personal Message (Optional)', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(inviteForm.message, {'attr': {'class': 'form-control' ~ (inviteForm.message.vars.valid ? '' : ' is-invalid'), 'rows': 3}}) }}
                            {{ form_errors(inviteForm.message, {'attr': {'class': 'invalid-feedback'}}) }}
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Send Invitation
                            </button>
                        </div>
                    {{ form_end(inviteForm) }}
                </div>
            </div>
        </div>
    </div>

    {# Delete Team Modal #}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Team</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action cannot be undone.
                    </div>
                    <p>Are you sure you want to permanently delete the team <strong>{{ team.name }}</strong>?</p>
                    <p>All data associated with this team will be permanently deleted, including:</p>
                    <ul>
                        <li>Contributions and payments</li>
                        <li>Penalties</li>
                        <li>Reports and statistics</li>
                        <li>Member associations</li>
                    </ul>
                    <p>Please type <strong>{{ team.name }}</strong> to confirm:</p>
                    <form method="post" action="{{ path('team_delete', {'id': team.id}) }}" id="deleteTeamForm">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="confirmTeamName" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="deleteTeamForm" class="btn btn-danger" id="deleteTeamButton" disabled>
                        <i class="fas fa-trash-alt me-1"></i> Delete Team
                    </button>
                </div>
            </div>
        </div>
    </div>
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enable/disable delete button based on confirmation text
            const confirmInput = document.getElementById('confirmTeamName');
            const deleteButton = document.getElementById('deleteTeamButton');
            const teamName = "{{ team.name }}";

            if (confirmInput && deleteButton) {
                confirmInput.addEventListener('input', function() {
                    deleteButton.disabled = confirmInput.value !== teamName;
                });
            }

            // Activate tab based on hash
            const hash = window.location.hash;
            if (hash) {
                const tab = document.querySelector(`a[href="${hash}"]`);
                if (tab) {
                    tab.click();
                }
            }
        });
    </script>
{% endblock %}
```

### Features

- Tabbed interface for different settings categories
- Team information editing
- Member management with role promotion/demotion
- Invitation system with status tracking
- Danger zone with appropriate warnings and confirmations
- Modal dialogs for destructive actions
- JavaScript validation for critical operations

## Member Management Page

The member management page provides a dedicated interface for adding, removing, and managing team members.

### Design

```twig
{# templates/pages/teams/members.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Team Members | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Team Members</h1>
            <p class="text-muted mb-0">{{ team.name }}</p>
        </div>
        <div class="d-flex">
            <a href="{{ path('team_details', {'id': team.id}) }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Team
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inviteModal">
                <i class="fas fa-user-plus me-1"></i> Invite Member
            </button>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-users me-2"></i>Current Members
            </h5>
            <div class="input-group" style="width: 250px;">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="memberSearch" placeholder="Search members...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="membersTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Unpaid Amount</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {% for member in team_members %}
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white me-2">
                                            {{ member.firstName|first }}{{ member.lastName|first }}
                                        </div>
                                        <div>
                                            {{ member.firstName }} {{ member.lastName }}
                                            {% if member.id == app.user.id %}
                                                <span class="badge bg-secondary ms-1">You</span>
                                            {% endif %}
                                        </div>
                                    </div>
                                </td>
                                <td>{{ member.email }}</td>
                                <td>
                                    <span class="badge {% if member.role == 'ADMIN' %}bg-primary{% else %}bg-secondary{% endif %}">
                                        {{ member.role }}
                                    </span>
                                </td>
                                <td>{{ member.joinedAt|date('d.m.Y') }}</td>
                                <td class="amount {% if member.unpaidAmount > 0 %}negative{% else %}positive{% endif %} monospace">
                                    {{ member.unpaidAmount|format_currency(team.currency) }}
                                </td>
                                <td class="text-end">
                                    {% if member.id != app.user.id %}
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                {% if member.role != 'ADMIN' %}
                                                    <li>
                                                        <form method="post" action="{{ path('team_member_promote', {'teamId': team.id, 'userId': member.id}) }}">
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fas fa-arrow-up me-1"></i> Promote to Admin
                                                            </button>
                                                        </form>
                                                    </li>
                                                {% else %}
                                                    <li>
                                                        <form method="post" action="{{ path('team_member_demote', {'teamId': team.id, 'userId': member.id}) }}">
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fas fa-arrow-down me-1"></i> Demote to Member
                                                            </button>
                                                        </form>
                                                    </li>
                                                {% endif %}
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="post" action="{{ path('team_member_remove', {'teamId': team.id, 'userId': member.id}) }}" onsubmit="return confirm('Are you sure you want to remove this member?');">
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="fas fa-user-minus me-1"></i> Remove from Team
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    {% endif %}
                                </td>
                            </tr>
                        {% endfor %}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-envelope me-2"></i>Pending Invitations
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Invited By</th>
                            <th>Date</th>
                            <th>Role</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {% if pending_invitations|length > 0 %}
                            {% for invitation in pending_invitations %}
                                <tr>
                                    <td>{{ invitation.email }}</td>
                                    <td>{{ invitation.inviterName }}</td>
                                    <td>{{ invitation.createdAt|date('d.m.Y') }}</td>
                                    <td>
                                        <span class="badge {% if invitation.role == 'ADMIN' %}bg-primary{% else %}bg-secondary{% endif %}">
                                            {{ invitation.role }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <form method="post" action="{{ path('team_invitation_cancel', {'id': invitation.id}) }}" class="d-inline">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times me-1"></i> Cancel
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#resendModal{{ invitation.id }}">
                                            <i class="fas fa-paper-plane me-1"></i> Resend
                                        </button>
                                    </td>
                                </tr>
                            {% endfor %}
                        {% else %}
                            <tr>
                                <td colspan="5" class="text-center py-3">No pending invitations</td>
                            </tr>
                        {% endif %}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {# Invite Member Modal #}
    <div class="modal fade" id="inviteModal" tabindex="-1" aria-labelledby="inviteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inviteModalLabel">Invite Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{ form_start(inviteForm) }}
                        <div class="mb-3">
                            {{ form_label(inviteForm.email, 'Email Address', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(inviteForm.email, {'attr': {'class': 'form-control' ~ (inviteForm.email.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(inviteForm.email, {'attr': {'class': 'invalid-feedback'}}) }}
                            <div class="form-text">Enter the email address of the person you want to invite.</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(inviteForm.role, 'Role', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(inviteForm.role, {'attr': {'class': 'form-select' ~ (inviteForm.role.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(inviteForm.role, {'attr': {'class': 'invalid-feedback'}}) }}
                        </div>

                        <div class="mb-3">
                            {{ form_label(inviteForm.message, 'Personal Message (Optional)', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(inviteForm.message, {'attr': {'class': 'form-control' ~ (inviteForm.message.vars.valid ? '' : ' is-invalid'), 'rows': 3}}) }}
                            {{ form_errors(inviteForm.message, {'attr': {'class': 'invalid-feedback'}}) }}
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Send Invitation
                            </button>
                        </div>
                    {{ form_end(inviteForm) }}
                </div>
            </div>
        </div>
    </div>
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Member search functionality
            const searchInput = document.getElementById('memberSearch');
            const table = document.getElementById('membersTable');
            const rows = table.querySelectorAll('tbody tr');

            searchInput.addEventListener('keyup', function() {
                const query = searchInput.value.toLowerCase();

                rows.forEach(function(row) {
                    const name = row.querySelector('td:first-child').textContent.toLowerCase();
                    const email = row.querySelector('td:nth-child(2)').textContent.toLowerCase();

                    if (name.includes(query) || email.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
{% endblock %}
```

### Features

- Comprehensive member listing with search functionality
- Role management (promote/demote)
- Member removal
- Invitation management
- Financial status of each member
- Modal for sending new invitations
- JavaScript for dynamic filtering

## Data Requirements

The team management pages require the following data from the backend:

1. **User Information**:
   - Current user details
   - User permissions and roles

2. **Team Information**:
   - Team details (name, description, etc.)
   - Team financial summary
   - Team members and their roles
   - Recent activity

3. **Invitation Information**:
   - Pending invitations
   - Invitation history

## Controller Logic

The team management controllers will need to:

1. **Team List Controller**:
   - Fetch all teams the user belongs to
   - Fetch pending team invitations for the user

2. **Team Creation Controller**:
   - Handle team creation form submission
   - Create new team with the user as admin

3. **Team Details Controller**:
   - Fetch detailed team information
   - Fetch team members and their details
   - Fetch recent activity for the team

4. **Team Settings Controller**:
   - Handle team settings form submission
   - Manage team member roles
   - Process invitations
   - Handle team deletion

5. **Member Management Controller**:
   - Handle member addition/removal
   - Process role changes
   - Manage invitations

## User Experience Considerations

1. **Role-Based Access Control**: Show/hide actions based on user role
2. **Confirmation Dialogs**: Require confirmation for destructive actions
3. **Form Validation**: Provide clear feedback for form errors
4. **Search Functionality**: Allow users to search for members
5. **Responsive Design**: Ensure all pages work well on all devices
6. **Loading States**: Show loading indicators while data is being fetched
7. **Error Handling**: Gracefully handle and display errors

## Next Steps

With the team management pages designed, we can now proceed to implement the contribution management pages as described in [05-contribution-management.md](05-contribution-management.md).
