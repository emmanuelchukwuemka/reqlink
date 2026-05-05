<?php

namespace App\Http\Controllers\Api;

use App\Domains\Emergencies\Services\EmergencyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SosController extends Controller
{
    protected $emergencyService;

    public function __construct(EmergencyService $emergencyService)
    {
        $this->emergencyService = $emergencyService;
    }

    public function trigger(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emergency_type_id' => 'required|exists:emergency_types,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'description' => 'nullable|string',
            'subtype' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $emergency = $this->emergencyService->createEmergency($request->user(), $request->all());

        return response()->json([
            'message' => 'Emergency triggered successfully',
            'data' => $emergency
        ], 201);
    }
}
