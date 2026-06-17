<?php

namespace App\Http\Controllers;

use App\Http\Requests\Administration\StoreSpecialtyRequest;
use App\Models\Specialty;
use App\Services\SpecialtyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpecialtyController extends Controller
{
    public function __construct(
        private readonly SpecialtyService $specialtyService
    ) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->can('campaign.view'), 403);

        $specialties = $this->specialtyService->search(
            $request->query('q'),
            (int) $request->query('limit', 100)
        );

        return response()->json([
            'data' => $specialties->map(fn (Specialty $specialty) => [
                'id' => $specialty->id,
                'name' => $specialty->name,
            ]),
        ]);
    }

    public function store(StoreSpecialtyRequest $request): JsonResponse
    {
        $specialty = $this->specialtyService->create($request->validated('name'));

        return response()->json([
            'message' => __('specialties.messages.created'),
            'data' => [
                'id' => $specialty->id,
                'name' => $specialty->name,
            ],
        ], 201);
    }
}
