<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Report;
use App\Entity\User;
use App\Enum\ReportTypeEnum;
use App\Event\ReportCreatedEvent;
use App\Event\ReportGeneratedEvent;
use App\ValueObject\PersonName;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class ReportTest extends TestCase
{
    private User $user;
    private string $reportName;
    private ReportTypeEnum $reportType;
    private array $parameters;

    protected function setUp(): void
    {
        $this->user = new User(new PersonName('John', 'Doe'));
        $this->reportName = 'Monthly Financial Report';
        $this->reportType = ReportTypeEnum::FINANCIAL;
        $this->parameters = ['dateFrom' => '2024-01-01', 'dateTo' => '2024-01-31', 'teamId' => 'test-team-id'];
    }

    public function testCanBeCreatedWithRequiredFields(): void
    {
        $report = new Report($this->reportName, $this->reportType, $this->parameters, $this->user);

        $this->assertInstanceOf(UuidInterface::class, $report->getId());
        $this->assertSame($this->user, $report->getCreatedBy());
        $this->assertSame($this->reportName, $report->getName());
        $this->assertSame($this->reportType, $report->getType());
        $this->assertSame($this->parameters, $report->getParameters());
        $this->assertNull($report->getResult());
        $this->assertFalse($report->isScheduled());
        $this->assertNull($report->getCronExpression());
        $this->assertInstanceOf(\DateTimeImmutable::class, $report->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $report->getUpdatedAt());
    }

    public function testRecordsDomainEventWhenCreated(): void
    {
        $report = new Report($this->reportName, $this->reportType, $this->parameters, $this->user);

        $events = $report->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ReportCreatedEvent::class, $events[0]);
        $this->assertSame($report, $events[0]->getReport());
    }

    public function testCanBeCreatedAsScheduledReport(): void
    {
        $cronExpression = '0 9 1 * *'; // First day of every month at 9 AM
        $report = new Report($this->reportName, $this->reportType, $this->parameters, $this->user, true, $cronExpression);

        $this->assertTrue($report->isScheduled());
        $this->assertSame($cronExpression, $report->getCronExpression());
    }

    public function testCanGenerateReportWithResults(): void
    {
        $report = new Report($this->reportName, $this->reportType, $this->parameters, $this->user);
        $result = ['totalAmount' => 1000, 'count' => 5];

        $report->generate($result);

        $this->assertSame($result, $report->getResult());

        $events = $report->releaseEvents();
        $this->assertCount(2, $events); // Created + Generated events
        $this->assertInstanceOf(ReportGeneratedEvent::class, $events[1]);
    }

    public function testCanUpdateReportParameters(): void
    {
        $report = new Report($this->reportName, $this->reportType, $this->parameters, $this->user);
        $newParameters = ['dateFrom' => '2024-02-01', 'dateTo' => '2024-02-28', 'teamId' => 'updated-team-id'];

        $report->update($this->reportName, $newParameters);

        $this->assertSame($newParameters, $report->getParameters());
    }

    public function testCanScheduleExistingReport(): void
    {
        $report = new Report($this->reportName, $this->reportType, $this->parameters, $this->user);
        $cronExpression = '0 9 1 * *';

        $report->schedule($cronExpression);

        $this->assertTrue($report->isScheduled());
        $this->assertSame($cronExpression, $report->getCronExpression());
    }

    public function testCanUnscheduleReport(): void
    {
        $cronExpression = '0 9 1 * *';
        $report = new Report($this->reportName, $this->reportType, $this->parameters, $this->user, true, $cronExpression);

        $report->unschedule();

        $this->assertFalse($report->isScheduled());
        $this->assertNull($report->getCronExpression());
    }

    public function testCanCreateWithEmptyName(): void
    {
        // The entity doesn't validate empty names in constructor
        $report = new Report('', $this->reportType, $this->parameters, $this->user);
        $this->assertSame('', $report->getName());
    }

    public function testValidatesRequiredParameters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Required parameter "dateFrom" is missing');

        // Missing required parameters for FINANCIAL report type
        new Report($this->reportName, $this->reportType, ['teamId' => 'test'], $this->user);
    }

    public function testCanCreateWithAllRequiredParameters(): void
    {
        $report = new Report($this->reportName, $this->reportType, $this->parameters, $this->user);
        $this->assertSame($this->parameters, $report->getParameters());
    }
}