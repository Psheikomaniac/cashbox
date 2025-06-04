# Security Guidelines

This document outlines the security practices and guidelines for the Cashbox project.

## Overview

Security is a top priority for our application, which handles sensitive financial data. This document provides guidelines for implementing secure coding practices, protecting against common vulnerabilities, and ensuring data protection.

## General Security Principles

### Defense in Depth

Implement multiple layers of security controls throughout the application:

- Network security
- Application security
- Data security
- Authentication and authorization
- Input validation
- Output encoding
- Error handling

### Least Privilege

- Apply the principle of least privilege to all components
- Restrict permissions to the minimum required
- Use role-based access control
- Regularly audit permissions

### Secure by Default

- All features should be secure in their default configuration
- Security should not rely on proper configuration
- Disable unnecessary features and services

## Authentication and Authorization

### Authentication

- Use Symfony's security component for authentication
- Implement JWT-based authentication for API endpoints
- Enforce strong password policies
- Support multi-factor authentication
- Implement account lockout after failed attempts
- Use secure credentials storage with password hashing
- Use Argon2id for password hashing with appropriate cost factors

### Authorization

- Implement fine-grained role-based access control
- Use Symfony's Voter system for complex authorization rules
- Apply authorization checks at controller and service levels
- Verify object ownership where appropriate
- Audit authorization decisions

### JWT Security (PHP 8.4 Enhanced)

- Use short-lived JWT tokens (15-30 minutes)
- Implement refresh token rotation with PHP 8.4 property hooks for automatic validation
- Sign tokens with strong algorithms (RS256 or EdDSA)
- Include only necessary claims in tokens
- Store tokens securely on client-side with SameSite=Strict
- Never store sensitive information in tokens
- Leverage PHP 8.4's enhanced random number generation for token generation
- Use readonly classes for immutable token structures

```php
final readonly class JWTToken
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public \DateTimeImmutable $expiresAt,
        public TokenTypeEnum $type = TokenTypeEnum::BEARER
    ) {}
    
    public function isExpired(): bool
    {
        return $this->expiresAt <= new \DateTimeImmutable();
    }
}
```

## Input Validation and Sanitization

### Input Validation

- Validate all input data at API boundaries
- Use Symfony's Validator component for validation
- Implement both syntactic and semantic validation
- Use appropriate constraints for each data type
- Add custom validators for domain-specific validation

### Sanitization

- Sanitize data before storage and display
- Use appropriate encoding for different contexts
- Implement context-specific sanitization strategies
- Never trust client-supplied data

## Protection Against Common Vulnerabilities

### SQL Injection

- Use Doctrine ORM for database interactions
- Use parameterized queries for custom SQL
- Never concatenate user input in queries
- Apply appropriate input validation

### Cross-Site Scripting (XSS)

- Apply proper output encoding in all contexts
- Use Twig's automatic escaping
- Use Content Security Policy (CSP) headers
- Validate and sanitize HTML input
- Use HTTPOnly and Secure flags for cookies

### Cross-Site Request Forgery (CSRF)

- Implement CSRF protection for all forms
- Use Symfony's CSRF protection component
- Apply proper token validation
- Use SameSite cookie attribute

### Injection Attacks

- Validate and sanitize all inputs
- Use secure APIs for system interactions
- Apply proper context-based escaping
- Avoid shell executions where possible

### Insecure Direct Object References (IDOR)

- Implement proper authorization checks for all resources
- Use indirect references where appropriate
- Verify ownership before access
- Apply access control checks at multiple levels

## Data Protection

### Data Classification

Classify data based on sensitivity:

- **Public**: Information that can be freely disclosed
- **Internal**: Information for internal use only
- **Confidential**: Sensitive business information
- **Restricted**: Highly sensitive personal or business data

### Data Protection Measures

- Encrypt sensitive data at rest
- Use HTTPS for all communications
- Implement proper access controls
- Apply data minimization principles
- Implement data retention policies

### Database Security

- Use encrypted connections to the database
- Limit database user privileges
- Apply column-level encryption for sensitive data
- Regular database security audits
- Secure database backups

## Session Management

- Use secure session handling with Symfony's session component
- Implement proper session timeout and expiration
- Store session data securely
- Apply secure cookie settings
- Regenerate session IDs after authentication

## Security Headers

Implement the following security headers:

- Content-Security-Policy (CSP)
- X-Content-Type-Options: nosniff
- X-Frame-Options: DENY
- X-XSS-Protection: 1; mode=block
- Strict-Transport-Security (HSTS)
- Referrer-Policy: strict-origin-when-cross-origin
- Permissions-Policy

