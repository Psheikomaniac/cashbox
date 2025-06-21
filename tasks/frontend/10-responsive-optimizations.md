# Responsive Optimizations

## Overview

This document outlines the responsive design optimizations for the Cashbox application, ensuring an optimal user experience across all devices and screen sizes. The implementation will follow a mobile-first approach with progressive enhancement for larger screens.

## Mobile-First Strategy

### Design Principles
1. **Content Priority**: Most important content and actions are prioritized on smaller screens
2. **Touch-Friendly**: All interactive elements have appropriate touch targets (minimum 44px)
3. **Performance**: Optimized loading and rendering for mobile devices
4. **Accessibility**: Maintained accessibility standards across all screen sizes
5. **Progressive Enhancement**: Enhanced features for larger screens

### Breakpoint Strategy

#### Breakpoints
```scss
// Breakpoint variables
$breakpoint-xs: 0;
$breakpoint-sm: 576px;
$breakpoint-md: 768px;
$breakpoint-lg: 992px;
$breakpoint-xl: 1200px;
$breakpoint-xxl: 1400px;

// Media query mixins
@mixin media-sm-up {
  @media (min-width: #{$breakpoint-sm}) {
    @content;
  }
}

@mixin media-md-up {
  @media (min-width: #{$breakpoint-md}) {
    @content;
  }
}

@mixin media-lg-up {
  @media (min-width: #{$breakpoint-lg}) {
    @content;
  }
}

@mixin media-xl-up {
  @media (min-width: #{$breakpoint-xl}) {
    @content;
  }
}
```

## Layout Optimizations

### Navigation
```twig
{# templates/components/navigation/responsive-nav.html.twig #}
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="{{ path('dashboard') }}">
      <i class="fas fa-money-bill-wave me-2"></i>
      <span class="d-none d-sm-inline">Cashbox</span>
    </a>
    
    <!-- Mobile menu toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="{{ path('dashboard') }}">
            <i class="fas fa-tachometer-alt me-1 d-lg-none"></i>
            <span>Dashboard</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ path('teams') }}">
            <i class="fas fa-users me-1 d-lg-none"></i>
            <span>Teams</span>
          </a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="financialDropdown" role="button" data-bs-toggle="dropdown">
            <i class="fas fa-money-bill-wave me-1 d-lg-none"></i>
            <span>Financial</span>
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ path('contributions') }}">Contributions</a></li>
            <li><a class="dropdown-item" href="{{ path('penalties') }}">Penalties</a></li>
            <li><a class="dropdown-item" href="{{ path('payments') }}">Payments</a></li>
          </ul>
        </li>
      </ul>
      
      <!-- User menu -->
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
            <div class="d-flex align-items-center">
              <div class="avatar-circle bg-light text-primary me-2 d-none d-sm-block" style="width: 32px; height: 32px;">
                {{ app.user.initials }}
              </div>
              <span class="d-none d-md-inline">{{ app.user.firstName }}</span>
              <span class="d-md-none">{{ app.user.initials }}</span>
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="{{ path('profile') }}">Profile</a></li>
            <li><a class="dropdown-item" href="{{ path('settings') }}">Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="{{ path('logout') }}">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
```

### Card Layouts
```scss
// Responsive card layouts
.card-grid {
  .card {
    margin-bottom: 1rem;
    
    @include media-sm-up {
      margin-bottom: 1.5rem;
    }
    
    @include media-lg-up {
      margin-bottom: 2rem;
    }
  }
}

// Financial summary cards
.financial-summary {
  .col {
    margin-bottom: 1rem;
    
    @include media-md-up {
      margin-bottom: 0;
    }
  }
  
  .card-body {
    padding: 1rem;
    text-align: center;
    
    @include media-sm-up {
      padding: 1.5rem;
    }
  }
}
```

## Table Optimizations

