# Authentication Pages

## Overview

This document outlines the design and implementation of the authentication pages for the Cashbox application. These pages are the entry point for users and set the tone for the entire application experience. The authentication system will be secure, user-friendly, and aligned with the design system established in [01-design-system.md](01-design-system.md).

## Authentication Flow

The authentication flow consists of the following steps:

1. **Login**: Users enter their credentials to access the application
2. **Registration**: New users create an account
3. **Password Reset**: Users who have forgotten their password can reset it
4. **Email Verification**: New users verify their email address (optional)

## Login Page

The login page will be clean, focused, and provide clear feedback to users.

### Design

```twig
{# templates/pages/auth/login.html.twig #}
{% extends 'layouts/auth.html.twig' %}

{% block title %}Login | Cashbox{% endblock %}

{% block content %}
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0">
                    <i class="fas fa-money-bill-wave me-2"></i>Cashbox
                </h4>
            </div>
            <div class="card-body p-4">
                <h5 class="card-title text-center mb-4">Sign In to Your Account</h5>

                {% if error %}
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ error.messageKey|trans(error.messageData, 'security') }}
                    </div>
                {% endif %}

                <form method="post">
                    {% if csrf_token is defined %}
                        <input type="hidden" name="_csrf_token" value="{{ csrf_token('authenticate') }}">
                    {% endif %}

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" id="email" name="email" class="form-control" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" id="remember_me" name="_remember_me" class="form-check-input">
                        <label class="form-check-label" for="remember_me">Remember me</label>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-light py-3 text-center">
                <div class="mb-2">
                    <a href="{{ path('app_forgot_password_request') }}" class="text-decoration-none">
                        <i class="fas fa-key me-1"></i>Forgot your password?
                    </a>
                </div>
                <div>
                    <span>Don't have an account?</span>
                    <a href="{{ path('app_register') }}" class="text-decoration-none ms-1">
                        <i class="fas fa-user-plus me-1"></i>Register
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
```

### Features

- Clean, focused design with minimal distractions
- Clear error messages for invalid credentials
- "Remember me" functionality
- Links to password reset and registration
- CSRF protection
- Responsive design that works on all devices

## Registration Page

The registration page will allow new users to create an account with minimal friction.

### Design

```twig
{# templates/pages/auth/register.html.twig #}
{% extends 'layouts/auth.html.twig' %}

{% block title %}Register | Cashbox{% endblock %}

{% block content %}
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0">
                    <i class="fas fa-money-bill-wave me-2"></i>Cashbox
                </h4>
            </div>
            <div class="card-body p-4">
                <h5 class="card-title text-center mb-4">Create Your Account</h5>

                {{ form_start(registrationForm) }}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            {{ form_label(registrationForm.firstName, 'First Name', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(registrationForm.firstName, {'attr': {'class': 'form-control' ~ (registrationForm.firstName.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(registrationForm.firstName, {'attr': {'class': 'invalid-feedback'}}) }}
                        </div>
                        <div class="col-md-6 mb-3">
                            {{ form_label(registrationForm.lastName, 'Last Name', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(registrationForm.lastName, {'attr': {'class': 'form-control' ~ (registrationForm.lastName.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_errors(registrationForm.lastName, {'attr': {'class': 'invalid-feedback'}}) }}
                        </div>
                    </div>

                    <div class="mb-3">
                        {{ form_label(registrationForm.email, 'Email', {'label_attr': {'class': 'form-label'}}) }}
                        {{ form_widget(registrationForm.email, {'attr': {'class': 'form-control' ~ (registrationForm.email.vars.valid ? '' : ' is-invalid')}}) }}
                        {{ form_errors(registrationForm.email, {'attr': {'class': 'invalid-feedback'}}) }}
                    </div>

                    <div class="mb-3">
                        {{ form_label(registrationForm.plainPassword, 'Password', {'label_attr': {'class': 'form-label'}}) }}
                        {{ form_widget(registrationForm.plainPassword, {'attr': {'class': 'form-control' ~ (registrationForm.plainPassword.vars.valid ? '' : ' is-invalid')}}) }}
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.
                        </div>
                        {{ form_errors(registrationForm.plainPassword, {'attr': {'class': 'invalid-feedback'}}) }}
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            {{ form_widget(registrationForm.agreeTerms, {'attr': {'class': 'form-check-input' ~ (registrationForm.agreeTerms.vars.valid ? '' : ' is-invalid')}}) }}
                            {{ form_label(registrationForm.agreeTerms, 'I agree to the Terms of Service and Privacy Policy', {'label_attr': {'class': 'form-check-label'}}) }}
                            {{ form_errors(registrationForm.agreeTerms, {'attr': {'class': 'invalid-feedback'}}) }}
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </button>
                    </div>
                {{ form_end(registrationForm) }}
            </div>
            <div class="card-footer bg-light py-3 text-center">
                <span>Already have an account?</span>
                <a href="{{ path('app_login') }}" class="text-decoration-none ms-1">
                    <i class="fas fa-sign-in-alt me-1"></i>Sign In
                </a>
            </div>
        </div>
    </div>
</div>
{% endblock %}
```

