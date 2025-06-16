# Dashboard

## Overview

The dashboard is the central hub of the Cashbox application, providing users with a comprehensive overview of their financial status and quick access to key actions. It serves as the landing page after authentication and is designed to give users immediate insights into their team's financial health.

## Design Goals

1. **Clarity**: Present key financial information in a clear, easy-to-understand format
2. **Actionability**: Enable users to take common actions directly from the dashboard
3. **Personalization**: Show relevant information based on the user's role and teams
4. **Responsiveness**: Ensure the dashboard works well on all devices
5. **Performance**: Load quickly and efficiently, even with large amounts of data

## Dashboard Layout

The dashboard will be divided into several sections:

1. **Header**: User information, notifications, and global actions
2. **Financial Summary**: Key financial metrics and indicators
3. **Recent Activity**: Latest transactions and events
4. **Quick Actions**: Common tasks that users can perform
5. **Team Overview**: Summary of team financial status (for users in multiple teams)
6. **Upcoming Payments**: Contributions and penalties due soon

### Dashboard Template

```twig
{# templates/pages/dashboard/index.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block title %}Dashboard | Cashbox{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Dashboard</h1>
        <div class="d-flex">
            <div class="dropdown me-2">
                {% if user_teams|length > 1 %}
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="teamSelector" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-users me-1"></i> {{ current_team.name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="teamSelector">
                        {% for team in user_teams %}
                            <li>
                                <a class="dropdown-item {% if team.id == current_team.id %}active{% endif %}" href="{{ path('dashboard', {'team': team.id}) }}">
                                    {{ team.name }}
                                </a>
                            </li>
                        {% endfor %}
                    </ul>
                {% else %}
                    <span class="btn btn-outline-primary disabled">
                        <i class="fas fa-users me-1"></i> {{ current_team.name }}
                    </span>
                {% endif %}
            </div>
            <a href="{{ path('contribution_new') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Contribution
            </a>
        </div>
    </div>

    {# Financial Summary Section #}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Balance</h6>
                    <h3 class="mb-0 amount {% if financial_summary.balance >= 0 %}positive{% else %}negative{% endif %}">
                        {{ financial_summary.balance|format_currency('EUR') }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Unpaid Contributions</h6>
                    <h3 class="mb-0 amount {% if financial_summary.unpaidContributions > 0 %}negative{% else %}positive{% endif %}">
                        {{ financial_summary.unpaidContributions|format_currency('EUR') }}
                    </h3>
                    <small class="text-muted">{{ financial_summary.unpaidContributionsCount }} items</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Unpaid Penalties</h6>
                    <h3 class="mb-0 amount {% if financial_summary.unpaidPenalties > 0 %}negative{% else %}positive{% endif %}">
                        {{ financial_summary.unpaidPenalties|format_currency('EUR') }}
                    </h3>
                    <small class="text-muted">{{ financial_summary.unpaidPenaltiesCount }} items</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Monthly Income</h6>
                    <h3 class="mb-0 amount positive">
                        {{ financial_summary.monthlyIncome|format_currency('EUR') }}
                    </h3>
                    <small class="text-muted">Last 30 days</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        {# Recent Activity Section #}
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Recent Activity
                    </h5>
                    <a href="{{ path('payment_list') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
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
                                                {{ activity.amount|format_currency('EUR') }}
                                            </td>
                                        </tr>
                                    {% endfor %}
                                {% else %}
                                    <tr>
                                        <td colspan="4" class="text-center py-3">No recent activity</td>
                                    </tr>
                                {% endif %}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {# Quick Actions Section #}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ path('contribution_new') }}" class="btn btn-outline-primary">
                            <i class="fas fa-plus-circle me-2"></i>Add Contribution
                        </a>
                        <a href="{{ path('penalty_new') }}" class="btn btn-outline-primary">
                            <i class="fas fa-exclamation-circle me-2"></i>Record Penalty
                        </a>
                        <a href="{{ path('payment_new') }}" class="btn btn-outline-primary">
                            <i class="fas fa-money-bill-wave me-2"></i>Record Payment
                        </a>
                        <a href="{{ path('report_generate') }}" class="btn btn-outline-primary">
                            <i class="fas fa-chart-bar me-2"></i>Generate Report
                        </a>
                    </div>
                </div>
            </div>

            {# Upcoming Payments Section #}
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Upcoming Payments
                    </h5>
                    <a href="{{ path('contribution_list', {'filter': 'upcoming'}) }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        {% if upcoming_payments|length > 0 %}
                            {% for payment in upcoming_payments %}
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-medium">{{ payment.description }}</div>
                                        <small class="text-muted">Due: {{ payment.dueDate|date('d.m.Y') }}</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="amount negative monospace">{{ payment.amount|format_currency('EUR') }}</div>
                                        {% if payment.daysUntilDue <= 3 %}
                                            <span class="badge bg-danger">Due soon</span>
                                        {% elseif payment.daysUntilDue <= 7 %}
                                            <span class="badge bg-warning text-dark">Upcoming</span>
                                        {% endif %}
                                    </div>
                                </li>
                            {% endfor %}
                        {% else %}
                            <li class="list-group-item text-center py-3">No upcoming payments</li>
                        {% endif %}
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {# Team Overview Section (only shown if user is in multiple teams) #}
    {% if user_teams|length > 1 %}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>Team Overview
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Team</th>
                                <th>Members</th>
                                <th>Total Balance</th>
                                <th>Unpaid Contributions</th>
                                <th>Unpaid Penalties</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {% for team in user_teams %}
                                <tr>
                                    <td>
                                        <a href="{{ path('dashboard', {'team': team.id}) }}" class="text-decoration-none">
                                            {{ team.name }}
                                        </a>
                                    </td>
                                    <td>{{ team.memberCount }}</td>
                                    <td class="amount {% if team.balance >= 0 %}positive{% else %}negative{% endif %} monospace">
                                        {{ team.balance|format_currency('EUR') }}
                                    </td>
                                    <td class="amount {% if team.unpaidContributions > 0 %}negative{% else %}positive{% endif %} monospace">
                                        {{ team.unpaidContributions|format_currency('EUR') }}
                                    </td>
                                    <td class="amount {% if team.unpaidPenalties > 0 %}negative{% else %}positive{% endif %} monospace">
                                        {{ team.unpaidPenalties|format_currency('EUR') }}
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ path('dashboard', {'team': team.id}) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                            {% endfor %}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    {% endif %}
{% endblock %}
```

