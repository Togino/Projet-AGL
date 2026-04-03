<?php

namespace App\Controllers;

use App\Services\UserService;

class UserController
{
    public function __construct(private UserService $userService)
    {
    }

    public function index(): void
    {
        $this->jsonResponse(['data' => $this->userService->listUsers()]);
    }

    public function show(string $matricule): void
    {
        $user = $this->userService->getUser($matricule);
        if (!$user) {
            $this->jsonResponse(['message' => 'Utilisateur introuvable.'], 404);
            return;
        }

        $this->jsonResponse(['data' => $user]);
    }

    public function nextMatricule(): void
    {
        try {
            $roleId = (int) ($_GET['role_id'] ?? 0);
            if ($roleId <= 0) {
                $this->jsonResponse(['message' => 'Le parametre role_id est obligatoire.'], 422);
                return;
            }

            $matricule = $this->userService->previewNextMatricule($roleId);
            $this->jsonResponse(['data' => ['matricule' => $matricule]]);
        } catch (\Throwable $exception) {
            $this->jsonResponse(['message' => $exception->getMessage()], 422);
        }
    }

    public function store(): void
    {
        try {
            $payload = $this->getInput();
            $matricule = $this->userService->createUser($payload, $_SESSION['matricule']);
            $this->jsonResponse(['message' => 'Utilisateur cree.', 'matricule' => $matricule], 201);
        } catch (\Throwable $exception) {
            $this->jsonResponse(['message' => $exception->getMessage()], 422);
        }
    }

    public function update(string $matricule): void
    {
        try {
            $payload = $this->getInput();
            $updated = $this->userService->updateUser($matricule, $payload, $_SESSION['matricule']);
            $this->jsonResponse(['message' => $updated ? 'Utilisateur modifie.' : 'Aucune modification.']);
        } catch (\Throwable $exception) {
            $this->jsonResponse(['message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(string $matricule): void
    {
        $deleted = $this->userService->softDeleteUser($matricule, $_SESSION['matricule']);

        if (!$deleted) {
            $this->jsonResponse(['message' => 'Utilisateur introuvable ou deja supprime.'], 404);
            return;
        }

        $this->jsonResponse(['message' => 'Utilisateur desactive et supprime logiquement.']);
    }

    private function getInput(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input') ?: '';
            return json_decode($raw, true) ?: [];
        }

        if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['PUT', 'PATCH'], true)) {
            $raw = file_get_contents('php://input') ?: '';
            parse_str($raw, $parsedData);
            return $parsedData;
        }

        return $_POST;
    }

    private function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
