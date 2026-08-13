<?php

namespace App\Http\Traits;

/**
 * One consistent JSON envelope for every Customer API response, so a
 * client can parse success/error the same way regardless of which
 * endpoint it called. See the API documentation page (Settings > System >
 * API Documentation) for the exact shape.
 */
trait ApiResponseTrait
{
    protected function success($data = null, string $message = 'OK', int $status = 200, array $meta = [])
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    protected function error(string $message = 'Something went wrong', int $status = 400, $errors = null)
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * Wraps a LengthAwarePaginator into the standard envelope with a
     * pagination block in meta, instead of Laravel's default paginator
     * JSON shape (which nests differently per endpoint).
     */
    protected function paginated($paginator, $itemTransform = null, string $message = 'OK')
    {
        $items = $itemTransform ? $paginator->getCollection()->map($itemTransform)->values() : $paginator->items();

        return $this->success($items, $message, 200, [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ]);
    }
}
