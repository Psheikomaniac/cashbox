<?php

declare(strict_types=1);

use App\Entity\Report;
use App\Entity\User;
use App\Enum\ReportTypeEnum;
use App\Event\ReportCreatedEvent;
use App\Event\ReportGeneratedEvent;
use App\ValueObject\PersonName;
use Ramsey\Uuid\UuidInterface;

describe('Report Entity', function () {
    beforeEach(function () {
        $this->user = new User(new PersonName('John', 'Doe'));
        $this->reportName = 'Monthly Financial Report';
        $this->reportType = ReportTypeEnum::FINANCIAL;
        $this->parameters = ['dateFrom' => '2024-01-01', 'dateTo' => '2024-01-31'];
    });

    it('can be created with required fields', function () {
        $report = new Report($this->user, $this->reportName, $this->reportType, $this->parameters);

        expect($report->getId())->toBeInstanceOf(UuidInterface::class)
            ->and($report->getCreatedBy())->toBe($this->user)
            ->and($report->getName())->toBe($this->reportName)
            ->and($report->getType())->toBe($this->reportType)
            ->and($report->getParameters())->toBe($this->parameters)
            ->and($report->getResult())->toBeNull()
            ->and($report->isScheduled())->toBeFalse()
            ->and($report->getCronExpression())->toBeNull()
            ->and($report->getCreatedAt())->toBeInstanceOf(\DateTimeImmutable::class)
            ->and($report->getUpdatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
    });

    it('records domain event when created', function () {
        $report = new Report($this->user, $this->reportName, $this->reportType, $this->parameters);

        $events = $report->flushEvents();
        expect($events)->toHaveCount(1)
            ->and($events[0])->toBeInstanceOf(ReportCreatedEvent::class)
            ->and($events[0]->getReport())->toBe($report);
    });

    it('can be created as scheduled report', function () {
        $cronExpression = '0 9 1 * *'; // First day of every month at 9 AM
        $report = new Report($this->user, $this->reportName, $this->reportType, $this->parameters, true, $cronExpression);

        expect($report->isScheduled())->toBeTrue()
            ->and($report->getCronExpression())->toBe($cronExpression);
    });

    it('can generate report with results', function () {
        $report = new Report($this->user, $this->reportName, $this->reportType, $this->parameters);
        $result = ['totalAmount' => 1000, 'count' => 5];

        $report->generate($result);

        expect($report->getResult())->toBe($result);

        $events = $report->flushEvents();
        expect($events)->toHaveCount(2) // Created + Generated events
            ->and($events[1])->toBeInstanceOf(ReportGeneratedEvent::class);
    });

    it('can update report parameters', function () {
        $report = new Report($this->user, $this->reportName, $this->reportType, $this->parameters);
        $newParameters = ['dateFrom' => '2024-02-01', 'dateTo' => '2024-02-28'];

        $report->update($this->reportName, $newParameters);

        expect($report->getParameters())->toBe($newParameters);
    });

    it('can schedule existing report', function () {
        $report = new Report($this->user, $this->reportName, $this->reportType, $this->parameters);
        $cronExpression = '0 9 1 * *';

        $report->schedule($cronExpression);

        expect($report->isScheduled())->toBeTrue()
            ->and($report->getCronExpression())->toBe($cronExpression);
    });

    it('can unschedule report', function () {
        $cronExpression = '0 9 1 * *';
        $report = new Report($this->user, $this->reportName, $this->reportType, $this->parameters, true, $cronExpression);

        $report->unschedule();

        expect($report->isScheduled())->toBeFalse()
            ->and($report->getCronExpression())->toBeNull();
    });

    it('validates cron expression format', function () {
        expect(fn() => new Report($this->user, $this->reportName, $this->reportType, $this->parameters, true, 'invalid-cron'))
            ->toThrow(\InvalidArgumentException::class, 'Invalid cron expression format');
    });

    it('requires cron expression when scheduled', function () {
        expect(fn() => new Report($this->user, $this->reportName, $this->reportType, $this->parameters, true))
            ->toThrow(\InvalidArgumentException::class, 'Cron expression is required for scheduled reports');
    });

    it('validates report name is not empty', function () {
        expect(fn() => new Report($this->user, '', $this->reportType, $this->parameters))
            ->toThrow(\InvalidArgumentException::class, 'Report name cannot be empty');
    });

    it('validates parameters are not empty', function () {
        expect(fn() => new Report($this->user, $this->reportName, $this->reportType, []))
            ->toThrow(\InvalidArgumentException::class, 'Report parameters cannot be empty');
    });
});