### Features

- Clean form layout with appropriate validation
- Password strength requirements with clear guidance
- Terms of service agreement
- Responsive design for all devices
- Clear error messages for validation failures
- Link back to login page

## Password Reset

The password reset flow will consist of two pages: a request page and a reset page.

### Password Reset Request

```twig
{# templates/pages/auth/forgot_password_request.html.twig #}
{% extends 'layouts/auth.html.twig' %}

{% block title %}Reset Password | Cashbox{% endblock %}

{% block content %}
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0">
                    <i class="fas fa-money-bill-wave me-2"></i>Cashbox
                </h4>
            </div>
            <div class="card-body p-4">
                <h5 class="card-title text-center mb-4">Reset Your Password</h5>

                {% for flash_error in app.flashes('reset_password_error') %}
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ flash_error }}
                    </div>
                {% endfor %}

                <p class="text-muted mb-4">
                    Enter your email address and we'll send you a link to reset your password.
                </p>

                {{ form_start(requestForm) }}
                    <div class="mb-3">
                        {{ form_label(requestForm.email, 'Email', {'label_attr': {'class': 'form-label'}}) }}
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            {{ form_widget(requestForm.email, {'attr': {'class': 'form-control' ~ (requestForm.email.vars.valid ? '' : ' is-invalid')}}) }}
                        </div>
                        {{ form_errors(requestForm.email, {'attr': {'class': 'invalid-feedback'}}) }}
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                        </button>
                    </div>
                {{ form_end(requestForm) }}
            </div>
            <div class="card-footer bg-light py-3 text-center">
                <a href="{{ path('app_login') }}" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i>Back to Login
                </a>
            </div>
        </div>
    </div>
</div>
{% endblock %}
```

### Password Reset Form

```twig
{# templates/pages/auth/reset_password.html.twig #}
{% extends 'layouts/auth.html.twig' %}

{% block title %}Reset Password | Cashbox{% endblock %}

{% block content %}
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0">
                    <i class="fas fa-money-bill-wave me-2"></i>Cashbox
                </h4>
            </div>
            <div class="card-body p-4">
                <h5 class="card-title text-center mb-4">Set New Password</h5>

                {{ form_start(resetForm) }}
                    <div class="mb-3">
                        {{ form_label(resetForm.plainPassword, 'New Password', {'label_attr': {'class': 'form-label'}}) }}
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            {{ form_widget(resetForm.plainPassword, {'attr': {'class': 'form-control' ~ (resetForm.plainPassword.vars.valid ? '' : ' is-invalid')}}) }}
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.
                        </div>
                        {{ form_errors(resetForm.plainPassword, {'attr': {'class': 'invalid-feedback'}}) }}
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key me-2"></i>Reset Password
                        </button>
                    </div>
                {{ form_end(resetForm) }}
            </div>
        </div>
    </div>
</div>
{% endblock %}
```

### Features

- Clear instructions for the password reset process
- Email validation
- Secure token-based reset mechanism
- Password strength requirements
- Responsive design

## Authentication Layout

A special layout will be created for authentication pages to provide a consistent experience:

```twig
{# templates/layouts/auth.html.twig #}
{% extends 'base.html.twig' %}

{% block stylesheets %}
    {{ parent() }}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Roboto+Mono&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .auth-container {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 2rem 1rem;
        }

        .auth-footer {
            text-align: center;
            padding: 1rem;
            background-color: #fff;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
    </style>
{% endblock %}

{% block body %}
    <div class="auth-container">
        <div class="container">
            {% for label, messages in app.flashes %}
                {% if label != 'reset_password_error' %}
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
                {% endif %}
            {% endfor %}

            {% block content %}{% endblock %}
        </div>
    </div>

    <footer class="auth-footer">
        <div class="container">
            <p class="text-muted mb-0">Cashbox &copy; {{ "now"|date("Y") }} | <a href="#" class="text-decoration-none">Terms</a> | <a href="#" class="text-decoration-none">Privacy</a></p>
        </div>
    </footer>
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
{% endblock %}
```

## Security Considerations

1. **CSRF Protection**: All forms will include CSRF tokens to prevent cross-site request forgery attacks
2. **Password Hashing**: Passwords will be securely hashed using Symfony's password hasher
3. **Rate Limiting**: Login attempts will be rate-limited to prevent brute force attacks
4. **Secure Cookies**: Session cookies will be secure and HTTP-only
5. **HTTPS**: All authentication pages will be served over HTTPS
6. **Password Requirements**: Strong password requirements will be enforced
7. **Account Lockout**: Accounts will be temporarily locked after multiple failed login attempts

## User Experience Considerations

1. **Clear Feedback**: Users will receive clear feedback for all actions
2. **Minimal Friction**: The registration process will be as simple as possible while maintaining security
3. **Responsive Design**: All authentication pages will work well on all devices
4. **Accessibility**: All forms will be accessible to users with disabilities
5. **Error Handling**: Clear error messages will guide users to correct issues

## Next Steps

With the authentication pages designed, we can now proceed to implement the dashboard as described in [03-dashboard.md](03-dashboard.md).
