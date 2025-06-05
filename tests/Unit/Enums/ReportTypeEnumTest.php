<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enums;

use App\Enum\ReportTypeEnum;
use PHPUnit\Framework\TestCase;

class ReportTypeEnumTest extends TestCase
{
    public function testHasCorrectCaseValues(): void
    {
        $this->assertSame('financial', ReportTypeEnum::FINANCIAL->value);
        $this->assertSame('penalty_summary', ReportTypeEnum::PENALTY_SUMMARY->value);
        $this->assertSame('user_activity', ReportTypeEnum::USER_ACTIVITY->value);
        $this->assertSame('team_overview', ReportTypeEnum::TEAM_OVERVIEW->value);
        $this->assertSame('payment_history', ReportTypeEnum::PAYMENT_HISTORY->value);
        $this->assertSame('audit_log', ReportTypeEnum::AUDIT_LOG->value);
    }

    public function testProvidesCorrectLabels(): void
    {
        $this->assertSame('Financial Report', ReportTypeEnum::FINANCIAL->getLabel());
        $this->assertSame('Penalty Summary', ReportTypeEnum::PENALTY_SUMMARY->getLabel());
        $this->assertSame('User Activity Report', ReportTypeEnum::USER_ACTIVITY->getLabel());
        $this->assertSame('Team Overview', ReportTypeEnum::TEAM_OVERVIEW->getLabel());
        $this->assertSame('Payment History', ReportTypeEnum::PAYMENT_HISTORY->getLabel());
        $this->assertSame('Audit Log', ReportTypeEnum::AUDIT_LOG->getLabel());
    }

    public function testProvidesRequiredParametersForEachType(): void
    {
        $this->assertSame(['dateFrom', 'dateTo', 'teamId'], ReportTypeEnum::FINANCIAL->getRequiredParameters());
        $this->assertSame(['dateFrom', 'dateTo', 'teamId'], ReportTypeEnum::PENALTY_SUMMARY->getRequiredParameters());
        $this->assertSame(['userId', 'dateFrom', 'dateTo'], ReportTypeEnum::USER_ACTIVITY->getRequiredParameters());
        $this->assertSame(['teamId'], ReportTypeEnum::TEAM_OVERVIEW->getRequiredParameters());
        $this->assertSame(['dateFrom', 'dateTo', 'userId'], ReportTypeEnum::PAYMENT_HISTORY->getRequiredParameters());
        $this->assertSame(['dateFrom', 'dateTo'], ReportTypeEnum::AUDIT_LOG->getRequiredParameters());
    }

    public function testProvidesEstimatedExecutionTimes(): void
    {
        $this->assertSame(30, ReportTypeEnum::FINANCIAL->getEstimatedExecutionTime());
        $this->assertSame(15, ReportTypeEnum::PENALTY_SUMMARY->getEstimatedExecutionTime());
        $this->assertSame(10, ReportTypeEnum::USER_ACTIVITY->getEstimatedExecutionTime());
        $this->assertSame(5, ReportTypeEnum::TEAM_OVERVIEW->getEstimatedExecutionTime());
        $this->assertSame(20, ReportTypeEnum::PAYMENT_HISTORY->getEstimatedExecutionTime());
        $this->assertSame(60, ReportTypeEnum::AUDIT_LOG->getEstimatedExecutionTime());
    }

    public function testCorrectlyIdentifiesAsyncRequirements(): void
    {
        $this->assertFalse(ReportTypeEnum::FINANCIAL->requiresAsync());
        $this->assertFalse(ReportTypeEnum::PENALTY_SUMMARY->requiresAsync());
        $this->assertFalse(ReportTypeEnum::USER_ACTIVITY->requiresAsync());
        $this->assertFalse(ReportTypeEnum::TEAM_OVERVIEW->requiresAsync());
        $this->assertFalse(ReportTypeEnum::PAYMENT_HISTORY->requiresAsync());
        $this->assertTrue(ReportTypeEnum::AUDIT_LOG->requiresAsync());
    }

    public function testProvidesCorrectDescriptions(): void
    {
        $this->assertSame('Comprehensive financial overview including penalties, payments, and balances', ReportTypeEnum::FINANCIAL->getDescription());
        $this->assertSame('Summary of penalties by type, status, and team member', ReportTypeEnum::PENALTY_SUMMARY->getDescription());
        $this->assertSame('Detailed user activity including penalties and payments', ReportTypeEnum::USER_ACTIVITY->getDescription());
        $this->assertSame('Team statistics and member overview', ReportTypeEnum::TEAM_OVERVIEW->getDescription());
        $this->assertSame('Complete payment history with transactions', ReportTypeEnum::PAYMENT_HISTORY->getDescription());
        $this->assertSame('System audit log with user actions and changes', ReportTypeEnum::AUDIT_LOG->getDescription());
    }

    public function testProvidesCorrectDefaultFormats(): void
    {
        $this->assertSame('pdf', ReportTypeEnum::FINANCIAL->getDefaultFormat());
        $this->assertSame('pdf', ReportTypeEnum::PENALTY_SUMMARY->getDefaultFormat());
        $this->assertSame('html', ReportTypeEnum::USER_ACTIVITY->getDefaultFormat());
        $this->assertSame('html', ReportTypeEnum::TEAM_OVERVIEW->getDefaultFormat());
        $this->assertSame('excel', ReportTypeEnum::PAYMENT_HISTORY->getDefaultFormat());
        $this->assertSame('csv', ReportTypeEnum::AUDIT_LOG->getDefaultFormat());
    }

    public function testCanGetAllForFrontend(): void
    {
        $allData = ReportTypeEnum::getAllForFrontend();

        $this->assertCount(6, $allData);
        $this->assertArrayHasKey('value', $allData[0]);
        $this->assertArrayHasKey('label', $allData[0]);
        $this->assertArrayHasKey('description', $allData[0]);
        $this->assertArrayHasKey('estimatedTime', $allData[0]);
        $this->assertArrayHasKey('requiresAsync', $allData[0]);
        $this->assertArrayHasKey('requiredParameters', $allData[0]);
        $this->assertArrayHasKey('defaultFormat', $allData[0]);
    }

    public function testCanCreateFromValue(): void
    {
        $financial = ReportTypeEnum::from('financial');
        $this->assertSame(ReportTypeEnum::FINANCIAL, $financial);

        $this->expectException(\ValueError::class);
        ReportTypeEnum::from('invalid_type');
    }

    public function testCanTryFromValue(): void
    {
        $financial = ReportTypeEnum::tryFrom('financial');
        $this->assertSame(ReportTypeEnum::FINANCIAL, $financial);

        $invalid = ReportTypeEnum::tryFrom('invalid_type');
        $this->assertNull($invalid);
    }
}