### Responsive Tables
```twig
{# templates/components/tables/responsive-table.html.twig #}
<div class="table-responsive">
  <table class="table table-hover">
    <thead class="table-light d-none d-md-table-header-group">
      <tr>
        <th>{{ header1 }}</th>
        <th>{{ header2 }}</th>
        <th>{{ header3 }}</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      {% for item in items %}
        <tr class="d-md-table-row">
          <!-- Desktop view -->
          <td class="d-none d-md-table-cell">{{ item.column1 }}</td>
          <td class="d-none d-md-table-cell">{{ item.column2 }}</td>
          <td class="d-none d-md-table-cell">{{ item.column3 }}</td>
          <td class="d-none d-md-table-cell text-end">
            <div class="btn-group btn-group-sm">
              <a href="#" class="btn btn-outline-secondary">
                <i class="fas fa-eye"></i>
              </a>
              <a href="#" class="btn btn-outline-primary">
                <i class="fas fa-edit"></i>
              </a>
            </div>
          </td>
          
          <!-- Mobile card view -->
          <td class="d-md-none" colspan="4">
            <div class="mobile-card-item">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="mb-0">{{ item.title }}</h6>
                <div class="btn-group btn-group-sm">
                  <a href="#" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="#" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-edit"></i>
                  </a>
                </div>
              </div>
              <div class="row">
                <div class="col-6">
                  <small class="text-muted">{{ header2 }}</small>
                  <div>{{ item.column2 }}</div>
                </div>
                <div class="col-6">
                  <small class="text-muted">{{ header3 }}</small>
                  <div>{{ item.column3 }}</div>
                </div>
              </div>
            </div>
          </td>
        </tr>
      {% endfor %}
    </tbody>
  </table>
</div>
```

### Mobile Card Styling
```scss
.mobile-card-item {
  padding: 1rem;
  border: 1px solid rgba(0, 0, 0, 0.125);
  border-radius: 0.375rem;
  background-color: #fff;
  margin-bottom: 0.5rem;
  
  &:last-child {
    margin-bottom: 0;
  }
  
  .row {
    margin-top: 0.5rem;
    
    .col-6 {
      small {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }
    }
  }
}
```

## Form Optimizations

### Responsive Forms
```twig
{# templates/components/forms/responsive-form.html.twig #}
<form class="responsive-form">
  <div class="row">
    <div class="col-12 col-md-6 mb-3">
      <label for="input1" class="form-label">Field 1</label>
      <input type="text" class="form-control form-control-lg" id="input1">
    </div>
    <div class="col-12 col-md-6 mb-3">
      <label for="input2" class="form-label">Field 2</label>
      <input type="text" class="form-control form-control-lg" id="input2">
    </div>
  </div>
  
  <div class="mb-3">
    <label for="textarea1" class="form-label">Description</label>
    <textarea class="form-control" id="textarea1" rows="4"></textarea>
  </div>
  
  <!-- Mobile-optimized button layout -->
  <div class="d-grid d-md-flex justify-content-md-end gap-2">
    <button type="button" class="btn btn-outline-secondary">Cancel</button>
    <button type="submit" class="btn btn-primary">Save</button>
  </div>
</form>
```

### Form Styling
```scss
.responsive-form {
  // Larger form controls on touch devices
  @media (max-width: 767.98px) {
    .form-control {
      min-height: 48px;
      font-size: 1rem;
    }
    
    .btn {
      min-height: 48px;
      font-size: 1rem;
    }
  }
  
  // Improved spacing on mobile
  .form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    
    @include media-sm-up {
      margin-bottom: 0.5rem;
    }
  }
}
```

## Dashboard Optimizations

