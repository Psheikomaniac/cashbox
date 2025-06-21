# User Profile & Settings

## Overview

The user profile and settings section of the Cashbox application allows users to manage their personal information, notification preferences, and account settings. This document outlines the design and implementation of the user profile and settings pages, which provide a comprehensive interface for users to customize their experience.

## User Stories

1. As a user, I want to view and edit my profile information so that my details are up-to-date
2. As a user, I want to change my password so that I can maintain account security
3. As a user, I want to configure notification preferences so that I receive relevant alerts
4. As a user, I want to view my activity history so that I can track my actions in the system
5. As a user, I want to manage my account settings so that I can customize my experience

## User Profile & Settings Pages

The user profile and settings section consists of the following pages:

1. **Profile Overview**: Summary of user information and recent activity
2. **Edit Profile**: Form for updating personal information
3. **Security Settings**: Password management and security options
4. **Notification Preferences**: Configuration for email and in-app notifications
5. **Account Settings**: General account configuration options

## Profile Overview Page

The profile overview page provides a summary of the user's information, team memberships, and recent activity.

### Design

```twig
{# templates/pages/profile/overview.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}My Profile | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">My Profile</h1>
        <div class="btn-group">
            <a href="{{ path('profile_edit') }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Edit Profile
            </a>
            <a href="{{ path('profile_security') }}" class="btn btn-outline-secondary">
                <i class="fas fa-lock me-1"></i> Security
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4 mb-lg-0">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="avatar-circle mx-auto mb-3" style="width: 100px; height: 100px; font-size: 40px;">
                        {{ app.user.initials }}
                    </div>
                    <h4 class="mb-0">{{ app.user.fullName }}</h4>
                    <p class="text-muted">{{ app.user.email }}</p>
                    <div class="d-flex justify-content-center">
                        <a href="{{ path('profile_edit') }}" class="btn btn-sm btn-outline-primary me-2">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="{{ path('profile_security') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-lock me-1"></i> Security
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <div class="row text-center">
                        <div class="col-4">
                            <h6 class="mb-0">{{ user_stats.teamCount }}</h6>
                            <small class="text-muted">Teams</small>
                        </div>
                        <div class="col-4">
                            <h6 class="mb-0">{{ user_stats.contributionCount }}</h6>
                            <small class="text-muted">Contributions</small>
                        </div>
                        <div class="col-4">
                            <h6 class="mb-0">{{ user_stats.paymentRate }}%</h6>
                            <small class="text-muted">Payment Rate</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Full Name</h6>
                        <p class="mb-0">{{ app.user.fullName }}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Email</h6>
                        <p class="mb-0">{{ app.user.email }}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Phone</h6>
                        <p class="mb-0">{{ app.user.phone ?: 'Not provided' }}</p>
                    </div>
                    <div class="mb-0">
                        <h6 class="text-muted mb-1">Member Since</h6>
                        <p class="mb-0">{{ app.user.createdAt|date('F d, Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Account Status</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Status</h6>
                        <p class="mb-0">
                            {% if app.user.isActive %}
                                <span class="badge bg-success">Active</span>
                            {% else %}
                                <span class="badge bg-secondary">Inactive</span>
                            {% endif %}
                        </p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Role</h6>
                        <p class="mb-0">
                            {% if is_granted('ROLE_ADMIN') %}
                                <span class="badge bg-danger">Administrator</span>
                            {% elseif is_granted('ROLE_TEAM_ADMIN') %}
                                <span class="badge bg-primary">Team Administrator</span>
                            {% else %}
                                <span class="badge bg-info">Member</span>
                            {% endif %}
                        </p>
                    </div>
                    <div class="mb-0">
                        <h6 class="text-muted mb-1">Last Login</h6>
                        <p class="mb-0">{{ app.user.lastLogin ? app.user.lastLogin|date('F d, Y H:i') : 'Never' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Team Memberships</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Team</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {% for membership in team_memberships %}
                                    <tr>
                                        <td>
                                            <a href="{{ path('team_details', {'id': membership.team.id}) }}" class="text-decoration-none">
                                                {{ membership.team.name }}
                                            </a>
                                        </td>
                                        <td>
                                            {% if membership.isAdmin %}
                                                <span class="badge bg-primary">Admin</span>
                                            {% else %}
                                                <span class="badge bg-info">Member</span>
                                            {% endif %}
                                        </td>
                                        <td>{{ membership.joinedAt|date('M d, Y') }}</td>
                                        <td>
                                            {% if membership.isActive %}
                                                <span class="badge bg-success">Active</span>
                                            {% else %}
                                                <span class="badge bg-secondary">Inactive</span>
                                            {% endif %}
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ path('team_details', {'id': membership.team.id}) }}" class="btn btn-outline-secondary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                {% if membership.isActive %}
                                                    <a href="{{ path('team_leave', {'id': membership.team.id}) }}" class="btn btn-outline-danger">
                                                        <i class="fas fa-sign-out-alt"></i>
                                                    </a>
                                                {% endif %}
                                            </div>
                                        </td>
                                    </tr>
                                {% else %}
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <p class="text-muted mb-0">You are not a member of any teams yet.</p>
                                            <a href="{{ path('teams') }}" class="btn btn-sm btn-primary mt-2">
                                                Browse Teams
                                            </a>
                                        </td>
                                    </tr>
                                {% endfor %}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Financial Summary</h5>
                    <a href="{{ path('reports_member', {'member': app.user.id}) }}" class="btn btn-sm btn-link">View Details</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-1">Total Contributions</h6>
                                    <p class="h4 mb-0">{{ user_stats.totalContributions|format_currency('EUR') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-1">Pending Payments</h6>
                                    <p class="h4 mb-0">{{ user_stats.pendingAmount|format_currency('EUR') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-1">Payment Rate</h6>
                                    <p class="h4 mb-0">{{ user_stats.paymentRate }}%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Activity</h5>
                    <a href="{{ path('profile_activity') }}" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        {% for activity in recent_activities %}
                            <li class="list-group-item">
                                <div class="d-flex">
                                    <div class="activity-icon me-3">
                                        <i class="{{ activity.icon }} {{ activity.iconColor }}"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0">{{ activity.description }}</p>
                                        <small class="text-muted">{{ activity.createdAt|date('M d, Y H:i') }}</small>
                                    </div>
                                </div>
                            </li>
                        {% else %}
                            <li class="list-group-item text-center py-4">
                                <p class="text-muted mb-0">No recent activity found.</p>
                            </li>
                        {% endfor %}
                    </ul>
                </div>
            </div>
        </div>
    </div>
{% endblock %}

{% block stylesheets %}
    {{ parent() }}
    <style>
        .activity-icon {
            width: 30px;
            text-align: center;
            font-size: 1.2rem;
        }
    </style>
{% endblock %}
```

