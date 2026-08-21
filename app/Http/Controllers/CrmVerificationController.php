<?php

namespace App\Http\Controllers;

use App\Exceptions\CrmLookupException;
use App\Services\ConsultarCrmClient;
use App\Support\ServiceBookingCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CrmVerificationController extends Controller
{
    public function __invoke(Request $request, ConsultarCrmClient $client): JsonResponse
    {
        $data = $request->validate([
            'credential' => ['required', 'string', 'max:150'],
            'state' => ['required', 'string', 'size:2'],
            'category' => ['required', 'string', 'max:100'],
            'professional_name' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        abort_unless(ServiceBookingCatalog::usesCrmCategory($data['category']), 422);

        try {
            $result = $client->lookup($data['credential'], $data['state'], $data['professional_name']);
        } catch (CrmLookupException $exception) {
            return response()->json(
                ['message' => $exception->getMessage()],
                $exception->isTransient() ? 503 : 422
            );
        }

        return response()->json([
            'message' => 'CRM ativo localizado na fonte consultada.',
            'professional' => [
                'name' => $result['name'],
                'number' => $result['number'],
                'state' => $result['state'],
                'situation' => $result['situation'],
                'specialties' => $result['specialties'],
            ],
        ]);
    }
}
