# Email Templates

## Overview

This document outlines the design and implementation of email templates for the Cashbox application. These templates provide consistent, professional communication with users for various system notifications, invitations, and alerts. All templates follow responsive design principles and maintain brand consistency.

## Design Principles

### Visual Identity
1. **Brand Consistency**: Use Cashbox colors, typography, and logo consistently
2. **Professional Appearance**: Clean, modern design that builds trust
3. **Responsive Design**: Templates work well on desktop and mobile email clients
4. **Accessibility**: High contrast, clear hierarchy, alt text for images
5. **Clear Call-to-Actions**: Prominent, actionable buttons

### Content Strategy
1. **Clear Subject Lines**: Descriptive and actionable
2. **Personalization**: Address users by name when possible
3. **Concise Messaging**: Get to the point quickly
4. **Value Proposition**: Explain why the email matters to the user
5. **Next Steps**: Clear instructions on what to do next

## Email Layout Foundation

### Base Email Template
```twig
{# templates/emails/base.html.twig #}
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <title>{% block title %}Cashbox{% endblock %}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    
    <style>
        /* Reset and base styles */
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        
        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            outline: none;
            text-decoration: none;
        }
        
        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f8f9fa;
            font-family: 'Roboto', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
        }
        
        /* Container */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        
        /* Header */
        .email-header {
            background-color: #1976D2;
            padding: 20px;
            text-align: center;
        }
        
        .email-header .logo {
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
        }
        
        /* Content */
        .email-content {
            padding: 30px 20px;
        }
        
        .email-content h1 {
            color: #333333;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .email-content h2 {
            color: #333333;
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 15px;
            margin-top: 25px;
        }
        
        .email-content p {
            margin-bottom: 15px;
            font-size: 16px;
            line-height: 1.6;
        }
        
        /* Buttons */
        .btn {
            display: inline-block;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 5px;
            text-align: center;
            line-height: 1.2;
        }
        
        .btn-primary {
            background-color: #1976D2;
            color: #ffffff;
        }
        
        .btn-success {
            background-color: #2E7D32;
            color: #ffffff;
        }
        
        .btn-warning {
            background-color: #F57C00;
            color: #ffffff;
        }
        
        .btn-outline {
            background-color: transparent;
            color: #1976D2;
            border: 2px solid #1976D2;
        }
        
        /* Cards */
        .card {
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            background-color: #f8f9fa;
        }
        
        /* Financial amounts */
        .amount {
            font-family: 'Roboto Mono', monospace;
            font-weight: 600;
            font-size: 18px;
        }
        
        .amount.positive {
            color: #2E7D32;
        }
        
        .amount.negative {
            color: #F44336;
        }
        
        /* Footer */
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #757575;
            border-top: 1px solid #E0E0E0;
        }
        
        .email-footer a {
            color: #1976D2;
            text-decoration: none;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }
            
            .email-content {
                padding: 20px 15px !important;
            }
            
            .email-content h1 {
                font-size: 24px !important;
            }
            
            .btn {
                display: block !important;
                width: 100% !important;
                margin: 10px 0 !important;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <a href="{{ app.request.schemeAndHttpHost }}" class="logo">
                💰 Cashbox
            </a>
        </div>
        
        <!-- Content -->
        <div class="email-content">
            {% block content %}{% endblock %}
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <p>
                © {{ 'now'|date('Y') }} Cashbox. All rights reserved.<br>
                <a href="{{ url('unsubscribe', {'token': unsubscribe_token}) }}">Unsubscribe</a> | 
                <a href="{{ url('terms') }}">Terms</a> | 
                <a href="{{ url('privacy') }}">Privacy</a>
            </p>
        </div>
    </div>
</body>
</html>
```

## Authentication Email Templates

