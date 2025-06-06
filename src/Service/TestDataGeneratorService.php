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
            $team = new Team();
            $team->setName($teamNames[$i] ?? 'Team ' . ($i + 1));
            $team->setDescription('Ein Testteam für die Entwicklungsumgebung');
            $team->setActive(true);

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

            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail(strtolower($firstName . '.' . $lastName . $i . '@example.com'));
            $user->setRoles(['ROLE_USER']);

            // Setze ein einfaches Passwort für Testbenutzer
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);

            // Weise dem Benutzer ein zufälliges Team zu
            $team = $teams[array_rand($teams)];
            $user->setTeam($team);

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
            PenaltyType::LATE_ARRIVAL => ['Zu spät zum Training', 'Verspätung zum Spiel'],
            PenaltyType::MISSED_TRAINING => ['Training verpasst', 'Unentschuldigt gefehlt'],
            PenaltyType::YELLOW_CARD => ['Gelbe Karte wegen Foulspiels', 'Gelbe Karte wegen Meckerns'],
            PenaltyType::RED_CARD => ['Rote Karte', 'Platzverweis'],
            PenaltyType::OTHER => ['Trikot vergessen', 'Mannschaftsabend verpasst', 'Zu wenig Einsatz'],
        ];

        foreach ($users as $user) {
            for ($i = 0; $i < $penaltiesPerUser; $i++) {
                $penalty = new Penalty();

                // Wähle einen zufälligen Straftyp
                $penaltyType = array_rand($penaltyTypes);
                $descriptions = $penaltyTypes[$penaltyType];

                $penalty->setUser($user);
                $penalty->setType($penaltyType);
                $penalty->setDescription($descriptions[array_rand($descriptions)]);
                $penalty->setAmount(mt_rand(5, 50) * 100); // Betrag in Cent (5-50 Euro)

                // Setze ein zufälliges Datum in den letzten 3 Monaten
                $date = new \DateTime();
                $date->sub(new \DateInterval('P' . mt_rand(1, 90) . 'D'));
                $penalty->setDate($date);

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
        $paymentMethods = ['cash', 'bank_transfer', 'paypal', 'credit_card'];

        // Gruppiere Strafen nach Benutzer
        $penaltiesByUser = [];
        foreach ($penalties as $penalty) {
            $userId = $penalty->getUser()->getId();
            if (!isset($penaltiesByUser[$userId])) {
                $penaltiesByUser[$userId] = [];
            }
            $penaltiesByUser[$userId][] = $penalty;
        }

        foreach ($users as $user) {
            $userPenalties = $penaltiesByUser[$user->getId()] ?? [];

            // Wenn der Benutzer Strafen hat, generiere Zahlungen
            if (!empty($userPenalties)) {
                $paymentsToGenerate = min(count($userPenalties), $paymentsPerUser);

                for ($i = 0; $i < $paymentsToGenerate; $i++) {
                    $payment = new Payment();
                    $payment->setUser($user);

                    // Wähle eine zufällige Strafe für die Zahlung
                    $penalty = $userPenalties[array_rand($userPenalties)];
                    $payment->setPenalty($penalty);

                    // Setze einen zufälligen Betrag (zwischen 50% und 100% des Strafbetrags)
                    $amount = (int) ($penalty->getAmount() * (mt_rand(50, 100) / 100));
                    $payment->setAmount($amount);

                    // Setze eine zufällige Zahlungsmethode
                    $payment->setMethod($paymentMethods[array_rand($paymentMethods)]);

                    // Setze einen zufälligen Status (meist bezahlt, manchmal ausstehend)
                    $status = (mt_rand(1, 10) <= 8) ? PaymentStatus::PAID : PaymentStatus::PENDING;
                    $payment->setStatus($status);

                    // Setze ein zufälliges Datum nach dem Strafdatum
                    $date = clone $penalty->getDate();
                    $date->add(new \DateInterval('P' . mt_rand(1, 30) . 'D'));
                    $payment->setDate($date);

                    $this->entityManager->persist($payment);
                    $payments[] = $payment;
                }
            }
        }

        $this->entityManager->flush();
        return $payments;
    }
}
