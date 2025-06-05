<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enums;

use App\Enum\NotificationTypeEnum;
use PHPUnit\Framework\TestCase;

class NotificationTypeEnumTest extends TestCase
{
    public function testHasCorrectCaseValues(): void
    {
        $this->assertSame('penalty_created', NotificationTypeEnum::PENALTY_CREATED->value);
        $this->assertSame('payment_received', NotificationTypeEnum::PAYMENT_RECEIVED->value);
        $this->assertSame('payment_reminder', NotificationTypeEnum::PAYMENT_REMINDER->value);
        $this->assertSame('balance_update', NotificationTypeEnum::BALANCE_UPDATE->value);
        $this->assertSame('report_generated', NotificationTypeEnum::REPORT_GENERATED->value);
        $this->assertSame('system_update', NotificationTypeEnum::SYSTEM_UPDATE->value);
    }

    public function testProvidesCorrectLabels(): void
    {
        $this->assertSame('New Penalty', NotificationTypeEnum::PENALTY_CREATED->getLabel());
        $this->assertSame('Payment Received', NotificationTypeEnum::PAYMENT_RECEIVED->getLabel());
        $this->assertSame('Payment Reminder', NotificationTypeEnum::PAYMENT_REMINDER->getLabel());
        $this->assertSame('Balance Update', NotificationTypeEnum::BALANCE_UPDATE->getLabel());
        $this->assertSame('Report Ready', NotificationTypeEnum::REPORT_GENERATED->getLabel());
        $this->assertSame('System Update', NotificationTypeEnum::SYSTEM_UPDATE->getLabel());
    }

    public function testProvidesCorrectPriorities(): void
    {
        $this->assertSame(3, NotificationTypeEnum::PENALTY_CREATED->getPriority());
        $this->assertSame(2, NotificationTypeEnum::PAYMENT_RECEIVED->getPriority());
        $this->assertSame(3, NotificationTypeEnum::PAYMENT_REMINDER->getPriority());
        $this->assertSame(2, NotificationTypeEnum::BALANCE_UPDATE->getPriority());
        $this->assertSame(1, NotificationTypeEnum::REPORT_GENERATED->getPriority());
        $this->assertSame(1, NotificationTypeEnum::SYSTEM_UPDATE->getPriority());
    }

    public function testProvidesCorrectRetentionDays(): void
    {
        $this->assertSame(365, NotificationTypeEnum::PENALTY_CREATED->getRetentionDays());
        $this->assertSame(365, NotificationTypeEnum::PAYMENT_RECEIVED->getRetentionDays());
        $this->assertSame(90, NotificationTypeEnum::PAYMENT_REMINDER->getRetentionDays());
        $this->assertSame(30, NotificationTypeEnum::BALANCE_UPDATE->getRetentionDays());
        $this->assertSame(7, NotificationTypeEnum::REPORT_GENERATED->getRetentionDays());
        $this->assertSame(30, NotificationTypeEnum::SYSTEM_UPDATE->getRetentionDays());
    }

    public function testCorrectlyIdentifiesEmailRequirements(): void
    {
        $this->assertTrue(NotificationTypeEnum::PENALTY_CREATED->shouldSendEmail());
        $this->assertTrue(NotificationTypeEnum::PAYMENT_RECEIVED->shouldSendEmail());
        $this->assertTrue(NotificationTypeEnum::PAYMENT_REMINDER->shouldSendEmail());
        $this->assertFalse(NotificationTypeEnum::BALANCE_UPDATE->shouldSendEmail());
        $this->assertFalse(NotificationTypeEnum::REPORT_GENERATED->shouldSendEmail());
        $this->assertFalse(NotificationTypeEnum::SYSTEM_UPDATE->shouldSendEmail());
    }

    public function testCanGetNotificationIcon(): void
    {
        $this->assertSame('exclamation-triangle', NotificationTypeEnum::PENALTY_CREATED->getIcon());
        $this->assertSame('check-circle', NotificationTypeEnum::PAYMENT_RECEIVED->getIcon());
        $this->assertSame('clock', NotificationTypeEnum::PAYMENT_REMINDER->getIcon());
        $this->assertSame('calculator', NotificationTypeEnum::BALANCE_UPDATE->getIcon());
        $this->assertSame('document-text', NotificationTypeEnum::REPORT_GENERATED->getIcon());
        $this->assertSame('cog', NotificationTypeEnum::SYSTEM_UPDATE->getIcon());
    }

