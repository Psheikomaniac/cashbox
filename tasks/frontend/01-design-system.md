# Design System & Core Components

## Overview

This document outlines the design system and core components for the Cashbox frontend. A consistent design system is crucial for creating a cohesive user experience across the application. The design system will establish the visual language, component patterns, and interaction models that will be used throughout the frontend.

## Design Principles

1. **Clarity**: Information should be presented clearly and concisely
2. **Consistency**: UI elements should behave predictably across the application
3. **Efficiency**: Users should be able to accomplish tasks with minimal effort
4. **Feedback**: The system should provide clear feedback for all user actions
5. **Accessibility**: The interface should be usable by people with diverse abilities

## Color Scheme

The color scheme is designed to be clean, professional, and accessible, with a focus on financial management.

### Primary Colors

- **Primary Blue** (#1976D2): Used for primary actions, links, and key UI elements
- **Secondary Green** (#2E7D32): Used for success states, confirmations, and positive financial indicators
- **Accent Orange** (#F57C00): Used for warnings, notifications, and attention-grabbing elements

### Neutral Colors

- **Dark Gray** (#333333): Used for primary text
- **Medium Gray** (#757575): Used for secondary text and icons
- **Light Gray** (#E0E0E0): Used for borders and dividers
- **Background Gray** (#F5F5F5): Used for page backgrounds and card backgrounds
- **White** (#FFFFFF): Used for card backgrounds and primary content areas

### Semantic Colors

- **Success** (#4CAF50): Used for success messages and positive states
- **Warning** (#FFC107): Used for warning messages and cautionary states
- **Error** (#F44336): Used for error messages and negative states
- **Info** (#2196F3): Used for informational messages

## Typography

Typography will be clean and readable, with a focus on legibility across devices.

### Font Family

- **Primary Font**: 'Roboto', sans-serif
- **Monospace Font**: 'Roboto Mono', monospace (for code, amounts, and tabular data)

### Font Sizes

- **Heading 1**: 2.5rem (40px)
- **Heading 2**: 2rem (32px)
- **Heading 3**: 1.75rem (28px)
- **Heading 4**: 1.5rem (24px)
- **Heading 5**: 1.25rem (20px)
- **Heading 6**: 1rem (16px)
- **Body Text**: 1rem (16px)
- **Small Text**: 0.875rem (14px)
- **Extra Small Text**: 0.75rem (12px)

### Font Weights

- **Regular**: 400
- **Medium**: 500
- **Bold**: 700

## Spacing System

A consistent spacing system will be used throughout the application to create visual rhythm and hierarchy.

- **Extra Small (xs)**: 0.25rem (4px)
- **Small (sm)**: 0.5rem (8px)
- **Medium (md)**: 1rem (16px)
- **Large (lg)**: 1.5rem (24px)
- **Extra Large (xl)**: 2rem (32px)
- **2x Extra Large (2xl)**: 3rem (48px)

## Core Components

### Base Layout

The base layout will provide the foundation for all pages in the application. It will include:

```twig
{# templates/layouts/default.html.twig #}
{% extends 'base.html.twig' %}

{% block stylesheets %}
    {{ parent() }}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Roboto+Mono&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
{% endblock %}

{% block body %}
    {% include 'components/navigation.html.twig' %}

    <main class="container py-4">
        {% include 'components/alerts.html.twig' %}

        {% block content %}{% endblock %}
    </main>

    {% include 'components/footer.html.twig' %}
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
{% endblock %}
```

### Navigation Component

The navigation component will provide consistent navigation across the application:

```twig
{# templates/components/navigation.html.twig #}
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="{{ path('dashboard') }}">
            <i class="fas fa-money-bill-wave me-2"></i>Cashbox
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {% if app.request.get('_route') starts with 'dashboard' %}active{% endif %}" href="{{ path('dashboard') }}">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {% if app.request.get('_route') starts with 'team' %}active{% endif %}" href="{{ path('team_list') }}">
                        <i class="fas fa-users me-1"></i> Teams
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {% if app.request.get('_route') starts with 'contribution' %}active{% endif %}" href="{{ path('contribution_list') }}">
                        <i class="fas fa-hand-holding-usd me-1"></i> Contributions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {% if app.request.get('_route') starts with 'penalty' %}active{% endif %}" href="{{ path('penalty_list') }}">
                        <i class="fas fa-exclamation-circle me-1"></i> Penalties
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {% if app.request.get('_route') starts with 'payment' %}active{% endif %}" href="{{ path('payment_list') }}">
                        <i class="fas fa-credit-card me-1"></i> Payments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {% if app.request.get('_route') starts with 'report' %}active{% endif %}" href="{{ path('report_list') }}">
                        <i class="fas fa-chart-bar me-1"></i> Reports
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i> {{ app.user.username }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ path('user_profile') }}"><i class="fas fa-user me-1"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="{{ path('notification_preferences') }}"><i class="fas fa-bell me-1"></i> Notifications</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ path('app_logout') }}"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
```

### Card Component

Cards will be used extensively throughout the application to group related content:

```twig
{# templates/components/card.html.twig #}
{% macro render(title, icon = null, footer = null, classes = '') %}
    <div class="card shadow-sm mb-4 {{ classes }}">
        {% if title %}
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    {% if icon %}<i class="fas fa-{{ icon }} me-2"></i>{% endif %}
                    {{ title }}
                </h5>
                {% if block('card_header_actions') is defined %}
                    <div class="card-actions">
                        {{ block('card_header_actions') }}
                    </div>
                {% endif %}
            </div>
        {% endif %}
        <div class="card-body">
            {{ block('card_content') }}
        </div>
        {% if footer or block('card_footer') is defined %}
            <div class="card-footer">
                {% if block('card_footer') is defined %}
                    {{ block('card_footer') }}
                {% else %}
                    {{ footer }}
                {% endif %}
            </div>
        {% endif %}
    </div>
{% endmacro %}
```

### Alert Component

Alerts will provide feedback to users about their actions:

```twig
{# templates/components/alerts.html.twig #}
{% for label, messages in app.flashes %}
    {% for message in messages %}
        <div class="alert alert-{{ label }} alert-dismissible fade show" role="alert">
            {% if label == 'success' %}
                <i class="fas fa-check-circle me-2"></i>
            {% elseif label == 'warning' %}
                <i class="fas fa-exclamation-triangle me-2"></i>
            {% elseif label == 'danger' %}
                <i class="fas fa-times-circle me-2"></i>
            {% else %}
                <i class="fas fa-info-circle me-2"></i>
            {% endif %}
            {{ message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    {% endfor %}
{% endfor %}
```

### Form Components

Forms will be styled consistently throughout the application:

```twig
{# templates/components/forms.html.twig #}

{# Form field macro #}
{% macro field(form_field, label = null, help = null, required = false) %}
    <div class="mb-3">
        {% if label is not null %}
            {{ form_label(form_field, label, {'label_attr': {'class': 'form-label'}}) }}
        {% else %}
            {{ form_label(form_field, null, {'label_attr': {'class': 'form-label'}}) }}
        {% endif %}

        {% if required %}
            <span class="text-danger">*</span>
        {% endif %}

        {{ form_widget(form_field, {'attr': {'class': 'form-control' ~ (form_field.vars.valid ? '' : ' is-invalid')}}) }}

        {% if help %}
            <div class="form-text">{{ help }}</div>
        {% endif %}

        {{ form_errors(form_field, {'attr': {'class': 'invalid-feedback'}}) }}
    </div>
{% endmacro %}

{# Submit button macro #}
{% macro submit_button(text = 'Save', icon = 'save', classes = 'btn-primary') %}
    <button type="submit" class="btn {{ classes }}">
        <i class="fas fa-{{ icon }} me-1"></i> {{ text }}
    </button>
{% endmacro %}

{# Cancel button macro #}
{% macro cancel_button(path, text = 'Cancel', icon = 'times') %}
    <a href="{{ path }}" class="btn btn-secondary">
        <i class="fas fa-{{ icon }} me-1"></i> {{ text }}
    </a>
{% endmacro %}
```

### Table Component

Tables will be used for displaying data in a structured format:

```twig
{# templates/components/tables.html.twig #}
{% macro data_table(headers, empty_message = 'No data available') %}
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    {% for header in headers %}
                        <th>{{ header }}</th>
                    {% endfor %}
                </tr>
            </thead>
            <tbody>
                {% if block('table_rows') is defined and block('table_rows')|trim is not empty %}
                    {{ block('table_rows') }}
                {% else %}
                    <tr>
                        <td colspan="{{ headers|length }}" class="text-center py-3">{{ empty_message }}</td>
                    </tr>
                {% endif %}
            </tbody>
        </table>
    </div>
{% endmacro %}
```

### Pagination Component

Pagination will be used for navigating through large datasets:

```twig
{# templates/components/pagination.html.twig #}
{% macro render(paginator) %}
    {% if paginator.pageCount > 1 %}
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                {% if paginator.hasPreviousPage %}
                    <li class="page-item">
                        <a class="page-link" href="{{ path(app.request.attributes.get('_route'), app.request.query.all|merge({page: 1})) }}">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="{{ path(app.request.attributes.get('_route'), app.request.query.all|merge({page: paginator.previousPage})) }}">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </li>
                {% else %}
                    <li class="page-item disabled">
                        <span class="page-link"><i class="fas fa-angle-double-left"></i></span>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link"><i class="fas fa-angle-left"></i></span>
                    </li>
                {% endif %}

                {% for i in max(1, paginator.currentPage - 2)..min(paginator.pageCount, paginator.currentPage + 2) %}
                    <li class="page-item {{ i == paginator.currentPage ? 'active' : '' }}">
                        <a class="page-link" href="{{ path(app.request.attributes.get('_route'), app.request.query.all|merge({page: i})) }}">{{ i }}</a>
                    </li>
                {% endfor %}

                {% if paginator.hasNextPage %}
                    <li class="page-item">
                        <a class="page-link" href="{{ path(app.request.attributes.get('_route'), app.request.query.all|merge({page: paginator.nextPage})) }}">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="{{ path(app.request.attributes.get('_route'), app.request.query.all|merge({page: paginator.pageCount})) }}">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    </li>
                {% else %}
                    <li class="page-item disabled">
                        <span class="page-link"><i class="fas fa-angle-right"></i></span>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link"><i class="fas fa-angle-double-right"></i></span>
                    </li>
                {% endif %}
            </ul>
        </nav>
    {% endif %}
{% endmacro %}
```

## Custom CSS

A custom CSS file will be created to extend Bootstrap and implement the design system:

```scss
// public/css/app.scss

// Override Bootstrap variables
$primary: #1976D2;
$secondary: #757575;
$success: #2E7D32;
$info: #2196F3;
$warning: #F57C00;
$danger: #F44336;
$light: #F5F5F5;
$dark: #333333;

// Import Bootstrap
// In a real implementation, Bootstrap would be imported using Webpack or Symfony Encore
// @import "bootstrap/scss/bootstrap";

// Typography
body {
  font-family: 'Roboto', sans-serif;
  color: $dark;
  background-color: $light;
}

.monospace {
  font-family: 'Roboto Mono', monospace;
}

// Custom card styles
.card {
  border-radius: 0.5rem;
  border: none;

  .card-header {
    background-color: rgba($primary, 0.05);
    border-bottom: 1px solid rgba($primary, 0.1);
  }

  .card-footer {
    background-color: rgba($light, 0.5);
    border-top: 1px solid rgba($primary, 0.1);
  }
}

// Financial amounts
.amount {
  font-family: 'Roboto Mono', monospace;
  font-weight: 500;

  &.positive {
    color: $success;
  }

  &.negative {
    color: $danger;
  }
}

// Status badges
.status-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;

  &.status-paid {
    background-color: rgba($success, 0.1);
    color: $success;
  }

  &.status-unpaid {
    background-color: rgba($warning, 0.1);
    color: $warning;
  }

  &.status-overdue {
    background-color: rgba($danger, 0.1);
    color: $danger;
  }
}

// Custom navbar
.navbar-dark.bg-primary {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

// Footer
footer {
  border-top: 1px solid rgba($dark, 0.1);
  padding: 1.5rem 0;
  margin-top: 2rem;
}
```

## Next Steps

With the design system and core components defined, we can now proceed to implement the authentication pages as described in [02-authentication-pages.md](02-authentication-pages.md).
