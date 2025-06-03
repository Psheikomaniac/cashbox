# Choose One API Approach

## Current Issues

1. **Duplicate API Implementation**
   - The project currently uses both API Platform and manual controllers for the same resources
   - For example, the User entity is exposed through:
     - API Platform annotations in the entity class
     ```php
     #[ApiResource(
         operations: [
             new Get(),
             new GetCollection(),
             new Post(),
             new Patch()
         ],
         normalizationContext: ['groups' => ['user:read']],
         denormalizationContext: ['groups' => ['user:write']]
     )]
     ```
     - Manual controller with duplicate endpoints
     ```php
     #[Route('/api/users')]
     class UserController extends AbstractController
     {
         #[Route('', methods: ['GET'])]
         public function getAll(): JsonResponse { ... }

         #[Route('/{id}', methods: ['GET'])]
         public function getOne(string $id): JsonResponse { ... }

         #[Route('', methods: ['POST'])]
         public function create(Request $request): JsonResponse { ... }

         #[Route('/{id}', methods: ['PATCH'])]
         public function update(string $id, Request $request): JsonResponse { ... }
     }
     ```

2. **Confusion and Maintenance Overhead**
   - Having two different ways to expose the same resources creates confusion
   - Changes need to be made in multiple places
   - Inconsistent behavior between the two approaches
   - Difficult to maintain and extend

## Recommended Actions

1. **Choose One Approach**
   - **Option 1: Fully Embrace API Platform**
     - Remove manual controllers for resources already exposed through API Platform
     - Use API Platform's features for customization (custom operations, data providers, data persisters)
     - Leverage API Platform's built-in features (pagination, filtering, validation)

   - **Option 2: Remove API Platform**
     - Remove API Platform annotations from entities
     - Standardize manual controllers
     - Implement consistent patterns for CRUD operations

2. **Standardize the Chosen Approach**
   - Create a consistent pattern for all resources
   - Document the approach for future development
   - Ensure all resources follow the same pattern

3. **Update Documentation**
   - Update API documentation to reflect the chosen approach
   - Provide examples of how to use the API

## Implementation Priority

This task should be addressed with high priority after fixing the security issues. Having a consistent API approach will make the codebase more maintainable and reduce confusion for developers working on the project.
