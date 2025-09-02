<?php

namespace App\Traits;

trait ApiResponse
{

    protected function success($message, $data, $status = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'status' => $status
        ], $status);
    }

    protected function ok($message, $data = null)
    {
        return $this->success($message, $data, 200);
    }

    protected function create($message, $data = null)
    {
        return $this->success($message, $data, 201);
    }
}