### Welcome Email
```twig
{# templates/emails/auth/welcome.html.twig #}
{% extends 'emails/base.html.twig' %}

{% block title %}Welcome to Cashbox - {{ user.fullName }}{% endblock %}

{% block content %}
<h1>Welcome to Cashbox, {{ user.firstName }}! 🎉</h1>

<p>We're excited to have you join the Cashbox community! Your account has been successfully created and you're ready to start managing your team finances with ease.</p>

<div class="card">
    <h2>What you can do with Cashbox:</h2>
    <ul>
        <li><strong>Track Contributions:</strong> Create and manage team financial contributions</li>
        <li><strong>Manage Penalties:</strong> Record and track penalty payments</li>
        <li><strong>Process Payments:</strong> Handle all team financial transactions</li>
        <li><strong>Generate Reports:</strong> Get insights into your team's financial health</li>
    </ul>
</div>

<p style="text-align: center; margin: 30px 0;">
    <a href="{{ url('dashboard') }}" class="btn btn-primary">Get Started with Your Dashboard</a>
</p>

<h2>Need help getting started?</h2>
<p>Check out our quick start guide or contact our support team if you have any questions.</p>

<p style="text-align: center;">
    <a href="{{ url('help') }}" class="btn btn-outline">View Help Center</a>
</p>

<p>Thanks for choosing Cashbox!</p>
<p><strong>The Cashbox Team</strong></p>
{% endblock %}
```

### Email Verification
```twig
{# templates/emails/auth/email_verification.html.twig #}
{% extends 'emails/base.html.twig' %}

{% block title %}Verify your email address - Cashbox{% endblock %}

{% block content %}
<h1>Verify Your Email Address</h1>

<p>Hi {{ user.firstName }},</p>

<p>Thanks for creating your Cashbox account! To complete your registration and start using all features, please verify your email address by clicking the button below.</p>

<p style="text-align: center; margin: 30px 0;">
    <a href="{{ verification_url }}" class="btn btn-primary">Verify Email Address</a>
</p>

<p>This verification link will expire in 24 hours for security reasons.</p>

<div class="card">
    <p><strong>Can't click the button?</strong> Copy and paste this link into your browser:</p>
    <p style="word-break: break-all; font-family: monospace; font-size: 14px;">{{ verification_url }}</p>
</div>

<p>If you didn't create this account, you can safely ignore this email.</p>

<p>Welcome to Cashbox!</p>
<p><strong>The Cashbox Team</strong></p>
{% endblock %}
```

### Password Reset
```twig
{# templates/emails/auth/password_reset.html.twig #}
{% extends 'emails/base.html.twig' %}

{% block title %}Reset your password - Cashbox{% endblock %}

{% block content %}
<h1>Reset Your Password</h1>

<p>Hi {{ user.firstName }},</p>

<p>We received a request to reset your password for your Cashbox account. If you made this request, click the button below to reset your password.</p>

<p style="text-align: center; margin: 30px 0;">
    <a href="{{ reset_url }}" class="btn btn-primary">Reset Password</a>
</p>

<p>This password reset link will expire in 1 hour for security reasons.</p>

<div class="card">
    <p><strong>Can't click the button?</strong> Copy and paste this link into your browser:</p>
    <p style="word-break: break-all; font-family: monospace; font-size: 14px;">{{ reset_url }}</p>
</div>

<p><strong>If you didn't request this password reset,</strong> you can safely ignore this email. Your password will remain unchanged.</p>

<p>For security, we recommend:</p>
<ul>
    <li>Using a strong, unique password</li>
    <li>Not sharing your password with others</li>
    <li>Logging out of shared computers</li>
</ul>

<p>Stay secure!</p>
<p><strong>The Cashbox Team</strong></p>
{% endblock %}
```

## Team Management Email Templates