## Dashboard Components

### Financial Summary Cards

The financial summary section provides at-a-glance information about the team's financial status:

1. **Total Balance**: The current balance of the team's account
2. **Unpaid Contributions**: The total amount of unpaid contributions
3. **Unpaid Penalties**: The total amount of unpaid penalties
4. **Monthly Income**: The total income over the last 30 days

Each card uses color coding to indicate positive or negative values, making it easy for users to quickly assess the financial health of their team.

### Recent Activity Table

The recent activity table shows the latest financial transactions, including:

- Contributions added
- Penalties recorded
- Payments made

Each entry includes:
- Date of the transaction
- Type (contribution, penalty, or payment)
- Description
- Amount

The table is limited to the most recent transactions, with a "View All" link to see the complete history.

### Quick Actions

The quick actions section provides buttons for common tasks:

1. **Add Contribution**: Create a new contribution
2. **Record Penalty**: Record a new penalty
3. **Record Payment**: Record a new payment
4. **Generate Report**: Generate a financial report

These actions are the most frequently performed tasks, making them easily accessible from the dashboard.

### Upcoming Payments

The upcoming payments section shows contributions and penalties that are due soon, helping users stay on top of their financial obligations. Each entry includes:

- Description of the payment
- Due date
- Amount
- Status indicator (due soon, upcoming)

### Team Overview (for users in multiple teams)

For users who are members of multiple teams, the team overview section provides a summary of each team's financial status. This allows users to quickly compare the financial health of different teams and navigate between them.

## Mobile Responsiveness

The dashboard is designed to be fully responsive, with a layout that adapts to different screen sizes:

- On large screens, the layout uses a multi-column grid to display all sections side by side
- On medium screens, some sections stack vertically while maintaining readability
- On small screens, all sections stack vertically, with tables becoming scrollable horizontally

## Data Requirements

The dashboard requires the following data from the backend:

1. **User Information**:
   - Current user details
   - Teams the user belongs to
   - Current selected team

2. **Financial Summary**:
   - Total balance
   - Unpaid contributions (amount and count)
   - Unpaid penalties (amount and count)
   - Monthly income

3. **Recent Activity**:
   - List of recent transactions (date, type, description, amount)

4. **Upcoming Payments**:
   - List of upcoming contributions and penalties (description, due date, amount, days until due)

5. **Team Overview** (if applicable):
   - List of teams with financial summaries

## Controller Logic

The dashboard controller will need to:

1. Determine the current user and their teams
2. Determine which team to display (from request parameter or default to first team)
3. Fetch the financial summary for the selected team
4. Fetch recent activity for the selected team
5. Fetch upcoming payments for the selected team
6. If the user belongs to multiple teams, fetch the team overview data

## User Experience Considerations

1. **Loading States**: Show loading indicators while data is being fetched
2. **Empty States**: Display helpful messages when there is no data to show
3. **Error Handling**: Gracefully handle and display errors
4. **Personalization**: Allow users to customize their dashboard view (future enhancement)
5. **Accessibility**: Ensure all dashboard elements are accessible

## Next Steps

With the dashboard designed, we can now proceed to implement the team management pages as described in [04-team-management.md](04-team-management.md).