### Mobile Dashboard Layout
```twig
{# templates/pages/dashboard/mobile-optimized.html.twig #}
{% extends 'layouts/default.html.twig' %}

{% block content %}
<!-- Mobile header with team selector -->
<div class="d-flex justify-content-between align-items-center mb-3 d-md-none">
  <h1 class="h4 mb-0">Dashboard</h1>
  {% if user_teams|length > 1 %}
    <div class="dropdown">
      <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
        {{ current_team.name }}
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        {% for team in user_teams %}
          <li><a class="dropdown-item" href="{{ path('dashboard', {'team': team.id}) }}">{{ team.name }}</a></li>
        {% endfor %}
      </ul>
    </div>
  {% endif %}
</div>

<!-- Desktop header -->
<div class="d-none d-md-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 mb-0">Dashboard</h1>
  <div class="d-flex gap-2">
    {% if user_teams|length > 1 %}
      <div class="dropdown">
        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
          <i class="fas fa-users me-1"></i> {{ current_team.name }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          {% for team in user_teams %}
            <li><a class="dropdown-item" href="{{ path('dashboard', {'team': team.id}) }}">{{ team.name }}</a></li>
          {% endfor %}
        </ul>
      </div>
    {% endif %}
    <a href="{{ path('contribution_new') }}" class="btn btn-primary">
      <i class="fas fa-plus me-1"></i> New Contribution
    </a>
  </div>
</div>

<!-- Financial Summary - Mobile Stack -->
<div class="row mb-4">
  <div class="col-6 col-lg-3 mb-3">
    <div class="card h-100 border-0 bg-light">
      <div class="card-body text-center p-3">
        <h6 class="text-muted mb-1 small">Balance</h6>
        <h5 class="mb-0 amount {{ financial_summary.balance >= 0 ? 'positive' : 'negative' }}">
          {{ financial_summary.balance|format_currency('EUR') }}
        </h5>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3 mb-3">
    <div class="card h-100 border-0 bg-light">
      <div class="card-body text-center p-3">
        <h6 class="text-muted mb-1 small">Contributions</h6>
        <h5 class="mb-0 amount negative">
          {{ financial_summary.unpaidContributions|format_currency('EUR') }}
        </h5>
        <small class="text-muted d-none d-md-block">{{ financial_summary.unpaidContributionsCount }} items</small>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3 mb-3">
    <div class="card h-100 border-0 bg-light">
      <div class="card-body text-center p-3">
        <h6 class="text-muted mb-1 small">Penalties</h6>
        <h5 class="mb-0 amount negative">
          {{ financial_summary.unpaidPenalties|format_currency('EUR') }}
        </h5>
        <small class="text-muted d-none d-md-block">{{ financial_summary.unpaidPenaltiesCount }} items</small>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3 mb-3">
    <div class="card h-100 border-0 bg-light">
      <div class="card-body text-center p-3">
        <h6 class="text-muted mb-1 small">Income</h6>
        <h5 class="mb-0 amount positive">
          {{ financial_summary.monthlyIncome|format_currency('EUR') }}
        </h5>
        <small class="text-muted d-none d-md-block">30 days</small>
      </div>
    </div>
  </div>
</div>

<!-- Mobile Quick Actions -->
<div class="d-md-none mb-4">
  <div class="row">
    <div class="col-6 mb-2">
      <a href="{{ path('contribution_new') }}" class="btn btn-outline-primary w-100">
        <i class="fas fa-plus-circle d-block mb-1"></i>
        <small>Add Contribution</small>
      </a>
    </div>
    <div class="col-6 mb-2">
      <a href="{{ path('penalty_new') }}" class="btn btn-outline-warning w-100">
        <i class="fas fa-exclamation-circle d-block mb-1"></i>
        <small>Record Penalty</small>
      </a>
    </div>
    <div class="col-6">
      <a href="{{ path('payment_new') }}" class="btn btn-outline-success w-100">
        <i class="fas fa-money-bill-wave d-block mb-1"></i>
        <small>Record Payment</small>
      </a>
    </div>
    <div class="col-6">
      <a href="{{ path('report_generate') }}" class="btn btn-outline-info w-100">
        <i class="fas fa-chart-bar d-block mb-1"></i>
        <small>Generate Report</small>
      </a>
    </div>
  </div>
</div>

<!-- Rest of dashboard content with responsive layout -->
<div class="row">
  <div class="col-12 col-lg-8 mb-4">
    <!-- Recent Activity -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Recent Activity</h5>
        <a href="{{ path('payment_list') }}" class="btn btn-sm btn-outline-primary">
          <span class="d-none d-md-inline">View All</span>
          <i class="fas fa-arrow-right d-md-none"></i>
        </a>
      </div>
      <div class="card-body p-0">
        <!-- Mobile optimized activity list -->
        <div class="d-md-none">
          {% for activity in recent_activity|slice(0, 5) %}
            <div class="p-3 border-bottom">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="fw-medium">{{ activity.description }}</div>
                  <small class="text-muted">{{ activity.date|date('M d, Y') }}</small>
                </div>
                <div class="text-end">
                  <span class="badge bg-{{ activity.type == 'payment' ? 'success' : (activity.type == 'penalty' ? 'warning' : 'primary') }}">
                    {{ activity.type|title }}
                  </span>
                  <div class="amount {{ activity.type == 'payment' ? 'positive' : 'negative' }} monospace small">
                    {{ activity.amount|format_currency('EUR') }}
                  </div>
                </div>
              </div>
            </div>
          {% endfor %}
        </div>
        
        <!-- Desktop table view -->
        <div class="d-none d-md-block">
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
                {% for activity in recent_activity %}
                  <tr>
                    <td>{{ activity.date|date('d.m.Y') }}</td>
                    <td>
                      <span class="badge bg-{{ activity.type == 'payment' ? 'success' : (activity.type == 'penalty' ? 'warning' : 'primary') }}">
                        {{ activity.type|title }}
                      </span>
                    </td>
                    <td>{{ activity.description }}</td>
                    <td class="text-end amount {{ activity.type == 'payment' ? 'positive' : 'negative' }} monospace">
                      {{ activity.amount|format_currency('EUR') }}
                    </td>
                  </tr>
                {% endfor %}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-12 col-lg-4">
    <!-- Desktop Quick Actions -->
    <div class="card mb-4 d-none d-md-block">
      <div class="card-header">
        <h5 class="card-title mb-0">Quick Actions</h5>
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
    
    <!-- Upcoming Payments -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Upcoming</h5>
        <a href="{{ path('contribution_list', {'filter': 'upcoming'}) }}" class="btn btn-sm btn-outline-primary">
          <span class="d-none d-md-inline">View All</span>
          <i class="fas fa-arrow-right d-md-none"></i>
        </a>
      </div>
      <div class="card-body p-0">
        {% if upcoming_payments|length > 0 %}
          {% for payment in upcoming_payments %}
            <div class="p-3 border-bottom">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <div class="fw-medium">{{ payment.description }}</div>
                  <small class="text-muted">Due: {{ payment.dueDate|date('M d') }}</small>
                </div>
                <div class="text-end ms-2">
                  <div class="amount negative monospace">{{ payment.amount|format_currency('EUR') }}</div>
                  {% if payment.daysUntilDue <= 3 %}
                    <span class="badge bg-danger small">Due soon</span>
                  {% endif %}
                </div>
              </div>
            </div>
          {% endfor %}
        {% else %}
          <div class="p-3 text-center text-muted">
            <i class="fas fa-calendar-check mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
            <div>No upcoming payments</div>
          </div>
        {% endif %}
      </div>
    </div>
  </div>
</div>
{% endblock %}
```