### Team Invitation
```twig
{# templates/emails/teams/invitation.html.twig #}
{% extends 'emails/base.html.twig' %}

{% block title %}You're invited to join {{ team.name }} on Cashbox{% endblock %}

{% block content %}
<h1>You're Invited to Join {{ team.name }}! 🏆</h1>

<p>Hi there,</p>

<p><strong>{{ invited_by.fullName }}</strong> has invited you to join <strong>{{ team.name }}</strong> on Cashbox, our team financial management platform.</p>

{% if personal_message %}
<div class="card">
    <h2>Personal message from {{ invited_by.firstName }}:</h2>
    <p style="font-style: italic;">"{{ personal_message }}"</p>
</div>
{% endif %}

<div class="card">
    <h2>About {{ team.name }}</h2>
    {% if team.description %}
        <p>{{ team.description }}</p>
    {% endif %}
    <ul>
        <li><strong>Team Members:</strong> {{ team.memberCount }} members</li>
        <li><strong>Your Role:</strong> {{ invited_role|title }}</li>
        <li><strong>Invited by:</strong> {{ invited_by.fullName }}</li>
    </ul>
</div>

<h2>What is Cashbox?</h2>
<p>Cashbox helps teams manage their finances by tracking contributions, penalties, and payments. It's perfect for sports teams, clubs, and any group that needs to manage shared expenses.</p>

<p style="text-align: center; margin: 30px 0;">
    <a href="{{ invitation_url }}" class="btn btn-primary">Accept Invitation</a>
</p>

<p>This invitation will expire in 7 days.</p>

<div class="card">
    <p><strong>Can't click the button?</strong> Copy and paste this link into your browser:</p>
    <p style="word-break: break-all; font-family: monospace; font-size: 14px;">{{ invitation_url }}</p>
</div>

<p>If you don't want to join this team, you can safely ignore this email.</p>

<p>Looking forward to having you on the team!</p>
<p><strong>The Cashbox Team</strong></p>
{% endblock %}
```

### Member Added to Team
```twig
{# templates/emails/teams/member_added.html.twig #}
{% extends 'emails/base.html.twig' %}

{% block title %}{{ new_member.fullName }} joined {{ team.name }} - Cashbox{% endblock %}

{% block content %}
<h1>New Team Member Alert! 👋</h1>

<p>Hi {{ user.firstName }},</p>

<p>Great news! <strong>{{ new_member.fullName }}</strong> has joined <strong>{{ team.name }}</strong>.</p>

<div class="card">
    <h2>Team Update</h2>
    <ul>
        <li><strong>New Member:</strong> {{ new_member.fullName }} ({{ new_member.email }})</li>
        <li><strong>Role:</strong> {{ new_member_role|title }}</li>
        <li><strong>Total Members:</strong> {{ team.memberCount }}</li>
        <li><strong>Joined:</strong> {{ new_member.joinedAt|date('F d, Y') }}</li>
    </ul>
</div>

<p>You can view the complete team member list and manage roles from your team dashboard.</p>

<p style="text-align: center; margin: 30px 0;">
    <a href="{{ url('team_detail', {'id': team.id}) }}" class="btn btn-primary">View Team Details</a>
</p>

<p>Keep building your team!</p>
<p><strong>The Cashbox Team</strong></p>
{% endblock %}
```

## Financial Notification Templates

### New Contribution Created
```twig
{# templates/emails/contributions/new_contribution.html.twig #}
{% extends 'emails/base.html.twig' %}

{% block title %}New Contribution: {{ contribution.title }} - {{ team.name }}{% endblock %}

{% block content %}
<h1>New Contribution Created 💰</h1>

<p>Hi {{ user.firstName }},</p>

<p>A new contribution has been created for <strong>{{ team.name }}</strong> that requires your payment.</p>

<div class="card">
    <h2>{{ contribution.title }}</h2>
    {% if contribution.description %}
        <p>{{ contribution.description }}</p>
    {% endif %}
    
    <table style="width: 100%; margin: 15px 0;">
        <tr>
            <td><strong>Amount:</strong></td>
            <td class="amount negative">{{ contribution.amount|format_currency(team.currency) }}</td>
        </tr>
        <tr>
            <td><strong>Due Date:</strong></td>
            <td>{{ contribution.dueDate|date('F d, Y') }}</td>
        </tr>
        <tr>
            <td><strong>Created by:</strong></td>
            <td>{{ contribution.createdBy.fullName }}</td>
        </tr>
        {% if contribution.category %}
        <tr>
            <td><strong>Category:</strong></td>
            <td>{{ contribution.category }}</td>
        </tr>
        {% endif %}
    </table>
</div>

{% if contribution.daysUntilDue <= 7 %}
<div class="card" style="background-color: #fff3cd; border-color: #ffc107;">
    <p style="margin: 0; color: #856404;"><strong>⏰ Due Soon:</strong> This contribution is due in {{ contribution.daysUntilDue }} day{{ contribution.daysUntilDue != 1 ? 's' : '' }}.</p>
</div>
{% endif %}

<p style="text-align: center; margin: 30px 0;">
    <a href="{{ url('contribution_detail', {'team': team.id, 'id': contribution.id}) }}" class="btn btn-primary">View Details & Pay</a>
</p>

<p>You can pay this contribution through your Cashbox dashboard or by clicking the link above.</p>

<p>Questions about this contribution? Contact {{ contribution.createdBy.fullName }} or your team administrator.</p>

<p><strong>The Cashbox Team</strong></p>
{% endblock %}
```

