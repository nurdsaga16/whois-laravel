<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DomainCheckResource;
use App\Models\Domain;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DomainCheckController extends Controller
{
    public function check(Request $request)
    {
        $validated = $this->validateRequest($request);

        try {
            $results = $this->processDomains($validated['domains']);

            return response()->json([
                'domains' => DomainCheckResource::collection($results),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Ошибка при проверке доменов',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @throws ValidationException
     */
    private function validateRequest(Request $request): array
    {
        $domainsInput = $request->input('domains');
        if (is_string($domainsInput)) {
            $domains = array_filter(
                array_map('trim', preg_split('/[\n,]+/', $domainsInput)),
                fn($domain) => !empty($domain)
            );
        } else {
            $domains = is_array($domainsInput) ? array_map('trim', $domainsInput) : [];
        }

        $domains = array_unique($domains);

        $validator = Validator::make(['domains' => $domains], [
            'domains' => ['required', 'array', 'min:1'],
            'domains.*' => [
                'required',
                'string',
                'max:255',
                'regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i',
            ],
        ], [
            'domains.required' => 'Список доменов не может быть пустым.',
            'domains.*.regex' => 'Домен :input имеет неверный формат.',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return ['domains' => $domains];
    }

    private function processDomains(array $domains): array
    {
        $results = [];
        $existingDomains = Domain::query()->whereIn('name', $domains)->get()->keyBy('name');

        foreach ($domains as $domain) {
            $lowerDomain = Str::lower($domain);
            if ($existingDomains->has($lowerDomain)) {
                $results[] = $this->formatDomainResult($existingDomains[$lowerDomain]);
            } else {
                $results[] = [
                    'domain' => $lowerDomain,
                    'is_available' => false,
                    'expires_at' => null,
                ];
            }
        }

        return $results;
    }

    private function formatDomainResult(Domain $domain): array
    {
        return [
            'domain' => $domain->name,
            'is_available' => $domain->is_available,
            'expires_at' => $domain->expires_at,
        ];
    }
}
