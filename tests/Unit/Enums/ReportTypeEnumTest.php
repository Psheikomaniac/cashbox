<?php

declare(strict_types=1);

use App\Enum\ReportTypeEnum;

describe('ReportTypeEnum', function () {
    it('has correct case values', function () {
        expect(ReportTypeEnum::FINANCIAL->value)->toBe('financial')
            ->and(ReportTypeEnum::PENALTY_SUMMARY->value)->toBe('penalty_summary')
            ->and(ReportTypeEnum::USER_ACTIVITY->value)->toBe('user_activity')
            ->and(ReportTypeEnum::TEAM_OVERVIEW->value)->toBe('team_overview')
            ->and(ReportTypeEnum::PAYMENT_HISTORY->value)->toBe('payment_history')
            ->and(ReportTypeEnum::AUDIT_LOG->value)->toBe('audit_log');
    });

    it('provides correct labels', function () {
        expect(ReportTypeEnum::FINANCIAL->getLabel())->toBe('Financial Report')
            ->and(ReportTypeEnum::PENALTY_SUMMARY->getLabel())->toBe('Penalty Summary')
            ->and(ReportTypeEnum::USER_ACTIVITY->getLabel())->toBe('User Activity Report')
            ->and(ReportTypeEnum::TEAM_OVERVIEW->getLabel())->toBe('Team Overview')
            ->and(ReportTypeEnum::PAYMENT_HISTORY->getLabel())->toBe('Payment History')
            ->and(ReportTypeEnum::AUDIT_LOG->getLabel())->toBe('Audit Log');
    });

    it('provides required parameters for each type', function () {
        expect(ReportTypeEnum::FINANCIAL->getRequiredParameters())
            ->toBe(['dateFrom', 'dateTo']);

        expect(ReportTypeEnum::PENALTY_SUMMARY->getRequiredParameters())
            ->toBe(['dateFrom', 'dateTo']);

        expect(ReportTypeEnum::USER_ACTIVITY->getRequiredParameters())
            ->toBe(['userId', 'dateFrom', 'dateTo']);

        expect(ReportTypeEnum::TEAM_OVERVIEW->getRequiredParameters())
            ->toBe(['teamId']);

        expect(ReportTypeEnum::PAYMENT_HISTORY->getRequiredParameters())
            ->toBe(['dateFrom', 'dateTo']);

        expect(ReportTypeEnum::AUDIT_LOG->getRequiredParameters())
            ->toBe(['dateFrom', 'dateTo']);
    });

    it('provides estimated execution times', function () {
        expect(ReportTypeEnum::FINANCIAL->getEstimatedExecutionTime())->toBe(30)
            ->and(ReportTypeEnum::PENALTY_SUMMARY->getEstimatedExecutionTime())->toBe(15)
            ->and(ReportTypeEnum::USER_ACTIVITY->getEstimatedExecutionTime())->toBe(10)
            ->and(ReportTypeEnum::TEAM_OVERVIEW->getEstimatedExecutionTime())->toBe(20)
            ->and(ReportTypeEnum::PAYMENT_HISTORY->getEstimatedExecutionTime())->toBe(25)
            ->and(ReportTypeEnum::AUDIT_LOG->getEstimatedExecutionTime())->toBe(45);
    });

    it('correctly identifies async requirements', function () {
        expect(ReportTypeEnum::FINANCIAL->requiresAsync())->toBeTrue()
            ->and(ReportTypeEnum::PENALTY_SUMMARY->requiresAsync())->toBeFalse()
            ->and(ReportTypeEnum::USER_ACTIVITY->requiresAsync())->toBeFalse()
            ->and(ReportTypeEnum::TEAM_OVERVIEW->requiresAsync())->toBeFalse()
            ->and(ReportTypeEnum::PAYMENT_HISTORY->requiresAsync())->toBeTrue()
            ->and(ReportTypeEnum::AUDIT_LOG->requiresAsync())->toBeTrue();
    });

    it('can validate parameters', function () {
        $financialParams = ['dateFrom' => '2024-01-01', 'dateTo' => '2024-01-31'];
        expect(ReportTypeEnum::FINANCIAL->validateParameters($financialParams))->toBeTrue();

        $incompleteParams = ['dateFrom' => '2024-01-01'];
        expect(ReportTypeEnum::FINANCIAL->validateParameters($incompleteParams))->toBeFalse();

        $userActivityParams = ['userId' => 'user-123', 'dateFrom' => '2024-01-01', 'dateTo' => '2024-01-31'];
        expect(ReportTypeEnum::USER_ACTIVITY->validateParameters($userActivityParams))->toBeTrue();

        $missingUserParams = ['dateFrom' => '2024-01-01', 'dateTo' => '2024-01-31'];
        expect(ReportTypeEnum::USER_ACTIVITY->validateParameters($missingUserParams))->toBeFalse();
    });

    it('can get all report types', function () {
        $allTypes = ReportTypeEnum::getAllTypes();

        expect($allTypes)->toHaveCount(6)
            ->and($allTypes)->toContain(ReportTypeEnum::FINANCIAL)
            ->and($allTypes)->toContain(ReportTypeEnum::PENALTY_SUMMARY)
            ->and($allTypes)->toContain(ReportTypeEnum::USER_ACTIVITY)
            ->and($allTypes)->toContain(ReportTypeEnum::TEAM_OVERVIEW)
            ->and($allTypes)->toContain(ReportTypeEnum::PAYMENT_HISTORY)
            ->and($allTypes)->toContain(ReportTypeEnum::AUDIT_LOG);
    });

    it('can get sync report types', function () {
        $syncTypes = ReportTypeEnum::getSyncTypes();

        expect($syncTypes)->toContain(ReportTypeEnum::PENALTY_SUMMARY)
            ->and($syncTypes)->toContain(ReportTypeEnum::USER_ACTIVITY)
            ->and($syncTypes)->toContain(ReportTypeEnum::TEAM_OVERVIEW)
            ->and($syncTypes)->not->toContain(ReportTypeEnum::FINANCIAL)
            ->and($syncTypes)->not->toContain(ReportTypeEnum::PAYMENT_HISTORY)
            ->and($syncTypes)->not->toContain(ReportTypeEnum::AUDIT_LOG);
    });

    it('can get async report types', function () {
        $asyncTypes = ReportTypeEnum::getAsyncTypes();

        expect($asyncTypes)->toContain(ReportTypeEnum::FINANCIAL)
            ->and($asyncTypes)->toContain(ReportTypeEnum::PAYMENT_HISTORY)
            ->and($asyncTypes)->toContain(ReportTypeEnum::AUDIT_LOG)
            ->and($asyncTypes)->not->toContain(ReportTypeEnum::PENALTY_SUMMARY)
            ->and($asyncTypes)->not->toContain(ReportTypeEnum::USER_ACTIVITY)
            ->and($asyncTypes)->not->toContain(ReportTypeEnum::TEAM_OVERVIEW);
    });

    it('can create from value', function () {
        $financial = ReportTypeEnum::from('financial');
        expect($financial)->toBe(ReportTypeEnum::FINANCIAL);

        expect(fn() => ReportTypeEnum::from('invalid_type'))
            ->toThrow(ValueError::class);
    });

    it('can try from value', function () {
        $financial = ReportTypeEnum::tryFrom('financial');
        expect($financial)->toBe(ReportTypeEnum::FINANCIAL);

        $invalid = ReportTypeEnum::tryFrom('invalid_type');
        expect($invalid)->toBeNull();
    });
});