    public function testCanGetNotificationColor(): void
    {
        $this->assertSame('red', NotificationTypeEnum::PENALTY_CREATED->getColor());
        $this->assertSame('green', NotificationTypeEnum::PAYMENT_RECEIVED->getColor());
        $this->assertSame('orange', NotificationTypeEnum::PAYMENT_REMINDER->getColor());
        $this->assertSame('blue', NotificationTypeEnum::BALANCE_UPDATE->getColor());
        $this->assertSame('purple', NotificationTypeEnum::REPORT_GENERATED->getColor());
        $this->assertSame('gray', NotificationTypeEnum::SYSTEM_UPDATE->getColor());
    }

    public function testCanCheckIfNotificationIsActionRequired(): void
    {
        $this->assertTrue(NotificationTypeEnum::PENALTY_CREATED->isActionRequired());
        $this->assertFalse(NotificationTypeEnum::PAYMENT_RECEIVED->isActionRequired());
        $this->assertTrue(NotificationTypeEnum::PAYMENT_REMINDER->isActionRequired());
        $this->assertFalse(NotificationTypeEnum::BALANCE_UPDATE->isActionRequired());
        $this->assertFalse(NotificationTypeEnum::REPORT_GENERATED->isActionRequired());
        $this->assertFalse(NotificationTypeEnum::SYSTEM_UPDATE->isActionRequired());
    }

    public function testCanGetByPriority(): void
    {
        $highPriorityTypes = NotificationTypeEnum::getByPriority(3);
        $this->assertCount(2, $highPriorityTypes);
        $this->assertContains(NotificationTypeEnum::PENALTY_CREATED, $highPriorityTypes);
        $this->assertContains(NotificationTypeEnum::PAYMENT_REMINDER, $highPriorityTypes);

        $mediumPriorityTypes = NotificationTypeEnum::getByPriority(2);
        $this->assertCount(2, $mediumPriorityTypes);
        $this->assertContains(NotificationTypeEnum::PAYMENT_RECEIVED, $mediumPriorityTypes);
        $this->assertContains(NotificationTypeEnum::BALANCE_UPDATE, $mediumPriorityTypes);

        $lowPriorityTypes = NotificationTypeEnum::getByPriority(1);
        $this->assertCount(2, $lowPriorityTypes);
        $this->assertContains(NotificationTypeEnum::REPORT_GENERATED, $lowPriorityTypes);
        $this->assertContains(NotificationTypeEnum::SYSTEM_UPDATE, $lowPriorityTypes);
    }

    public function testCanGetAllForFrontend(): void
    {
        $allData = NotificationTypeEnum::getAllForFrontend();

        $this->assertCount(6, $allData);
        $this->assertArrayHasKey('value', $allData[0]);
        $this->assertArrayHasKey('label', $allData[0]);
        $this->assertArrayHasKey('icon', $allData[0]);
        $this->assertArrayHasKey('priority', $allData[0]);
        $this->assertArrayHasKey('color', $allData[0]);
        $this->assertArrayHasKey('actionRequired', $allData[0]);
        $this->assertArrayHasKey('defaultEmailEnabled', $allData[0]);
        $this->assertArrayHasKey('retentionDays', $allData[0]);
    }

    public function testCanCreateFromValue(): void
    {
        $penaltyCreated = NotificationTypeEnum::from('penalty_created');
        $this->assertSame(NotificationTypeEnum::PENALTY_CREATED, $penaltyCreated);

        $this->expectException(\ValueError::class);
        NotificationTypeEnum::from('invalid_type');
    }

    public function testCanTryFromValue(): void
    {
        $penaltyCreated = NotificationTypeEnum::tryFrom('penalty_created');
        $this->assertSame(NotificationTypeEnum::PENALTY_CREATED, $penaltyCreated);

        $invalid = NotificationTypeEnum::tryFrom('invalid_type');
        $this->assertNull($invalid);
    }
}