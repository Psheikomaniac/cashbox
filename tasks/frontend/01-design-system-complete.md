# Complete Design System for Cashbox

## Introduction

This document consolidates the design system foundations and implementation guidelines for the Cashbox application. It combines comprehensive design principles with practical implementation details to create a cohesive, modern, and accessible user interface.

## Design Philosophy

Our design philosophy is centered around the following principles:

1. **User-Centered Design**: All design decisions are made with the user's needs and goals in mind
2. **Simplicity**: The interface should be simple and intuitive, reducing cognitive load
3. **Consistency**: UI elements should behave predictably across the application
4. **Feedback**: The system should provide clear feedback for all user actions
5. **Accessibility**: The interface should be usable by people with diverse abilities
6. **Engagement**: The design should encourage users to engage with the application

## Typography

Typography plays a crucial role in creating a clear visual hierarchy and ensuring readability across devices and screen sizes.

### Font Family
- **Primary Font**: 'Roboto', sans-serif
- **Monospace Font**: 'Roboto Mono', monospace (for code, amounts, and tabular data)

### Type Scale
We use a modular type scale with a ratio of 1.25 (major third) to create a harmonious progression of font sizes:

| Level | Size (rem) | Size (px) | Weight | Usage |
|-------|------------|-----------|--------|-------|
| h1    | 3.052      | 48.83     | 700    | Page titles |
| h2    | 2.441      | 39.06     | 700    | Section headings |
| h3    | 1.953      | 31.25     | 600    | Subsection headings |
| h4    | 1.563      | 25.00     | 600    | Card titles |
| h5    | 1.25       | 20.00     | 500    | Component headings |
| h6    | 1          | 16.00     | 500    | Small headings |
| Body  | 1          | 16.00     | 400    | Body text |
| Small | 0.8        | 12.80     | 400    | Captions, labels |

### Implementation in Twig/CSS

```scss
// Typography variables
$font-family-base: 'Roboto', sans-serif;
$font-family-monospace: 'Roboto Mono', monospace;

$font-size-base: 1rem;
$font-size-sm: 0.8rem;
$font-size-lg: 1.25rem;

$font-weight-normal: 400;
$font-weight-medium: 500;
$font-weight-semibold: 600;
$font-weight-bold: 700;

$line-height-tight: 1.2;
$line-height-base: 1.5;
$line-height-sm: 1.4;

// Heading sizes
$h1-font-size: 3.052rem;
$h2-font-size: 2.441rem;
$h3-font-size: 1.953rem;
$h4-font-size: 1.563rem;
$h5-font-size: 1.25rem;
$h6-font-size: 1rem;
```

```twig
{# Example of typography usage in Twig #}
<h1 class="display-1">Page Title</h1>
<h2 class="display-2">Section Heading</h2>
<p class="body-text">Regular body text for content.</p>
<p class="small-text">Smaller text for captions and labels.</p>
<span class="monospace">€1,234.56</span>
```

## Color Palette

Our color palette is designed to be clean, professional, and accessible, with a focus on financial management applications.

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