### Payment Reminder
```twig
{# templates/emails/payments/reminder.html.twig #}
{% extends 'emails/base.html.twig' %}

{% block title %}Payment Reminder - {{ item.title }} - {{ team.name }}{% endblock %}

{% block content %}
<h1>Payment Reminder ⏰</h1>

<p>Hi {{ user.firstName }},</p>

<p>This is a friendly reminder that you have an outstanding {{ item_type }} payment for <strong>{{ team.name }}</strong>.</p>

<div class="card">
    <h2>{{ item.title }}</h2>
    {% if item.description %}
        <p>{{ item.description }}</p>
    {% endif %}
    
    <table style="width: 100%; margin: 15px 0;">
        <tr>
            <td><strong>Amount:</strong></td>
            <td class="amount negative">{{ item.amount|format_currency(team.currency) }}</td>
        </tr>
        <tr>
            <td><strong>Due Date:</strong></td>
            <td>{{ item.dueDate|date('F d, Y') }}</td>
        </tr>
        <tr>
            <td><strong>Days Overdue:</strong></td>
            <td>{{ item.daysOverdue }} days</td>
        </tr>
    </table>
</div>

{% if item.daysOverdue > 0 %}
<div class="card" style="background-color: #f8d7da; border-color: #f44336;">
    <p style="margin: 0; color: #721c24;"><strong>⚠️ Overdue:</strong> This payment is {{ item.daysOverdue }} day{{ item.daysOverdue != 1 ? 's' : '' }} overdue.</p>
</div>
{% elseif item.daysUntilDue <= 3 %}
<div class="card" style="background-color: #fff3cd; border-color: #ffc107;">
    <p style="margin: 0; color: #856404;"><strong>⏰ Due Soon:</strong> This payment is due in {{ item.daysUntilDue }} day{{ item.daysUntilDue != 1 ? 's' : '' }}.</p>
</div>
{% endif %}

<p style="text-align: center; margin: 30px 0;">
    <a href="{{ url(item_type ~ '_detail', {'team': team.id, 'id': item.id}) }}" class="btn btn-primary">Pay Now</a>
</p>

<p>Please make your payment as soon as possible to avoid any late fees or additional penalties.</p>

<p>If you have any questions or need assistance with your payment, please contact your team administrator.</p>

<p><strong>The Cashbox Team</strong></p>
{% endblock %}
```

### Payment Received Confirmation
```twig
{# templates/emails/payments/confirmation.html.twig #}
{% extends 'emails/base.html.twig' %}

{% block title %}Payment Received - {{ payment.amount|format_currency(team.currency) }} - {{ team.name }}{% endblock %}

{% block content %}
<h1>Payment Received ✅</h1>

<p>Hi {{ user.firstName }},</p>

<p>Thank you! We've received your payment for <strong>{{ team.name }}</strong>.</p>

<div class="card">
    <h2>Payment Details</h2>
    <table style="width: 100%; margin: 15px 0;">
        <tr>
            <td><strong>Amount:</strong></td>
            <td class="amount positive">{{ payment.amount|format_currency(team.currency) }}</td>
        </tr>
        <tr>
            <td><strong>Payment Date:</strong></td>
            <td>{{ payment.date|date('F d, Y') }}</td>
        </tr>
        <tr>
            <td><strong>Payment Method:</strong></td>
            <td>{{ payment.method|title|replace({'_': ' '}) }}</td>
        </tr>
        {% if payment.reference %}
        <tr>
            <td><strong>Reference:</strong></td>
            <td>{{ payment.reference }}</td>
        </tr>
        {% endif %}
        <tr>
            <td><strong>For:</strong></td>
            <td>{{ payment.relatedItem.title }}</td>
        </tr>
    </table>
</div>

{% if payment.status == 'pending' %}
<div class="card" style="background-color: #fff3cd; border-color: #ffc107;">
    <p style="margin: 0; color: #856404;"><strong>Pending Verification:</strong> Your payment is pending admin verification. You'll receive another confirmation once it's verified.</p>
</div>
{% endif %}

<p>Your current account status has been updated. You can view your complete payment history and account balance from your dashboard.</p>

<p style="text-align: center; margin: 30px 0;">
    <a href="{{ url('dashboard', {'team': team.id}) }}" class="btn btn-primary">View Dashboard</a>
</p>

<p>Thank you for staying current with your team obligations!</p>

<p><strong>The Cashbox Team</strong></p>
{% endblock %}
```