## Performance Optimizations

### Image Optimization
```scss
// Responsive images
.responsive-img {
  max-width: 100%;
  height: auto;
  
  &.lazy {
    opacity: 0;
    transition: opacity 0.3s;
    
    &.loaded {
      opacity: 1;
    }
  }
}

// Avatar optimization
.avatar-circle {
  width: 32px;
  height: 32px;
  
  @include media-sm-up {
    width: 40px;
    height: 40px;
  }
  
  @include media-lg-up {
    width: 48px;
    height: 48px;
  }
}
```

### JavaScript Optimizations
```javascript
// Responsive JavaScript utilities
class ResponsiveHelper {
  constructor() {
    this.breakpoints = {
      sm: 576,
      md: 768,
      lg: 992,
      xl: 1200,
      xxl: 1400
    };
    
    this.currentBreakpoint = this.getCurrentBreakpoint();
    this.setupResizeListener();
  }
  
  getCurrentBreakpoint() {
    const width = window.innerWidth;
    if (width >= this.breakpoints.xxl) return 'xxl';
    if (width >= this.breakpoints.xl) return 'xl';
    if (width >= this.breakpoints.lg) return 'lg';
    if (width >= this.breakpoints.md) return 'md';
    if (width >= this.breakpoints.sm) return 'sm';
    return 'xs';
  }
  
  isMobile() {
    return this.currentBreakpoint === 'xs' || this.currentBreakpoint === 'sm';
  }
  
  isTablet() {
    return this.currentBreakpoint === 'md';
  }
  
  isDesktop() {
    return this.currentBreakpoint === 'lg' || this.currentBreakpoint === 'xl' || this.currentBreakpoint === 'xxl';
  }
  
  setupResizeListener() {
    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        const newBreakpoint = this.getCurrentBreakpoint();
        if (newBreakpoint !== this.currentBreakpoint) {
          this.currentBreakpoint = newBreakpoint;
          this.onBreakpointChange(newBreakpoint);
        }
      }, 150);
    });
  }
  
  onBreakpointChange(breakpoint) {
    // Trigger custom events for breakpoint changes
    document.dispatchEvent(new CustomEvent('breakpointChange', {
      detail: { breakpoint }
    }));
  }
}

// Initialize responsive helper
const responsive = new ResponsiveHelper();

// Example usage for conditional behavior
document.addEventListener('breakpointChange', (e) => {
  const { breakpoint } = e.detail;
  
  // Adjust table behavior
  if (breakpoint === 'xs' || breakpoint === 'sm') {
    // Switch to mobile card view
    document.querySelectorAll('.table-responsive').forEach(table => {
      table.classList.add('mobile-view');
    });
  } else {
    // Switch to desktop table view
    document.querySelectorAll('.table-responsive').forEach(table => {
      table.classList.remove('mobile-view');
    });
  }
});
```

