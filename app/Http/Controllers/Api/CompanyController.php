<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\ContactResource;
use App\Http\Resources\DealResource;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompanyController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('perPage', 25);

        $companies = Company::where('user_id', $request->user()->id)
            ->withCount(['contacts', 'deals'])
            ->when($request->query('search'), fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('industry', 'ilike', "%{$search}%")
                  ->orWhere('city', 'ilike', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate($perPage);

        return CompanyResource::collection($companies);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $company = Company::with(['contacts.tags', 'deals.contact'])->findOrFail($id);
        $this->authorize('view', $company);

        return response()->json([
            'id' => $company->id,
            'name' => $company->name,
            'industry' => $company->industry,
            'website' => $company->website,
            'phone' => $company->phone,
            'address' => $company->address,
            'city' => $company->city,
            'state' => $company->state,
            'zip' => $company->zip,
            'notes' => $company->notes,
            'createdAt' => $company->created_at?->toIso8601String(),
            'updatedAt' => $company->updated_at?->toIso8601String(),
            'contacts' => ContactResource::collection($company->contacts),
            'deals' => DealResource::collection($company->deals),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'zip' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $company = Company::create([
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        return (new CompanyResource($company))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $id): CompanyResource
    {
        $company = Company::findOrFail($id);
        $this->authorize('update', $company);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'zip' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $company->update($validated);

        return new CompanyResource($company);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $company = Company::findOrFail($id);
        $this->authorize('delete', $company);
        $company->delete();

        return response()->json(null, 204);
    }
}