## Edit Profile Page

The edit profile page allows users to update their personal information.

### Design

```twig
{# templates/pages/profile/edit.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Edit Profile | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit Profile</h1>
        <a href="{{ path('profile_overview') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    {{ form_start(form, {'attr': {'class': 'needs-validation', 'novalidate': 'novalidate'}}) }}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                {{ form_label(form.firstName) }}
                                {{ form_widget(form.firstName, {'attr': {'class': 'form-control' ~ (form.firstName.vars.valid ? '' : ' is-invalid')}}) }}
                                {{ form_errors(form.firstName) }}
                            </div>
                            <div class="col-md-6">
                                {{ form_label(form.lastName) }}
                                {{ form_widget(form.lastName, {'attr': {'class': 'form-control' ~ (form.lastName.vars.valid ? '' : ' is-invalid')}}) }}
                                {{ form_errors(form.lastName) }}
                            </div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.email) }}
                            {{ form_widget(form.email, {'attr': {'class': 'form-control' ~ (form.email.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(form.email) }}
                            <div class="form-text">Your email address is also used for login and notifications</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.phone) }}
                            {{ form_widget(form.phone, {'attr': {'class': 'form-control' ~ (form.phone.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(form.phone) }}
                            <div class="form-text">Optional: Your phone number will only be visible to team administrators</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.timezone) }}
                            {{ form_widget(form.timezone, {'attr': {'class': 'form-select' ~ (form.timezone.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(form.timezone) }}
                            <div class="form-text">Used for displaying dates and times in your local timezone</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.language) }}
                            {{ form_widget(form.language, {'attr': {'class': 'form-select' ~ (form.language.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(form.language) }}
                        </div>

                        <div class="mb-3">
                            {{ form_label(form.bio) }}
                            {{ form_widget(form.bio, {'attr': {'class': 'form-control' ~ (form.bio.vars.valid ? '' : ' is-invalid'), 'rows': '3'}}) }}
                            {{ form_errors(form.bio) }}
                            <div class="form-text">Optional: Tell others a bit about yourself</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ path('profile_overview') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    {{ form_end(form) }}
                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

## Security Settings Page

The security settings page allows users to change their password and manage security options.

### Design

```twig
{# templates/pages/profile/security.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Security Settings | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Security Settings</h1>
        <a href="{{ path('profile_overview') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    {{ form_start(password_form, {'attr': {'class': 'needs-validation', 'novalidate': 'novalidate'}}) }}
                        <div class="mb-3">
                            {{ form_label(password_form.currentPassword) }}
                            {{ form_widget(password_form.currentPassword, {'attr': {'class': 'form-control' ~ (password_form.currentPassword.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(password_form.currentPassword) }}
                        </div>

                        <div class="mb-3">
                            {{ form_label(password_form.newPassword.first) }}
                            {{ form_widget(password_form.newPassword.first, {'attr': {'class': 'form-control' ~ (password_form.newPassword.first.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(password_form.newPassword.first) }}
                            <div class="form-text">Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(password_form.newPassword.second) }}
                            {{ form_widget(password_form.newPassword.second, {'attr': {'class': 'form-control' ~ (password_form.newPassword.second.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(password_form.newPassword.second) }}
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </div>
                    {{ form_end(password_form) }}
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Login Sessions</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Device</th>
                                    <th>IP Address</th>
                                    <th>Last Activity</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {% for session in login_sessions %}
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="{{ session.deviceIcon }} me-2"></i>
                                                <div>
                                                    <span>{{ session.deviceName }}</span>
                                                    <small class="d-block text-muted">{{ session.browser }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ session.ipAddress }}</td>
                                        <td>{{ session.lastActivity|date('M d, Y H:i') }}</td>
                                        <td>
                                            {% if session.isCurrent %}
                                                <span class="badge bg-success">Current</span>
                                            {% else %}
                                                <span class="badge bg-secondary">Active</span>
                                            {% endif %}
                                        </td>
                                        <td>
                                            {% if not session.isCurrent %}
                                                <a href="{{ path('profile_terminate_session', {'id': session.id}) }}" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            {% else %}
                                                <span class="text-muted">-</span>
                                            {% endif %}
                                        </td>
                                    </tr>
                                {% else %}
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <p class="text-muted mb-0">No active sessions found.</p>
                                        </td>
                                    </tr>
                                {% endfor %}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <a href="{{ path('profile_terminate_all_sessions') }}" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt me-1"></i> Log Out All Other Devices
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Account Security</h5>
                </div>
                <div class="card-body">
                    {{ form_start(security_form) }}
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                {{ form_widget(security_form.twoFactorEnabled, {'attr': {'class': 'form-check-input'}}) }}
                                {{ form_label(security_form.twoFactorEnabled, null, {'label_attr': {'class': 'form-check-label'}}) }}
                            </div>
                            <div class="form-text">Require a verification code when logging in from a new device</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                {{ form_widget(security_form.loginNotifications, {'attr': {'class': 'form-check-input'}}) }}
                                {{ form_label(security_form.loginNotifications, null, {'label_attr': {'class': 'form-check-label'}}) }}
                            </div>
                            <div class="form-text">Receive email notifications for new login attempts</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Save Security Settings</button>
                        </div>
                    {{ form_end(security_form) }}
                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

## Notification Preferences Page

The notification preferences page allows users to configure which notifications they receive and how they receive them.

### Design

```twig
{# templates/pages/profile/notifications.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Notification Preferences | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Notification Preferences</h1>
        <a href="{{ path('profile_overview') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Notification Settings</h5>
                </div>
                <div class="card-body">
                    {{ form_start(form) }}
                        <div class="mb-4">
                            <h6>Delivery Methods</h6>
                            <div class="form-check form-switch mb-2">
                                {{ form_widget(form.emailNotifications, {'attr': {'class': 'form-check-input'}}) }}
                                {{ form_label(form.emailNotifications, null, {'label_attr': {'class': 'form-check-label'}}) }}
                            </div>
                            <div class="form-check form-switch">
                                {{ form_widget(form.inAppNotifications, {'attr': {'class': 'form-check-input'}}) }}
                                {{ form_label(form.inAppNotifications, null, {'label_attr': {'class': 'form-check-label'}}) }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Contribution Notifications</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Notification Type</th>
                                            <th class="text-center">Email</th>
                                            <th class="text-center">In-App</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>New contribution assigned</td>
                                            <td class="text-center">
                                                {{ form_widget(form.newContributionEmail, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                            <td class="text-center">
                                                {{ form_widget(form.newContributionInApp, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Contribution due reminder (3 days before)</td>
                                            <td class="text-center">
                                                {{ form_widget(form.contributionReminderEmail, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                            <td class="text-center">
                                                {{ form_widget(form.contributionReminderInApp, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Contribution overdue</td>
                                            <td class="text-center">
                                                {{ form_widget(form.contributionOverdueEmail, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                            <td class="text-center">
                                                {{ form_widget(form.contributionOverdueInApp, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Contribution paid</td>
                                            <td class="text-center">
                                                {{ form_widget(form.contributionPaidEmail, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                            <td class="text-center">
                                                {{ form_widget(form.contributionPaidInApp, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Penalty Notifications</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Notification Type</th>
                                            <th class="text-center">Email</th>
                                            <th class="text-center">In-App</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>New penalty assigned</td>
                                            <td class="text-center">
                                                {{ form_widget(form.newPenaltyEmail, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                            <td class="text-center">
                                                {{ form_widget(form.newPenaltyInApp, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Penalty reminder (weekly)</td>
                                            <td class="text-center">
                                                {{ form_widget(form.penaltyReminderEmail, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                            <td class="text-center">
                                                {{ form_widget(form.penaltyReminderInApp, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Penalty paid</td>
                                            <td class="text-center">
                                                {{ form_widget(form.penaltyPaidEmail, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                            <td class="text-center">
                                                {{ form_widget(form.penaltyPaidInApp, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Team Notifications</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Notification Type</th>
                                            <th class="text-center">Email</th>
                                            <th class="text-center">In-App</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Team invitation</td>
                                            <td class="text-center">
                                                {{ form_widget(form.teamInvitationEmail, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                            <td class="text-center">
                                                {{ form_widget(form.teamInvitationInApp, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Role changes</td>
                                            <td class="text-center">
                                                {{ form_widget(form.roleChangeEmail, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                            <td class="text-center">
                                                {{ form_widget(form.roleChangeInApp, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Team announcements</td>
                                            <td class="text-center">
                                                {{ form_widget(form.teamAnnouncementEmail, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                            <td class="text-center">
                                                {{ form_widget(form.teamAnnouncementInApp, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Payment Notifications</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Notification Type</th>
                                            <th class="text-center">Email</th>
                                            <th class="text-center">In-App</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Payment confirmation</td>
                                            <td class="text-center">
                                                {{ form_widget(form.paymentConfirmationEmail, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                            <td class="text-center">
                                                {{ form_widget(form.paymentConfirmationInApp, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Payment rejection</td>
                                            <td class="text-center">
                                                {{ form_widget(form.paymentRejectionEmail, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                            <td class="text-center">
                                                {{ form_widget(form.paymentRejectionInApp, {'attr': {'class': 'form-check-input'}}) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6>Notification Schedule</h6>
                            <div class="mb-3">
                                {{ form_label(form.digestFrequency) }}
                                {{ form_widget(form.digestFrequency, {'attr': {'class': 'form-select'}}) }}
                                <div class="form-text">Receive a digest of notifications instead of individual emails</div>
                            </div>
                            <div class="mb-3">
                                {{ form_label(form.quietHoursStart) }}
                                {{ form_widget(form.quietHoursStart, {'attr': {'class': 'form-control'}}) }}
                                <div class="form-text">Start time for quiet hours (no notifications)</div>
                            </div>
                            <div class="mb-3">
                                {{ form_label(form.quietHoursEnd) }}
                                {{ form_widget(form.quietHoursEnd, {'attr': {'class': 'form-control'}}) }}
                                <div class="form-text">End time for quiet hours (no notifications)</div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Save Notification Preferences</button>
                        </div>
                    {{ form_end(form) }}
                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

## Account Settings Page

The account settings page allows users to manage general account settings and preferences.

### Design

```twig
{# templates/pages/profile/settings.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Account Settings | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Account Settings</h1>
        <a href="{{ path('profile_overview') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Display Preferences</h5>
                </div>
                <div class="card-body">
                    {{ form_start(display_form) }}
                        <div class="mb-3">
                            {{ form_label(display_form.theme) }}
                            {{ form_widget(display_form.theme, {'attr': {'class': 'form-select'}}) }}
                            <div class="form-text">Choose the application theme</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(display_form.dateFormat) }}
                            {{ form_widget(display_form.dateFormat, {'attr': {'class': 'form-select'}}) }}
                            <div class="form-text">Choose how dates are displayed</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(display_form.timeFormat) }}
                            {{ form_widget(display_form.timeFormat, {'attr': {'class': 'form-select'}}) }}
                            <div class="form-text">Choose how times are displayed</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(display_form.currency) }}
                            {{ form_widget(display_form.currency, {'attr': {'class': 'form-select'}}) }}
                            <div class="form-text">Default currency for displaying amounts</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                {{ form_widget(display_form.compactTables, {'attr': {'class': 'form-check-input'}}) }}
                                {{ form_label(display_form.compactTables, null, {'label_attr': {'class': 'form-check-label'}}) }}
                            </div>
                            <div class="form-text">Display tables in a more compact format</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Save Display Preferences</button>
                        </div>
                    {{ form_end(display_form) }}
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Default Settings</h5>
                </div>
                <div class="card-body">
                    {{ form_start(defaults_form) }}
                        <div class="mb-3">
                            {{ form_label(defaults_form.defaultTeam) }}
                            {{ form_widget(defaults_form.defaultTeam, {'attr': {'class': 'form-select'}}) }}
                            <div class="form-text">Team to show by default when you log in</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(defaults_form.defaultPage) }}
                            {{ form_widget(defaults_form.defaultPage, {'attr': {'class': 'form-select'}}) }}
                            <div class="form-text">Page to show by default when you log in</div>
                        </div>

                        <div class="mb-3">
                            {{ form_label(defaults_form.itemsPerPage) }}
                            {{ form_widget(defaults_form.itemsPerPage, {'attr': {'class': 'form-select'}}) }}
                            <div class="form-text">Number of items to show per page in lists</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Save Default Settings</button>
                        </div>
                    {{ form_end(defaults_form) }}
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0 text-danger">Danger Zone</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6>Export Your Data</h6>
                        <p class="text-muted">Download a copy of all your personal data from the system.</p>
                        <a href="{{ path('profile_export_data') }}" class="btn btn-outline-primary">
                            <i class="fas fa-download me-1"></i> Export Data
                        </a>
                    </div>

                    <div class="mb-4">
                        <h6>Deactivate Account</h6>
                        <p class="text-muted">Temporarily deactivate your account. You can reactivate it later by logging in.</p>
                        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#deactivateModal">
                            <i class="fas fa-user-slash me-1"></i> Deactivate Account
                        </button>
                    </div>

                    <div>
                        <h6>Delete Account</h6>
                        <p class="text-muted">Permanently delete your account and all associated data. This action cannot be undone.</p>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash me-1"></i> Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deactivate Account Modal -->
    <div class="modal fade" id="deactivateModal" tabindex="-1" aria-labelledby="deactivateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deactivateModalLabel">Deactivate Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to deactivate your account? You will be logged out and your account will be temporarily disabled.</p>
                    <p>You can reactivate your account at any time by logging in again.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="{{ path('profile_deactivate') }}" class="btn btn-warning">Deactivate Account</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger fw-bold">Warning: This action cannot be undone!</p>
                    <p>Deleting your account will:</p>
                    <ul>
                        <li>Permanently remove all your personal information</li>
                        <li>Remove you from all teams</li>
                        <li>Delete your contribution and payment history</li>
                        <li>Make it impossible to recover your account</li>
                    </ul>
                    <div class="mb-3">
                        <label for="deleteConfirmation" class="form-label">Type "DELETE" to confirm:</label>
                        <input type="text" class="form-control" id="deleteConfirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn" disabled>Delete Account</button>
                </div>
            </div>
        </div>
    </div>
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete account confirmation
            const deleteConfirmationInput = document.getElementById('deleteConfirmation');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

            deleteConfirmationInput.addEventListener('input', function() {
                confirmDeleteBtn.disabled = this.value !== 'DELETE';
            });

            confirmDeleteBtn.addEventListener('click', function() {
                if (deleteConfirmationInput.value === 'DELETE') {
                    window.location.href = '{{ path('profile_delete') }}';
                }
            });
        });
    </script>
{% endblock %}
```

## Activity History Page

The activity history page displays a detailed log of the user's actions in the system.

### Design

```twig
{# templates/pages/profile/activity.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Activity History | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Activity History</h1>
        <a href="{{ path('profile_overview') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <form class="row g-3" method="get" action="{{ path('profile_activity') }}">
                <div class="col-md-4">
                    <label for="filter-type" class="form-label">Activity Type</label>
                    <select id="filter-type" name="type" class="form-select">
                        <option value="">All Activities</option>
                        <option value="login" {{ app.request.query.get('type') == 'login' ? 'selected' : '' }}>Login/Security</option>
                        <option value="profile" {{ app.request.query.get('type') == 'profile' ? 'selected' : '' }}>Profile Updates</option>
                        <option value="team" {{ app.request.query.get('type') == 'team' ? 'selected' : '' }}>Team Activities</option>
                        <option value="contribution" {{ app.request.query.get('type') == 'contribution' ? 'selected' : '' }}>Contributions</option>
                        <option value="penalty" {{ app.request.query.get('type') == 'penalty' ? 'selected' : '' }}>Penalties</option>
                        <option value="payment" {{ app.request.query.get('type') == 'payment' ? 'selected' : '' }}>Payments</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-date-from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="filter-date-from" name="dateFrom" value="{{ app.request.query.get('dateFrom') }}">
                </div>
                <div class="col-md-4">
                    <label for="filter-date-to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="filter-date-to" name="dateTo" value="{{ app.request.query.get('dateTo') }}">
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ path('profile_activity') }}" class="btn btn-outline-secondary ms-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Activity Log</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Activity</th>
                            <th>Type</th>
                            <th>IP Address</th>
                            <th>Device</th>
                        </tr>
                    </thead>
                    <tbody>
                        {% for activity in activities %}
                            <tr>
                                <td>{{ activity.createdAt|date('M d, Y H:i:s') }}</td>
                                <td>{{ activity.description }}</td>
                                <td>
                                    <span class="badge {{ activity.typeColor }}">{{ activity.type }}</span>
                                </td>
                                <td>{{ activity.ipAddress }}</td>
                                <td>{{ activity.device }}</td>
                            </tr>
                        {% else %}
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <p class="text-muted mb-0">No activity records found.</p>
                                </td>
                            </tr>
                        {% endfor %}
                    </tbody>
                </table>
            </div>
        </div>
        {% if activities|length > 0 %}
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">Showing {{ activities|length }} of {{ total_activities }} activities</small>
                    </div>
                    {% include 'components/pagination.html.twig' with {
                        'current_page': current_page,
                        'total_pages': total_pages,
                        'route': 'profile_activity',
                        'route_params': app.request.query.all
                    } %}
                </div>
            </div>
        {% endif %}
    </div>
{% endblock %}
```

## Integration with Backend

The user profile and settings frontend will integrate with the backend API endpoints:

- `GET /api/profile`: Retrieve user profile information
- `PUT /api/profile`: Update user profile information
- `GET /api/profile/activity`: Retrieve user activity history
- `GET /api/profile/teams`: Retrieve user team memberships
- `GET /api/profile/stats`: Retrieve user statistics
- `PUT /api/profile/password`: Change user password
- `GET /api/profile/sessions`: Retrieve user login sessions
- `DELETE /api/profile/sessions/{id}`: Terminate a specific login session
- `DELETE /api/profile/sessions`: Terminate all login sessions except current
- `PUT /api/profile/security`: Update security settings
- `GET /api/profile/notifications`: Retrieve notification preferences
- `PUT /api/profile/notifications`: Update notification preferences
- `GET /api/profile/settings`: Retrieve account settings
- `PUT /api/profile/settings/display`: Update display preferences
- `PUT /api/profile/settings/defaults`: Update default settings
- `GET /api/profile/export`: Export user data
- `PUT /api/profile/deactivate`: Deactivate user account
- `DELETE /api/profile`: Delete user account

## JavaScript Enhancements

The user profile and settings pages will include JavaScript enhancements for a better user experience:

1. **Form Validation**: Client-side validation for all forms
2. **Password Strength Meter**: Visual indicator of password strength
3. **Confirmation Dialogs**: Confirmation for destructive actions like account deletion
4. **Dynamic Form Updates**: Show/hide form fields based on user selections
5. **Real-time Notifications**: Update notification settings without page reload
6. **Theme Preview**: Preview theme changes before applying them

## Responsive Design Considerations

The user profile and settings pages will be fully responsive:

1. **Mobile-Friendly Forms**: Forms that adapt to smaller screens
2. **Stacked Layouts**: Vertical stacking of content on smaller screens
3. **Touch-Friendly Controls**: Larger touch targets for mobile users
4. **Simplified Views**: Condensed data views for mobile devices
5. **Accessible Modals**: Properly sized and positioned modals on all devices

## Accessibility Features

The user profile and settings pages will include the following accessibility features:

1. **ARIA Labels**: Proper ARIA labels for all interactive elements
2. **Keyboard Navigation**: Full keyboard navigation support
3. **Screen Reader Support**: Semantic HTML and appropriate text alternatives
4. **Color Contrast**: High contrast for all text elements
5. **Focus Indicators**: Clear focus indicators for keyboard navigation
6. **Error Messages**: Accessible error messages for form validation

## Next Steps

After implementing the user profile and settings pages, the next step will be to implement responsive optimizations as outlined in [10-responsive-optimizations.md](10-responsive-optimizations.md).