## Touch Optimizations

### Touch-Friendly Interactions
```scss
// Touch-friendly button sizing
@media (max-width: 767.98px) {
  .btn {
    min-height: 44px;
    min-width: 44px;
    
    &.btn-sm {
      min-height: 36px;
      min-width: 36px;
    }
    
    &.btn-lg {
      min-height: 52px;
      min-width: 52px;
    }
  }
  
  // Touch-friendly form controls
  .form-control,
  .form-select {
    min-height: 44px;
    font-size: 16px; // Prevents zoom on iOS
  }
  
  // Touch-friendly checkboxes and radios
  .form-check-input {
    width: 1.25rem;
    height: 1.25rem;
  }
  
  // Touch-friendly pagination
  .pagination .page-link {
    min-width: 44px;
    min-height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
}
```

### Swipe Gestures
```javascript
// Simple swipe gesture detection
class SwipeHandler {
  constructor(element, options = {}) {
    this.element = element;
    this.options = {
      threshold: 50,
      timeout: 500,
      ...options
    };
    
    this.startX = 0;
    this.startY = 0;
    this.startTime = 0;
    
    this.bindEvents();
  }
  
  bindEvents() {
    this.element.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: true });
    this.element.addEventListener('touchend', this.handleTouchEnd.bind(this), { passive: true });
  }
  
  handleTouchStart(e) {
    const touch = e.touches[0];
    this.startX = touch.clientX;
    this.startY = touch.clientY;
    this.startTime = Date.now();
  }
  
  handleTouchEnd(e) {
    const touch = e.changedTouches[0];
    const endX = touch.clientX;
    const endY = touch.clientY;
    const endTime = Date.now();
    
    const deltaX = endX - this.startX;
    const deltaY = endY - this.startY;
    const deltaTime = endTime - this.startTime;
    
    if (deltaTime > this.options.timeout) return;
    
    const absX = Math.abs(deltaX);
    const absY = Math.abs(deltaY);
    
    if (absX > this.options.threshold && absX > absY) {
      const direction = deltaX > 0 ? 'right' : 'left';
      this.element.dispatchEvent(new CustomEvent('swipe', {
        detail: { direction, deltaX, deltaY }
      }));
    }
  }
}

// Usage example for table pagination
document.querySelectorAll('.table-responsive').forEach(table => {
  new SwipeHandler(table);
  
  table.addEventListener('swipe', (e) => {
    const { direction } = e.detail;
    const pagination = table.closest('.card').querySelector('.pagination');
    
    if (!pagination) return;
    
    if (direction === 'left') {
      const nextLink = pagination.querySelector('.page-item:not(.disabled) .page-link[aria-label="Next"]');
      if (nextLink) nextLink.click();
    } else if (direction === 'right') {
      const prevLink = pagination.querySelector('.page-item:not(.disabled) .page-link[aria-label="Previous"]');
      if (prevLink) prevLink.click();
    }
  });
});
```