## Error Handling and Logging

### Secure Error Handling

- Implement custom error handling
- Never expose sensitive information in error messages
- Use generic error messages in production
- Log detailed errors for debugging

### Logging

- Implement comprehensive security logging
- Log security-relevant events
- Include necessary context in logs
- Protect log integrity
- Implement log rotation and retention
- Centralize log collection and analysis

## Dependency Management

- Regularly update dependencies
- Use Composer to manage dependencies
- Implement a dependency vulnerability scanning process
- Monitor security advisories
- Have a response plan for vulnerable dependencies

## Security Testing

### Static Analysis

- Use security-focused static analysis tools (Psalm with taint analysis)
- Run static analysis in CI/CD pipeline
- Address critical findings promptly

### Dynamic Testing

- Implement security-focused test cases
- Test authentication and authorization flows
- Test input validation and sanitization
- Test for common vulnerabilities

### Penetration Testing

- Conduct regular penetration testing
- Use a combination of automated and manual testing
- Address findings based on risk assessment
- Document testing methodology and results

## Secure Development Practices

### Code Reviews

- Include security-focused review criteria
- Use security-minded reviewers
- Verify security controls during review
- Check for common security mistakes

### Secrets Management

- Never commit secrets to the repository
- Use environment variables for secrets
- Consider using a secrets management service
- Rotate secrets regularly

### Deployment Security

- Use secure deployment processes
- Implement infrastructure as code
- Apply security controls at infrastructure level
- Conduct security validation during deployment

## Security Incident Response

- Develop and maintain an incident response plan
- Designate incident response roles and responsibilities
- Implement detection mechanisms
- Document response procedures
- Conduct post-incident reviews

## Compliance

- Identify applicable regulations and standards
- Implement required controls
- Conduct regular compliance assessments
- Document compliance efforts

## PHP 8.4 Security Enhancements

### Enhanced Type Safety for Security

Leverage PHP 8.4's improved type system for security-critical code:

```php
final readonly class SecureUserData
{
    public function __construct(
        public PersonalDataEnum $type,
        public string $value {
            set => $this->sanitizeValue($value);
        },
        public \DateTimeImmutable $lastAccessed = new \DateTimeImmutable
    ) {}
    
    private function sanitizeValue(string $value): string
    {
        return filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
}
```

### Property Hooks for Security Validation

Use property hooks for automatic security validation:

```php
class PaymentAmount
{
    public function __construct(
        public int $cents {
            set => $value > 0 
                ? $value 
                : throw new InvalidArgumentException('Amount must be positive');
        },
        public CurrencyEnum $currency
    ) {}
}
```

### Asymmetric Visibility for Security

Protect sensitive data with asymmetric visibility:

```php
class UserSession
{
    public function __construct(
        public(set) readonly string $sessionId,
        private(set) string $csrfToken,
        public(set) \DateTimeImmutable $lastActivity = new \DateTimeImmutable
    ) {}
    
    public function validateCsrfToken(string $token): bool
    {
        return hash_equals($this->csrfToken, $token);
    }
}
```

### Enhanced Random Number Generation

PHP 8.4 provides improved random number generation for security:

```php
final class SecurityTokenGenerator
{
    public static function generateSecureToken(int $length = 32): string
    {
        // PHP 8.4 enhanced randomness
        return bin2hex(random_bytes($length));
    }
    
    public static function generateCSRFToken(): string
    {
        return base64_encode(random_bytes(32));
    }
}
```

### Modern Input Validation Patterns

```php
enum ValidationRuleEnum: string
{
    case EMAIL = 'email';
    case UUID = 'uuid';
    case PHONE = 'phone';
    case AMOUNT = 'amount';
    
    public function validate(mixed $value): bool
    {
        return match($this) {
            self::EMAIL => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            self::UUID => preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-7][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value),
            self::PHONE => preg_match('/^\+?[1-9]\d{1,14}$/', $value),
            self::AMOUNT => is_numeric($value) && $value > 0,
        };
    }
}
```

## Security Resources

- [OWASP Top Ten](https://owasp.org/www-project-top-ten/)
- [Symfony Security Best Practices](https://symfony.com/doc/current/security.html)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Security_Cheat_Sheet.html)
- [API Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/REST_Security_Cheat_Sheet.html)
- [PHP 8.4 Security Improvements](https://www.php.net/releases/8.4/en.php#security)

## Conclusion

Security is an ongoing process that requires continuous attention and improvement. By following these guidelines and leveraging PHP 8.4's enhanced security features like property hooks, asymmetric visibility, and improved type safety, we can create a more secure application that protects sensitive user data and maintains the trust of our users.