## Administrative Email Templates

### Payment Verification Request
```twig
{# templates/emails/admin/payment_verification.html.twig #}
{% extends 'emails/base.html.twig' %}

{% block title %}Payment Verification Required - {{ team.name }}{% endblock %}

{% block content %}
<h1>Payment Verification Required 🔍</h1>

<p>Hi {{ admin.firstName }},</p>

<p>A new payment has been submitted for <strong>{{ team.name }}</strong> and requires your verification.</p>

<div class="card">
    <h2>Payment Details</h2>
    <table style="width: 100%; margin: 15px 0;">
        <tr>
            <td><strong>Member:</strong></td>
            <td>{{ payment.member.fullName }}</td>
        </tr>
        <tr>
            <td><strong>Amount:</strong></td>
            <td class="amount positive">{{ payment.amount|format_currency(team.currency) }}</td>
        </tr>
        <tr>
            <td><strong>Payment Date:</strong></td>
            <td>{{ payment.date|date('F d, Y') }}</td>
        </tr>
        <tr>
            <td><strong>Method:</strong></td>
            <td>{{ payment.method|title|replace({'_': ' '}) }}</td>
        </tr>
        <tr>
            <td><strong>For:</strong></td>
            <td>{{ payment.relatedItem.title }}</td>
        </tr>
        {% if payment.note %}
        <tr>
            <td><strong>Note:</strong></td>
            <td>{{ payment.note }}</td>
        </tr>
        {% endif %}
    </table>
</div>

<p style="text-align: center; margin: 30px 0;">
    <a href="{{ url('payment_detail', {'team': team.id, 'id': payment.id}) }}" class="btn btn-primary">Review & Verify Payment</a>
</p>

<p>Please review the payment details and verify or reject the payment. The member will be notified once you take action.</p>

<p><strong>The Cashbox Team</strong></p>
{% endblock %}
```

