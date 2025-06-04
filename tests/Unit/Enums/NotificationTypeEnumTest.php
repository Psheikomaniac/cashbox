<?php

declare(strict_types=1);

use App\Enum\NotificationTypeEnum;

describe('NotificationTypeEnum', function () {
    it('has correct case values', function () {
        expect(NotificationTypeEnum::PENALTY_CREATED->value)->toBe('penalty_created')
            ->and(NotificationTypeEnum::PENALTY_PAID->value)->toBe('penalty_paid')
            ->and(NotificationTypeEnum::PENALTY_OVERDUE->value)->toBe('penalty_overdue')
            ->and(NotificationTypeEnum::PAYMENT_RECEIVED->value)->toBe('payment_received')
            ->and(NotificationTypeEnum::REPORT_READY->value)->toBe('report_ready')
            ->and(NotificationTypeEnum::SYSTEM_MAINTENANCE->value)->toBe('system_maintenance');
    });

    it('provides correct labels', function () {
        expect(NotificationTypeEnum::PENALTY_CREATED->getLabel())->toBe('Penalty Created')
            ->and(NotificationTypeEnum::PENALTY_PAID->getLabel())->toBe('Penalty Paid')
            ->and(NotificationTypeEnum::PENALTY_OVERDUE->getLabel())->toBe('Penalty Overdue')
            ->and(NotificationTypeEnum::PAYMENT_RECEIVED->getLabel())->toBe('Payment Received')
            ->and(NotificationTypeEnum::REPORT_READY->getLabel())->toBe('Report Ready')
            ->and(NotificationTypeEnum::SYSTEM_MAINTENANCE->getLabel())->toBe('System Maintenance');
    });

    it('provides correct priorities', function () {
        expect(NotificationTypeEnum::PENALTY_CREATED->getPriority())->toBe('medium')
            ->and(NotificationTypeEnum::PENALTY_PAID->getPriority())->toBe('low')
            ->and(NotificationTypeEnum::PENALTY_OVERDUE->getPriority())->toBe('high')
            ->and(NotificationTypeEnum::PAYMENT_RECEIVED->getPriority())->toBe('low')
            ->and(NotificationTypeEnum::REPORT_READY->getPriority())->toBe('medium')
            ->and(NotificationTypeEnum::SYSTEM_MAINTENANCE->getPriority())->toBe('high');
    });

    it('provides correct retention days', function () {
        expect(NotificationTypeEnum::PENALTY_CREATED->getRetentionDays())->toBe(30)
            ->and(NotificationTypeEnum::PENALTY_PAID->getRetentionDays())->toBe(90)
            ->and(NotificationTypeEnum::PENALTY_OVERDUE->getRetentionDays())->toBe(60)
            ->and(NotificationTypeEnum::PAYMENT_RECEIVED->getRetentionDays())->toBe(90)
            ->and(NotificationTypeEnum::REPORT_READY->getRetentionDays())->toBe(7)
            ->and(NotificationTypeEnum::SYSTEM_MAINTENANCE->getRetentionDays())->toBe(14);
    });

    it('correctly identifies email requirements', function () {
        expect(NotificationTypeEnum::PENALTY_CREATED->shouldSendEmail())->toBeFalse()
            ->and(NotificationTypeEnum::PENALTY_PAID->shouldSendEmail())->toBeFalse()
            ->and(NotificationTypeEnum::PENALTY_OVERDUE->shouldSendEmail())->toBeTrue()
            ->and(NotificationTypeEnum::PAYMENT_RECEIVED->shouldSendEmail())->toBeFalse()
            ->and(NotificationTypeEnum::REPORT_READY->shouldSendEmail())->toBeTrue()
            ->and(NotificationTypeEnum::SYSTEM_MAINTENANCE->shouldSendEmail())->toBeTrue();
    });

    it('can get notification icon', function () {
        expect(NotificationTypeEnum::PENALTY_CREATED->getIcon())->toBe('warning')
            ->and(NotificationTypeEnum::PENALTY_PAID->getIcon())->toBe('check')
            ->and(NotificationTypeEnum::PENALTY_OVERDUE->getIcon())->toBe('alert')
            ->and(NotificationTypeEnum::PAYMENT_RECEIVED->getIcon())->toBe('money')
            ->and(NotificationTypeEnum::REPORT_READY->getIcon())->toBe('document')
            ->and(NotificationTypeEnum::SYSTEM_MAINTENANCE->getIcon())->toBe('settings');
    });

    it('can get notification color', function () {
        expect(NotificationTypeEnum::PENALTY_CREATED->getColor())->toBe('orange')
            ->and(NotificationTypeEnum::PENALTY_PAID->getColor())->toBe('green')
            ->and(NotificationTypeEnum::PENALTY_OVERDUE->getColor())->toBe('red')
            ->and(NotificationTypeEnum::PAYMENT_RECEIVED->getColor())->toBe('blue')
            ->and(NotificationTypeEnum::REPORT_READY->getColor())->toBe('purple')
            ->and(NotificationTypeEnum::SYSTEM_MAINTENANCE->getColor())->toBe('gray');
    });

    it('can check if notification is actionable', function () {
        expect(NotificationTypeEnum::PENALTY_CREATED->isActionable())->toBeTrue()
            ->and(NotificationTypeEnum::PENALTY_PAID->isActionable())->toBeFalse()
            ->and(NotificationTypeEnum::PENALTY_OVERDUE->isActionable())->toBeTrue()
            ->and(NotificationTypeEnum::PAYMENT_RECEIVED->isActionable())->toBeFalse()
            ->and(NotificationTypeEnum::REPORT_READY->isActionable())->toBeTrue()
            ->and(NotificationTypeEnum::SYSTEM_MAINTENANCE->isActionable())->toBeFalse();
    });

    it('can get action URL for actionable notifications', function () {
        expect(NotificationTypeEnum::PENALTY_CREATED->getActionUrl('penalty-123'))
            ->toBe('/penalties/penalty-123');

        expect(NotificationTypeEnum::PENALTY_OVERDUE->getActionUrl('penalty-456'))
            ->toBe('/penalties/penalty-456');

        expect(NotificationTypeEnum::REPORT_READY->getActionUrl('report-789'))
            ->toBe('/reports/report-789');

        expect(NotificationTypeEnum::PENALTY_PAID->getActionUrl('penalty-123'))
            ->toBeNull();
    });

    it('can get all notification types', function () {
        $allTypes = NotificationTypeEnum::getAllTypes();

        expect($allTypes)->toHaveCount(6)
            ->and($allTypes)->toContain(NotificationTypeEnum::PENALTY_CREATED)
            ->and($allTypes)->toContain(NotificationTypeEnum::PENALTY_PAID)
            ->and($allTypes)->toContain(NotificationTypeEnum::PENALTY_OVERDUE)
            ->and($allTypes)->toContain(NotificationTypeEnum::PAYMENT_RECEIVED)
            ->and($allTypes)->toContain(NotificationTypeEnum::REPORT_READY)
            ->and($allTypes)->toContain(NotificationTypeEnum::SYSTEM_MAINTENANCE);
    });

    it('can get high priority types', function () {
        $highPriorityTypes = NotificationTypeEnum::getHighPriorityTypes();

        expect($highPriorityTypes)->toContain(NotificationTypeEnum::PENALTY_OVERDUE)
            ->and($highPriorityTypes)->toContain(NotificationTypeEnum::SYSTEM_MAINTENANCE)
            ->and($highPriorityTypes)->not->toContain(NotificationTypeEnum::PENALTY_CREATED)
            ->and($highPriorityTypes)->not->toContain(NotificationTypeEnum::PENALTY_PAID)
            ->and($highPriorityTypes)->not->toContain(NotificationTypeEnum::PAYMENT_RECEIVED)
            ->and($highPriorityTypes)->not->toContain(NotificationTypeEnum::REPORT_READY);
    });

    it('can get email notification types', function () {
        $emailTypes = NotificationTypeEnum::getEmailTypes();

        expect($emailTypes)->toContain(NotificationTypeEnum::PENALTY_OVERDUE)
            ->and($emailTypes)->toContain(NotificationTypeEnum::REPORT_READY)
            ->and($emailTypes)->toContain(NotificationTypeEnum::SYSTEM_MAINTENANCE)
            ->and($emailTypes)->not->toContain(NotificationTypeEnum::PENALTY_CREATED)
            ->and($emailTypes)->not->toContain(NotificationTypeEnum::PENALTY_PAID)
            ->and($emailTypes)->not->toContain(NotificationTypeEnum::PAYMENT_RECEIVED);
    });

    it('can create from value', function () {
        $penaltyCreated = NotificationTypeEnum::from('penalty_created');
        expect($penaltyCreated)->toBe(NotificationTypeEnum::PENALTY_CREATED);

        expect(fn() => NotificationTypeEnum::from('invalid_type'))
            ->toThrow(ValueError::class);
    });

    it('can try from value', function () {
        $penaltyCreated = NotificationTypeEnum::tryFrom('penalty_created');
        expect($penaltyCreated)->toBe(NotificationTypeEnum::PENALTY_CREATED);

        $invalid = NotificationTypeEnum::tryFrom('invalid_type');
        expect($invalid)->toBeNull();
    });
});