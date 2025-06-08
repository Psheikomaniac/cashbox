<?php

namespace App\Service;

use App\Entity\Penalty;
use App\Entity\Payment;
use App\Entity\Team;
use App\Entity\User;
use App\Enum\PaymentStatus;
use App\Enum\PenaltyType;
use App\Repository\PenaltyRepository;
use App\Repository\PaymentRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Service zum Generieren von Testdaten für die Entwicklungsumgebung.
 */
class TestDataGeneratorService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
        private readonly TeamRepository $teamRepository,
        private readonly PenaltyRepository $penaltyRepository,
        private readonly PaymentRepository $paymentRepository
    ) {
    }

    /**
     * Generiert Testdaten basierend auf der angegebenen Menge.
     *
     * @param string $amount Die Menge der zu generierenden Daten (small, medium, large)
     * @return array Statistik über die generierten Daten
     */
    public function generate(string $amount = 'medium'): array
    {
        // Bestimme die Anzahl der zu generierenden Daten basierend auf der Menge
        $counts = match ($amount) {
            'small' => [
                'users' => 5,
                'teams' => 1,
                'penalties_per_user' => 2,
                'payments_per_user' => 1,
            ],
            'large' => [
                'users' => 50,
                'teams' => 5,
                'penalties_per_user' => 10,
                'payments_per_user' => 5,
            ],
            default => [
                'users' => 20,
                'teams' => 3,
                'penalties_per_user' => 5,
                'payments_per_user' => 3,
            ],
        };

        // Generiere Teams
        $teams = $this->generateTeams($counts['teams']);

        // Generiere Benutzer
        $users = $this->generateUsers($counts['users'], $teams);

        // Generiere Strafen
        $penalties = $this->generatePenalties($users, $counts['penalties_per_user']);

        // Generiere Zahlungen
        $payments = $this->generatePayments($users, $penalties, $counts['payments_per_user']);

        // Statistik zurückgeben
        return [
            'users' => count($users),
            'teams' => count($teams),
            'penalties' => count($penalties),
            'payments' => count($payments),
        ];
    }

    /**
     * Generiert Teams.
     *
     * @param int $count Anzahl der zu generierenden Teams
     * @return Team[] Array mit generierten Teams
     */
    private function generateTeams(int $count): array
    {
        $teams = [];
        $teamNames = ['FC Testverein', 'SV Datenbank', 'TSV Entwicklung', 'Sportfreunde Docker', 'VfB Symfony'];

        for ($i = 0; $i < $count; $i++) {
            $teamName = $teamNames[$i] ?? 'Team ' . ($i + 1);
            $team = Team::create($teamName);
            // Add metadata for description
            $team->addMetadata('description', 'Ein Testteam für die Entwicklungsumgebung');

            $this->entityManager->persist($team);
            $teams[] = $team;
        }

        $this->entityManager->flush();
        return $teams;
    }

    /**
     * Generiert Benutzer.
     *
     * @param int $count Anzahl der zu generierenden Benutzer
     * @param Team[] $teams Teams, denen die Benutzer zugeordnet werden
     * @return User[] Array mit generierten Benutzern
     */
    private function generateUsers(int $count, array $teams): array
    {
        $users = [];
        $firstNames = ['Max', 'Anna', 'Paul', 'Lisa', 'Tim', 'Julia', 'Felix', 'Laura', 'David', 'Sarah'];
        $lastNames = ['Müller', 'Schmidt', 'Schneider', 'Fischer', 'Weber', 'Meyer', 'Wagner', 'Becker', 'Schulz', 'Hoffmann'];

        for ($i = 0; $i < $count; $i++) {
            $user = new User();
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];

            $email = strtolower($firstName . '.' . $lastName . $i . '@example.com');
            $user->updateProfile(
                new \App\ValueObject\PersonName($firstName, $lastName),
                new \App\ValueObject\Email($email)
            );
            $user->setRoles(['ROLE_USER']);

            // Setze ein einfaches Passwort für Testbenutzer
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);

            // Weise dem Benutzer ein zufälliges Team zu
            $team = $teams[array_rand($teams)];
            $teamUser = new \App\Entity\TeamUser($team, $user);
            $this->entityManager->persist($teamUser);

            $this->entityManager->persist($user);
            $users[] = $user;
        }

        $this->entityManager->flush();
        return $users;
    }

    /**
     * Generiert Strafen.
     *
     * @param User[] $users Benutzer, für die Strafen generiert werden
     * @param int $penaltiesPerUser Anzahl der Strafen pro Benutzer
     * @return Penalty[] Array mit generierten Strafen
     */
    private function generatePenalties(array $users, int $penaltiesPerUser): array
    {
        $penalties = [];
        $penaltyTypes = [
            'late_arrival' => ['Zu spät zum Training', 'Verspätung zum Spiel'],
            'missed_training' => ['Training verpasst', 'Unentschuldigt gefehlt'],
            'custom' => ['Trikot vergessen', 'Mannschaftsabend verpasst', 'Zu wenig Einsatz'],
        ];

        // Lade alle TeamUser-Einträge für die Benutzer
        $teamUsers = $this->entityManager->getRepository(\App\Entity\TeamUser::class)->findBy([
            'user' => $users
        ]);

        // Gruppiere TeamUser nach User-ID
        $teamUsersByUserId = [];
        foreach ($teamUsers as $teamUser) {
            $userId = $teamUser->getUser()->getId()->toString();
            $teamUsersByUserId[$userId] = $teamUser;
        }

        foreach ($users as $user) {
            $teamUser = $teamUsersByUserId[$user->getId()->toString()] ?? null;
            if (!$teamUser) {
                continue; // Überspringe Benutzer ohne TeamUser
            }

            for ($i = 0; $i < $penaltiesPerUser; $i++) {
                // Wähle einen zufälligen Straftyp
                $penaltyTypeKey = array_rand($penaltyTypes);
                $descriptions = $penaltyTypes[$penaltyTypeKey];
                $reason = $descriptions[array_rand($descriptions)];

                // Erstelle einen PenaltyType
                $penaltyType = new \App\Entity\PenaltyType(
                    $reason,
                    \App\Enum\PenaltyTypeEnum::from($penaltyTypeKey)
                );
                $this->entityManager->persist($penaltyType);

                // Erstelle ein Money-Objekt
                $amount = mt_rand(5, 50) * 100; // Betrag in Cent (5-50 Euro)
                $money = new \App\ValueObject\Money($amount, \App\Enum\CurrencyEnum::EUR);

                // Erstelle die Penalty
                $penalty = new \App\Entity\Penalty(
                    $teamUser,
                    $penaltyType,
                    $reason,
                    $money
                );

                $this->entityManager->persist($penalty);
                $penalties[] = $penalty;
            }
        }

        $this->entityManager->flush();
        return $penalties;
    }

    /**
     * Generiert Zahlungen.
     *
     * @param User[] $users Benutzer, für die Zahlungen generiert werden
     * @param Penalty[] $penalties Strafen, für die Zahlungen generiert werden
     * @param int $paymentsPerUser Anzahl der Zahlungen pro Benutzer
     * @return Payment[] Array mit generierten Zahlungen
     */
    private function generatePayments(array $users, array $penalties, int $paymentsPerUser): array
    {
        $payments = [];
        $paymentTypes = [
            \App\Enum\PaymentTypeEnum::CASH,
            \App\Enum\PaymentTypeEnum::BANK_TRANSFER,
            \App\Enum\PaymentTypeEnum::CREDIT_CARD,
            \App\Enum\PaymentTypeEnum::MOBILE_PAYMENT
        ];

        // Gruppiere Strafen nach Benutzer
        $penaltiesByUser = [];
        foreach ($penalties as $penalty) {
            $userId = $penalty->getTeamUser()->getUser()->getId()->toString();
            if (!isset($penaltiesByUser[$userId])) {
                $penaltiesByUser[$userId] = [];
            }
            $penaltiesByUser[$userId][] = $penalty;
        }

        // Lade alle TeamUser-Einträge für die Benutzer
        $teamUsers = $this->entityManager->getRepository(\App\Entity\TeamUser::class)->findBy([
            'user' => $users
        ]);

        // Gruppiere TeamUser nach User-ID
        $teamUsersByUserId = [];
        foreach ($teamUsers as $teamUser) {
            $userId = $teamUser->getUser()->getId()->toString();
            $teamUsersByUserId[$userId] = $teamUser;
        }

        foreach ($users as $user) {
            $userId = $user->getId()->toString();
            $userPenalties = $penaltiesByUser[$userId] ?? [];
            $teamUser = $teamUsersByUserId[$userId] ?? null;

            // Wenn der Benutzer Strafen hat und einen TeamUser-Eintrag, generiere Zahlungen
            if (!empty($userPenalties) && $teamUser) {
                $paymentsToGenerate = min(count($userPenalties), $paymentsPerUser);

                for ($i = 0; $i < $paymentsToGenerate; $i++) {
                    $payment = new \App\Entity\Payment();
                    $payment->setTeamUser($teamUser);

                    // Wähle eine zufällige Strafe für die Zahlung
                    $penalty = $userPenalties[array_rand($userPenalties)];

                    // Setze einen zufälligen Betrag (zwischen 50% und 100% des Strafbetrags)
                    $amount = (int) ($penalty->getAmount() * (mt_rand(50, 100) / 100));
                    $money = new \App\ValueObject\Money($amount, \App\Enum\CurrencyEnum::EUR);
                    $payment->setMoney($money);

                    // Setze eine zufällige Zahlungsmethode
                    $paymentType = $paymentTypes[array_rand($paymentTypes)];
                    $payment->setType($paymentType);

                    // Setze eine Beschreibung
                    $payment->setDescription('Zahlung für ' . $penalty->getReason());

                    // Setze eine Referenz, wenn erforderlich
                    if ($paymentType->requiresReference()) {
                        $payment->setReference('REF-' . strtoupper(substr(md5(mt_rand()), 0, 8)));
                    }

                    $this->entityManager->persist($payment);
                    $payments[] = $payment;
                }
            }
        }

        $this->entityManager->flush();
        return $payments;
    }
}
