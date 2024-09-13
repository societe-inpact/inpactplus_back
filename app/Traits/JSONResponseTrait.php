<?php

namespace App\Traits;

use App\Models\Companies\CompanyFolderModuleAccess;
use App\Models\Modules\CompanyModuleAccess;
use App\Models\Modules\Module;
use Illuminate\Database\Eloquent\Collection;

trait JSONResponseTrait
{
    public function successResponse($data = [], $message = 'Success', $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public function errorResponse($message = 'Error', $status = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $status);
    }

    public function errorHistoryResponse($data = [], $message = 'Error', $status = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $status);
    }

    // ------------------------------------- //

    public function successConvertResponse($data = [], $message = 'Success', $header = null, $convertedFile = null, $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'header' => $header,
            'file' => $convertedFile
        ], $status);
    }

    public function errorConvertResponse($message = 'Error', $unmappedRubrics = null, $status = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'unmapped_rubrics' => $unmappedRubrics,
        ], $status);
    }
}
