# Git Guidelines

## Introduction
This document outlines the Git workflow and best practices for the Cashbox project. Following these guidelines ensures a consistent and efficient development process.

## Branching Strategy

We follow a Git Flow-based branching strategy with the following branches:

### Main Branches
- **main**: Production-ready code only. This branch is protected and requires pull request reviews.
- **develop**: Integration branch for features. This is where feature branches are merged after review.

### Supporting Branches
- **feature/[feature-name]**: For new features and non-emergency bug fixes.
- **hotfix/[hotfix-name]**: For urgent production fixes.
- **release/[version]**: Preparation for a new production release.

## Branch Naming Conventions
- Use lowercase letters and hyphens
- Prefix with the type (feature, hotfix, etc.)
- Include issue/ticket number when applicable
- Examples:
  - `feature/user-authentication`
  - `hotfix/penalty-calculation-fix`
  - `feature/CASH-123-team-management`

## Commit Guidelines

### Commit Message Format
Follow the Conventional Commits specification:
```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

### Types
- **feat**: A new feature
- **fix**: A bug fix
- **docs**: Documentation changes
- **style**: Code style changes (formatting, missing semicolons, etc.)
- **refactor**: Code changes that neither fix bugs nor add features
- **perf**: Performance improvements
- **test**: Adding or correcting tests
- **chore**: Changes to the build process or auxiliary tools

### Examples
```
feat(api): add endpoint for retrieving user penalties
fix(payment): correct calculation for multiple payments
docs: update API documentation
```

## Pull Request Workflow

1. Create a branch from `develop` (for features) or `main` (for hotfixes)
2. Develop and test your changes locally
3. Push your branch to the remote repository
4. Create a pull request with a clear description
5. Ensure all CI tests pass
6. Request review from at least one team member
7. Address any feedback from the review
8. Once approved, merge using squash merge

## Code Review Guidelines

- Reviews should be constructive and respectful
- Focus on code quality, maintainability, and adherence to project standards
- Verify that new code includes appropriate tests
- Check for potential security issues
- Ensure documentation is updated

## Tagging and Versioning

We follow [Semantic Versioning](https://semver.org/) (SemVer):
- Format: `MAJOR.MINOR.PATCH`
- Increment MAJOR for incompatible API changes
- Increment MINOR for backwards-compatible functionality additions
- Increment PATCH for backwards-compatible bug fixes

Tag releases in Git after merging to `main`:
```bash
git tag -a v1.0.0 -m "Version 1.0.0"
git push origin v1.0.0
```

## Repository Information

- **Repository URL**: `git@github.com:Psheikomaniac/cashbox.git`
- **Main Branch**: `main`
- **Development Branch**: `develop`

## Git Hooks

We use Git hooks to ensure code quality:
- Pre-commit hooks for linting and formatting
- Pre-push hooks for running tests

## Git Best Practices

- Keep commits small and focused on a single change
- Regularly pull changes from the main branches
- Rebase feature branches to incorporate upstream changes
- Use `git fetch` to stay informed about remote changes without merging
- Avoid committing sensitive information or large binary files
- Resolve merge conflicts promptly
- Make a commit after each completed task in your plan when a file has been changed
- Push directly to GitHub after each commit to ensure changes are immediately available to the team
- Use appropriate commit messages that clearly describe the changes made

## GitLab CI/CD Integration

Our project uses GitHub Actions for CI/CD pipelines:
- Automated testing on all branches
- Code quality and static analysis checks
- Automated deployments to staging and production environments

## Troubleshooting

If you encounter issues with Git workflows, consult the team lead or refer to our internal documentation for assistance.
