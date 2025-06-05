<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enums;

use App\Enum\PenaltyTypeEnum;
use PHPUnit\Framework\TestCase;

class PenaltyTypeEnumTest extends TestCase
{
    public function testHasCorrectLabels(): void
    {
        $this->assertSame('Drink', PenaltyTypeEnum::DRINK->getLabel());
        $this->assertSame('Late Arrival', PenaltyTypeEnum::LATE_ARRIVAL->getLabel());
        $this->assertSame('Missed Training', PenaltyTypeEnum::MISSED_TRAINING->getLabel());
        $this->assertSame('Custom', PenaltyTypeEnum::CUSTOM->getLabel());
    }

    public function testIdentifiesDrinkTypesCorrectly(): void
    {
        $this->assertTrue(PenaltyTypeEnum::DRINK->isDrink());
        $this->assertFalse(PenaltyTypeEnum::LATE_ARRIVAL->isDrink());
        $this->assertFalse(PenaltyTypeEnum::MISSED_TRAINING->isDrink());
        $this->assertFalse(PenaltyTypeEnum::CUSTOM->isDrink());
    }

    public function testHasCorrectDefaultAmounts(): void
    {
        $this->assertSame(150, PenaltyTypeEnum::DRINK->getDefaultAmount()); // 1.50 EUR in cents
        $this->assertSame(500, PenaltyTypeEnum::LATE_ARRIVAL->getDefaultAmount()); // 5.00 EUR
        $this->assertSame(1500, PenaltyTypeEnum::MISSED_TRAINING->getDefaultAmount()); // 15.00 EUR
        $this->assertSame(0, PenaltyTypeEnum::CUSTOM->getDefaultAmount()); // Custom amount
    }

    public function testProvidesAllEnumCases(): void
    {
        $cases = PenaltyTypeEnum::cases();

        $this->assertCount(4, $cases);
        $this->assertContains(PenaltyTypeEnum::DRINK, $cases);
        $this->assertContains(PenaltyTypeEnum::LATE_ARRIVAL, $cases);
        $this->assertContains(PenaltyTypeEnum::MISSED_TRAINING, $cases);
        $this->assertContains(PenaltyTypeEnum::CUSTOM, $cases);
    }
}