<?php

declare(strict_types=1);

use App\Enum\PenaltyTypeEnum;

describe('PenaltyTypeEnum', function () {
    it('has correct labels', function () {
        expect(PenaltyTypeEnum::DRINK->getLabel())->toBe('Drink')
            ->and(PenaltyTypeEnum::LATE_ARRIVAL->getLabel())->toBe('Late Arrival')
            ->and(PenaltyTypeEnum::MISSED_TRAINING->getLabel())->toBe('Missed Training')
            ->and(PenaltyTypeEnum::CUSTOM->getLabel())->toBe('Custom');
    });

    it('identifies drink types correctly', function () {
        expect(PenaltyTypeEnum::DRINK->isDrink())->toBeTrue()
            ->and(PenaltyTypeEnum::LATE_ARRIVAL->isDrink())->toBeFalse()
            ->and(PenaltyTypeEnum::MISSED_TRAINING->isDrink())->toBeFalse()
            ->and(PenaltyTypeEnum::CUSTOM->isDrink())->toBeFalse();
    });

    it('has correct default amounts', function () {
        expect(PenaltyTypeEnum::DRINK->getDefaultAmount())->toBe(150) // 1.50 EUR in cents
            ->and(PenaltyTypeEnum::LATE_ARRIVAL->getDefaultAmount())->toBe(500) // 5.00 EUR
            ->and(PenaltyTypeEnum::MISSED_TRAINING->getDefaultAmount())->toBe(1500) // 15.00 EUR
            ->and(PenaltyTypeEnum::CUSTOM->getDefaultAmount())->toBe(0); // Custom amount
    });

    it('provides all enum cases', function () {
        $cases = PenaltyTypeEnum::cases();

        expect($cases)
            ->toHaveCount(4)
            ->toContain(PenaltyTypeEnum::DRINK)
            ->toContain(PenaltyTypeEnum::LATE_ARRIVAL)
            ->toContain(PenaltyTypeEnum::MISSED_TRAINING)
            ->toContain(PenaltyTypeEnum::CUSTOM);
    });
});