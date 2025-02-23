<?php

namespace App\Http\Controller;

abstract class AbstractController
{
    protected function jsonResponse(array $data, int $status = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
    }

    protected function getRequestData(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}