### Financial Status Colors
- **Positive Balance** (#2E7D32): Green for positive financial indicators
- **Negative Balance** (#F44336): Red for negative financial indicators
- **Pending Status** (#FFC107): Orange/yellow for pending transactions
- **Overdue Status** (#D32F2F): Dark red for overdue payments

### Implementation

```scss
// Color variables
$primary: #1976D2;
$secondary: #2E7D32;
$accent: #F57C00;

$dark: #333333;
$medium: #757575;
$light: #E0E0E0;
$background: #F5F5F5;
$white: #FFFFFF;

$success: #4CAF50;
$warning: #FFC107;
$error: #F44336;
$info: #2196F3;

// Financial status colors
$positive: #2E7D32;
$negative: #F44336;
$pending: #FFC107;
$overdue: #D32F2F;
```

## Spacing System

A consistent spacing system creates visual rhythm and hierarchy throughout the application using a 4px base unit.

### Spacing Scale

| Name | Size (rem) | Size (px) | Usage |
|------|------------|-----------|-------|
| 0    | 0          | 0         | No spacing |
| 1    | 0.25       | 4         | Tiny spacing (between related elements) |
| 2    | 0.5        | 8         | Small spacing (between related elements) |
| 3    | 0.75       | 12        | Small-medium spacing |
| 4    | 1          | 16        | Medium spacing (standard) |
| 5    | 1.5        | 24        | Medium-large spacing |
| 6    | 2          | 32        | Large spacing |
| 7    | 3          | 48        | Extra large spacing |
| 8    | 4          | 64        | Huge spacing |

### Implementation

```scss
// Spacing variables
$spacer: 1rem;
$spacer-0: 0;
$spacer-1: $spacer * 0.25;  // 4px
$spacer-2: $spacer * 0.5;   // 8px
$spacer-3: $spacer * 0.75;  // 12px
$spacer-4: $spacer;         // 16px
$spacer-5: $spacer * 1.5;   // 24px
$spacer-6: $spacer * 2;     // 32px
$spacer-7: $spacer * 3;     // 48px
$spacer-8: $spacer * 4;     // 64px
```

## Responsive Grid System

We use Bootstrap's 12-column grid system for flexible and responsive layouts.

### Breakpoints

| Name | Size (px) | Description |
|------|-----------|-------------|
| xs   | <576      | Extra small devices (portrait phones) |
| sm   | ≥576      | Small devices (landscape phones) |
| md   | ≥768      | Medium devices (tablets) |
| lg   | ≥992      | Large devices (desktops) |
| xl   | ≥1200     | Extra large devices (large desktops) |
| xxl  | ≥1400     | Extra extra large devices |

### Container Widths

| Breakpoint | Container Width |
|------------|----------------|
| xs         | 100%           |
| sm         | 540px          |
| md         | 720px          |
| lg         | 960px          |
| xl         | 1140px         |
| xxl        | 1320px         |

## Component Design System

### Buttons

```scss
// Button styles
.btn {
  border-radius: 0.375rem;
  font-weight: 500;
  transition: all 0.2s ease-in-out;
  
  &.btn-primary {
    background-color: $primary;
    border-color: $primary;
    
    &:hover {
      background-color: darken($primary, 10%);
      border-color: darken($primary, 10%);
    }
  }
}
```

### Cards

```twig
{# Standard card component #}
<div class="card shadow-sm">
  <div class="card-header bg-white">
    <h5 class="card-title mb-0">Card Title</h5>
  </div>
  <div class="card-body">
    <p class="card-text">Card content goes here.</p>
  </div>
  <div class="card-footer bg-white border-0">
    <button type="button" class="btn btn-primary">Action</button>
  </div>
</div>
```

### Forms

```twig
{# Form component with validation #}
<div class="mb-3">
  <label for="email" class="form-label">Email Address</label>
  <input type="email" 
         class="form-control{{ form.email.vars.valid ? '' : ' is-invalid' }}" 
         id="email" 
         name="email" 
         required>
  <div class="form-text">We'll never share your email with anyone else.</div>
  {% if not form.email.vars.valid %}
    <div class="invalid-feedback">
      Please provide a valid email address.
    </div>
  {% endif %}
</div>
```

### Navigation

```twig
{# Primary navigation component #}
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="{{ path('dashboard') }}">
      <i class="fas fa-money-bill-wave me-2"></i>Cashbox
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="{{ path('dashboard') }}">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ path('teams') }}">Teams</a>
        </li>
      </ul>
      
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
            {{ app.user.fullName }}
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ path('profile') }}">Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="{{ path('logout') }}">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
```

## Financial UI Components

### Amount Display

```twig
{# Amount display component #}
{% macro amount(value, currency, class) %}
  <span class="amount {{ class ?: (value >= 0 ? 'positive' : 'negative') }} monospace">
    {{ value|format_currency(currency ?: 'EUR') }}
  </span>
{% endmacro %}
```

### Status Badges

```twig
{# Status badge component #}
{% macro status_badge(status) %}
  {% set badge_class = {
    'paid': 'bg-success',
    'pending': 'bg-warning text-dark',
    'overdue': 'bg-danger',
    'completed': 'bg-success',
    'active': 'bg-primary'
  } %}
  
  <span class="badge {{ badge_class[status] ?: 'bg-secondary' }}">
    {{ status|title }}
  </span>
{% endmacro %}
```

### Progress Indicators

```twig
{# Progress bar for contributions/payments #}
{% macro progress_bar(current, total, type) %}
  {% set percentage = total > 0 ? (current / total * 100) : 0 %}
  {% set bar_class = type == 'success' ? 'bg-success' : (type == 'warning' ? 'bg-warning' : 'bg-primary') %}
  
  <div class="progress" style="height: 8px;">
    <div class="progress-bar {{ bar_class }}" 
         role="progressbar" 
         style="width: {{ percentage }}%" 
         aria-valuenow="{{ percentage }}" 
         aria-valuemin="0" 
         aria-valuemax="100">
    </div>
  </div>
  <small class="text-muted">{{ current }}/{{ total }}</small>
{% endmacro %}
```

## Icons and Imagery

### Icon Usage
- **Font Awesome 6**: Primary icon library for consistency
- **Contextual Icons**: Use meaningful icons that enhance understanding
- **Size Guidelines**: 16px for inline, 24px for buttons, 32px+ for features

### Common Icon Patterns
```twig
{# Icon usage examples #}
<i class="fas fa-money-bill-wave"></i> {# Cashbox brand #}
<i class="fas fa-users"></i> {# Teams #}
<i class="fas fa-hand-holding-usd"></i> {# Contributions #}
<i class="fas fa-exclamation-circle"></i> {# Penalties #}
<i class="fas fa-credit-card"></i> {# Payments #}
<i class="fas fa-chart-bar"></i> {# Reports #}
<i class="fas fa-cog"></i> {# Settings #}
```

## Animation and Transitions

### Timing and Easing
- **Quick Transitions**: 100ms for button hover states
- **Standard Transitions**: 200ms for most UI elements
- **Emphasized Transitions**: 300ms for modals and page transitions
- **Complex Animations**: 500ms for charts and illustrations

### Implementation
```scss
// Animation variables
$transition-quick: 100ms;
$transition-standard: 200ms;
$transition-emphasized: 300ms;
$transition-complex: 500ms;

// Utility classes
.transition-quick { transition: all $transition-quick ease-in-out; }
.transition-standard { transition: all $transition-standard ease-in-out; }
.transition-emphasized { transition: all $transition-emphasized ease-in-out; }
```

## Accessibility Guidelines

### Key Requirements
1. **Color Contrast**: Minimum 4.5:1 ratio for normal text, 3:1 for large text
2. **Keyboard Navigation**: All interactive elements accessible via keyboard
3. **Screen Reader Support**: Semantic HTML and appropriate ARIA attributes
4. **Focus Indicators**: Visible focus indicators for keyboard navigation
5. **Alternative Text**: Descriptive alt text for all images
6. **Form Labels**: Proper labels associated with all form controls

### Implementation Examples
```twig
{# Accessible form field #}
<div class="mb-3">
  <label for="amount" class="form-label">Amount</label>
  <input type="number" 
         class="form-control" 
         id="amount" 
         name="amount"
         aria-describedby="amountHelp"
         required>
  <div id="amountHelp" class="form-text">Enter the contribution amount in EUR.</div>
</div>

{# Accessible button #}
<button type="button" 
        class="btn btn-primary" 
        aria-label="Save contribution"
        data-bs-toggle="tooltip" 
        data-bs-placement="top" 
        title="Save and create contribution">
  <i class="fas fa-save"></i> Save
</button>
```

## Dark Mode Support

### CSS Custom Properties
```scss
:root {
  --bs-body-bg: #{$white};
  --bs-body-color: #{$dark};
  --bs-card-bg: #{$white};
  --bs-border-color: #{$light};
}

[data-bs-theme="dark"] {
  --bs-body-bg: #212529;
  --bs-body-color: #dee2e6;
  --bs-card-bg: #343a40;
  --bs-border-color: #495057;
}
```

### Theme Toggle Implementation
```twig
{# Theme toggle button #}
<button type="button" 
        class="btn btn-outline-secondary" 
        id="themeToggle"
        aria-label="Toggle theme">
  <i class="fas fa-moon"></i>
</button>

<script>
document.getElementById('themeToggle').addEventListener('click', function() {
  const currentTheme = document.documentElement.getAttribute('data-bs-theme');
  const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-bs-theme', newTheme);
  localStorage.setItem('theme', newTheme);
});
</script>
```

## Component Library Implementation

### Base Layout Template
```twig
{# templates/layouts/default.html.twig #}
{% extends 'base.html.twig' %}

{% block body %}
  {% include 'components/navigation/header.html.twig' %}
  
  <main class="main-content">
    <div class="container-fluid">
      {% include 'components/alerts.html.twig' %}
      {% block content %}{% endblock %}
    </div>
  </main>
  
  {% include 'components/navigation/footer.html.twig' %}
{% endblock %}
```

### Reusable Card Component
```twig
{# templates/components/cards/base-card.html.twig #}
<div class="card {{ card_class|default('shadow-sm') }}">
  {% if card_title or card_actions %}
    <div class="card-header {{ header_class|default('bg-white') }}">
      {% if card_title %}
        <h5 class="card-title mb-0">{{ card_title }}</h5>
      {% endif %}
      {% if card_actions %}
        <div class="card-actions">
          {{ card_actions|raw }}
        </div>
      {% endif %}
    </div>
  {% endif %}
  
  <div class="card-body {{ body_class|default('') }}">
    {{ card_content|raw }}
  </div>
  
  {% if card_footer %}
    <div class="card-footer {{ footer_class|default('bg-white border-0') }}">
      {{ card_footer|raw }}
    </div>
  {% endif %}
</div>
```

## Next Steps

This comprehensive design system provides the foundation for implementing a consistent, accessible, and user-friendly interface for the Cashbox application. The next steps involve:

1. **Component Implementation**: Build the complete component library
2. **Page Templates**: Create specific page layouts using these components
3. **Testing**: Ensure accessibility and cross-browser compatibility
4. **Documentation**: Maintain up-to-date component documentation
5. **Iteration**: Continuously improve based on user feedback and usage patterns

The design system should be treated as a living document that evolves with the application while maintaining consistency and usability standards.