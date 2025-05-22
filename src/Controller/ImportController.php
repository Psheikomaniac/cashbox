<?php

namespace App\Controller;

use App\DTO\PenaltyDTO;
use App\Entity\Contribution;
use App\Entity\Penalty;
use App\Enum\CurrencyEnum;
use App\Repository\ContributionRepository;
use App\Repository\ContributionTypeRepository;
use App\Repository\PenaltyRepository;
use App\Repository\PenaltyTypeRepository;
use App\Repository\TeamRepository;
use App\Repository\TeamUserRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/import')]
class ImportController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PenaltyRepository $penaltyRepository,
        private PenaltyTypeRepository $penaltyTypeRepository,
        private ContributionRepository $contributionRepository,
        private ContributionTypeRepository $contributionTypeRepository,
        private TeamRepository $teamRepository,
        private UserRepository $userRepository,
        private TeamUserRepository $teamUserRepository,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('/penalties', methods: ['POST'])]
    public function importPenalties(Request $request): JsonResponse
    {
        /** @var UploadedFile|null $csvFile */
        $csvFile = $request->files->get('file');

        if (!$csvFile) {
            return $this->json(['message' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        if ($csvFile->getClientOriginalExtension() !== 'csv') {
            return $this->json(['message' => 'File must be a CSV'], Response::HTTP_BAD_REQUEST);
        }

        $content = file_get_contents($csvFile->getPathname());
        $rows = array_map('str_getcsv', explode("\n", $content));

        // Remove empty rows
        $rows = array_filter($rows, fn($row) => count($row) > 1);

        // Get headers
        $headers = array_shift($rows);

        if (!$headers) {
            return $this->json(['message' => 'CSV file is empty or invalid'], Response::HTTP_BAD_REQUEST);
        }

        // Validate headers
        $requiredHeaders = ['team_id', 'user_id', 'type_id', 'reason', 'amount'];
        $missingHeaders = array_diff($requiredHeaders, $headers);

        if (count($missingHeaders) > 0) {
            return $this->json([
                'message' => 'Missing required headers: ' . implode(', ', $missingHeaders)
            ], Response::HTTP_BAD_REQUEST);
        }

        $results = [
            'success' => 0,
            'errors' => [],
        ];

        foreach ($rows as $index => $row) {
            if (count($row) !== count($headers)) {
                $results['errors'][] = [
                    'row' => $index + 2, // +2 because of 0-indexing and header row
                    'message' => 'Row has incorrect number of columns',
                ];
                continue;
            }

            $data = array_combine($headers, $row);

            // Find team user
            $teamUser = $this->findTeamUser($data['team_id'], $data['user_id']);
            if (!$teamUser) {
                $results['errors'][] = [
                    'row' => $index + 2,
                    'message' => 'Team user not found',
                ];
                continue;
            }

            // Find penalty type
            $penaltyType = $this->penaltyTypeRepository->find($data['type_id']);
            if (!$penaltyType) {
                $results['errors'][] = [
                    'row' => $index + 2,
                    'message' => 'Penalty type not found',
                ];
                continue;
            }

            // Parse currency
            try {
                $currency = isset($data['currency']) ? CurrencyEnum::from($data['currency']) : CurrencyEnum::EUR;
            } catch (\ValueError $e) {
                $results['errors'][] = [
                    'row' => $index + 2,
                    'message' => 'Invalid currency',
                ];
                continue;
            }

            // Create penalty
            $penalty = new Penalty();
            $penalty->setTeamUser($teamUser);
            $penalty->setType($penaltyType);
            $penalty->setReason($data['reason']);
            $penalty->setAmount((int) $data['amount']);
            $penalty->setCurrency($currency);
            $penalty->setArchived(isset($data['archived']) && $data['archived'] === 'true');

            if (isset($data['paid_at']) && $data['paid_at']) {
                try {
                    $paidAt = new \DateTimeImmutable($data['paid_at']);
                    $penalty->setPaidAt($paidAt);
                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'row' => $index + 2,
                        'message' => 'Invalid paid at date',
                    ];
                    continue;
                }
            }

            // Validate penalty
            $errors = $this->validator->validate($penalty);
            if (count($errors) > 0) {
                $results['errors'][] = [
                    'row' => $index + 2,
                    'message' => (string) $errors,
                ];
                continue;
            }

            // Save penalty
            $this->entityManager->persist($penalty);
            $results['success']++;
        }

        $this->entityManager->flush();

        return $this->json($results);
    }

    #[Route('/contributions', methods: ['POST'])]
    public function importContributions(Request $request): JsonResponse
    {
        /** @var UploadedFile|null $csvFile */
        $csvFile = $request->files->get('file');

        if (!$csvFile) {
            return $this->json(['message' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        if ($csvFile->getClientOriginalExtension() !== 'csv') {
            return $this->json(['message' => 'File must be a CSV'], Response::HTTP_BAD_REQUEST);
        }

        $content = file_get_contents($csvFile->getPathname());
        $rows = array_map('str_getcsv', explode("\n", $content));

        // Remove empty rows
        $rows = array_filter($rows, fn($row) => count($row) > 1);

        // Get headers
        $headers = array_shift($rows);

        if (!$headers) {
            return $this->json(['message' => 'CSV file is empty or invalid'], Response::HTTP_BAD_REQUEST);
        }

        // Validate headers
        $requiredHeaders = ['team_user_id', 'type_id', 'description', 'amount', 'due_date'];
        $missingHeaders = array_diff($requiredHeaders, $headers);

        if (count($missingHeaders) > 0) {
            return $this->json([
                'message' => 'Missing required headers: ' . implode(', ', $missingHeaders)
            ], Response::HTTP_BAD_REQUEST);
        }

        $results = [
            'success' => 0,
            'errors' => [],
        ];

        foreach ($rows as $index => $row) {
            if (count($row) !== count($headers)) {
                $results['errors'][] = [
                    'row' => $index + 2, // +2 because of 0-indexing and header row
                    'message' => 'Row has incorrect number of columns',
                ];
                continue;
            }

            $data = array_combine($headers, $row);

            // Find team user
            $teamUser = $this->teamUserRepository->find($data['team_user_id']);
            if (!$teamUser) {
                $results['errors'][] = [
                    'row' => $index + 2,
                    'message' => 'Team user not found',
                ];
                continue;
            }

            // Find contribution type
            $contributionType = $this->contributionTypeRepository->find($data['type_id']);
            if (!$contributionType) {
                $results['errors'][] = [
                    'row' => $index + 2,
                    'message' => 'Contribution type not found',
                ];
                continue;
            }

            // Parse due date
            try {
                $dueDate = new \DateTimeImmutable($data['due_date']);
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'row' => $index + 2,
                    'message' => 'Invalid due date',
                ];
                continue;
            }

            // Create contribution
            $contribution = new Contribution();
            $contribution->setTeamUser($teamUser);
            $contribution->setType($contributionType);
            $contribution->setDescription($data['description']);
            $contribution->setAmount((int) $data['amount']);
            $contribution->setCurrency($data['currency'] ?? 'EUR');
            $contribution->setDueDate($dueDate);

            if (isset($data['paid_at']) && $data['paid_at']) {
                try {
                    $paidAt = new \DateTimeImmutable($data['paid_at']);
                    $contribution->setPaidAt($paidAt);
                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'row' => $index + 2,
                        'message' => 'Invalid paid at date',
                    ];
                    continue;
                }
            }

            // Validate contribution
            $errors = $this->validator->validate($contribution);
            if (count($errors) > 0) {
                $results['errors'][] = [
                    'row' => $index + 2,
                    'message' => (string) $errors,
                ];
                continue;
            }

            // Save contribution
            $this->entityManager->persist($contribution);
            $results['success']++;
        }

        $this->entityManager->flush();

        return $this->json($results);
    }

    #[Route('/contributions/template', methods: ['GET'])]
    public function getContributionsTemplate(): Response
    {
        $headers = [
            'team_user_id',
            'type_id',
            'description',
            'amount',
            'currency',
            'due_date',
            'paid_at'
        ];

        $csv = implode(',', $headers) . "\n";
        $csv .= "team_user_uuid,type_uuid,Monthly Contribution,1000,EUR,2025-12-31,\n";
        $csv .= "team_user_uuid,type_uuid,Annual Fee,5000,EUR,2025-12-31,2025-01-15\n";

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="contributions_import_template.csv"');

        return $response;
    }

    private function findTeamUser(string $teamId, string $userId)
    {
        $team = $this->teamRepository->find($teamId);
        $user = $this->userRepository->find($userId);

        if (!$team || !$user) {
            return null;
        }

        return $this->teamUserRepository->findOneByTeamAndUser($team, $user);
    }
}