## Accessibility Enhancements

### Screen Reader Optimizations
```twig
{# Screen reader friendly responsive content #}
<div class="financial-summary" role="region" aria-labelledby="financial-summary-title">
  <h2 id="financial-summary-title" class="visually-hidden">Financial Summary</h2>
  
  <div class="row">
    <div class="col-6 col-lg-3">
      <div class="card" role="article" aria-labelledby="balance-title">
        <div class="card-body">
          <h3 id="balance-title" class="h6 text-muted mb-1">Current Balance</h3>
          <div class="h5 mb-0" aria-describedby="balance-description">
            {{ financial_summary.balance|format_currency('EUR') }}
          </div>
          <div id="balance-description" class="visually-hidden">
            Your current account balance is {{ financial_summary.balance >= 0 ? 'positive' : 'negative' }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
```

### Focus Management
```scss
// Enhanced focus indicators for mobile
@media (max-width: 767.98px) {
  .btn:focus,
  .form-control:focus,
  .form-select:focus {
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
    outline: 2px solid transparent;
  }
  
  // Larger focus areas for touch devices
  .nav-link:focus {
    outline: 2px solid #0d6efd;
    outline-offset: 2px;
  }
}
```

## Testing and Validation

### Responsive Testing Checklist
- [ ] Test on actual devices (iOS, Android)
- [ ] Verify touch targets are at least 44px
- [ ] Check horizontal scrolling behavior
- [ ] Validate table responsive behavior
- [ ] Test form usability on mobile
- [ ] Verify navigation collapse/expand
- [ ] Check image scaling and loading
- [ ] Test swipe gestures where applicable
- [ ] Validate accessibility with screen readers
- [ ] Check performance on slower connections

### Performance Metrics
- First Contentful Paint (FCP): < 1.5s on 3G
- Largest Contentful Paint (LCP): < 2.5s on 3G
- Cumulative Layout Shift (CLS): < 0.1
- First Input Delay (FID): < 100ms

## Conclusion

These responsive optimizations ensure that the Cashbox application provides an excellent user experience across all devices and screen sizes. The mobile-first approach, combined with progressive enhancement and touch-friendly interactions, creates a modern and accessible interface that works well for all users.

The implementation maintains the application's functionality while adapting the interface to the constraints and capabilities of different devices, ensuring that users can effectively manage their team finances regardless of how they access the application.