### Monthly Financial Summary
```twig
{# templates/emails/reports/monthly_summary.html.twig #}
{% extends 'emails/base.html.twig' %}

{% block title %}Monthly Financial Summary - {{ team.name }} - {{ month|date('F Y') }}{% endblock %}

{% block content %}
<h1>Monthly Financial Summary 📊</h1>

<p>Hi {{ user.firstName }},</p>

<p>Here's your monthly financial summary for <strong>{{ team.name }}</strong> for {{ month|date('F Y') }}.</p>

<div class="card">
    <h2>Financial Overview</h2>
    <table style="width: 100%; margin: 15px 0;">
        <tr>
            <td><strong>Opening Balance:</strong></td>
            <td class="amount {{ summary.openingBalance >= 0 ? 'positive' : 'negative' }}">{{ summary.openingBalance|format_currency(team.currency) }}</td>
        </tr>
        <tr>
            <td><strong>Total Income:</strong></td>
            <td class="amount positive">{{ summary.totalIncome|format_currency(team.currency) }}</td>
        </tr>
        <tr>
            <td><strong>Total Contributions:</strong></td>
            <td class="amount">{{ summary.totalContributions|format_currency(team.currency) }}</td>
        </tr>
        <tr>
            <td><strong>Total Penalties:</strong></td>
            <td class="amount">{{ summary.totalPenalties|format_currency(team.currency) }}</td>
        </tr>
        <tr style="border-top: 2px solid #ddd;">
            <td><strong>Closing Balance:</strong></td>
            <td class="amount {{ summary.closingBalance >= 0 ? 'positive' : 'negative' }}">{{ summary.closingBalance|format_currency(team.currency) }}</td>
        </tr>
    </table>
</div>

<div class="card">
    <h2>Activity Summary</h2>
    <ul>
        <li><strong>{{ summary.contributionsCount }}</strong> contributions created</li>
        <li><strong>{{ summary.penaltiesCount }}</strong> penalties recorded</li>
        <li><strong>{{ summary.paymentsCount }}</strong> payments processed</li>
        <li><strong>{{ summary.activeMembers }}</strong> active members</li>
    </ul>
</div>

{% if summary.outstandingAmount > 0 %}
<div class="card" style="background-color: #fff3cd; border-color: #ffc107;">
    <h2>Outstanding Payments</h2>
    <p style="color: #856404; margin: 0;">There are <strong>{{ summary.outstandingAmount|format_currency(team.currency) }}</strong> in outstanding payments from team members.</p>
</div>
{% endif %}

<p style="text-align: center; margin: 30px 0;">
    <a href="{{ url('reports_detail', {'team': team.id, 'month': month|date('Y-m')}) }}" class="btn btn-primary">View Detailed Report</a>
</p>

<p>Need to generate a custom report or export data? Visit your team dashboard for more reporting options.</p>

<p><strong>The Cashbox Team</strong></p>
{% endblock %}
```

## Styling Guidelines

### Email-Specific CSS Considerations
```css
/* Email client compatibility */
.email-safe-table {
    border-collapse: collapse;
    mso-table-lspace: 0pt;
    mso-table-rspace: 0pt;
}

/* Outlook specific fixes */
.outlook-fix {
    mso-line-height-rule: exactly;
    line-height: 100%;
}

/* Gmail app dark mode support */
@media (prefers-color-scheme: dark) {
    .email-container {
        background-color: #1f1f1f !important;
        color: #ffffff !important;
    }
    
    .email-content {
        background-color: #1f1f1f !important;
        color: #ffffff !important;
    }
    
    .card {
        background-color: #2d2d2d !important;
        border-color: #404040 !important;
    }
}
```

## Email Testing Checklist

### Client Testing
- [ ] Gmail (web, iOS, Android)
- [ ] Outlook (desktop, web, mobile)
- [ ] Apple Mail (macOS, iOS)
- [ ] Yahoo Mail
- [ ] Thunderbird
- [ ] Windows Mail

### Device Testing
- [ ] Desktop browsers
- [ ] Mobile browsers
- [ ] Tablet displays
- [ ] High-DPI displays

### Content Testing
- [ ] Subject line length (< 50 characters)
- [ ] Preview text optimization
- [ ] Link functionality
- [ ] Image alt text
- [ ] Accessibility compliance
- [ ] Unsubscribe link functionality

## Implementation Notes

### Email Service Integration
```yaml
# config/packages/mailer.yaml
framework:
    mailer:
        dsn: '%env(MAILER_DSN)%'
        envelope:
            sender: 'noreply@cashbox.app'
        headers:
            From: 'Cashbox <noreply@cashbox.app>'
            Reply-To: 'support@cashbox.app'
```

### Email Queue Processing
```php
// src/Service/EmailService.php
class EmailService
{
    public function sendWelcomeEmail(User $user): void
    {
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Welcome to Cashbox')
            ->htmlTemplate('emails/auth/welcome.html.twig')
            ->context(['user' => $user]);
            
        $this->mailer->send($email);
    }
}
```

## Conclusion

These email templates provide a comprehensive communication system for the Cashbox application, ensuring users stay informed about important team financial activities while maintaining a professional, branded experience. The responsive design and accessibility considerations ensure emails work well across all email clients and devices.

Regular testing and monitoring of email deliverability, open rates, and user engagement will help optimize the email communication strategy over time.