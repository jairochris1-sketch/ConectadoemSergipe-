<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\FavoriteFolder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdFavoriteController extends Controller
{
    public const MAX_FAVORITES = 20;

    public function store(Request $request, Ad $ad): JsonResponse
    {
        abort_unless($ad->status === 'active', 404);

        $validated = $request->validate([
            'folder_id' => ['nullable', 'integer'],
            'folder_name' => ['nullable', 'string', 'max:60'],
        ]);
        $folderName = trim((string) ($validated['folder_name'] ?? ''));
        $folderId = $validated['folder_id'] ?? null;

        if (! $folderId && $folderName === '') {
            return response()->json([
                'message' => 'Informe o nome da pasta ou escolha uma pasta existente.',
                'errors' => [
                    'folder_name' => ['Informe o nome da pasta ou escolha uma pasta existente.'],
                ],
            ], 422);
        }

        $result = DB::transaction(function () use ($request, $folderId, $folderName, $ad): array {
            $request->user()->newQuery()->whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $favoriteQuery = DB::table('favorites')->where('user_id', $request->user()->id);
            $alreadyFavorite = (clone $favoriteQuery)->where('ad_id', $ad->id)->exists();

            if (! $alreadyFavorite && (clone $favoriteQuery)->count() >= self::MAX_FAVORITES) {
                return ['limit_reached' => true];
            }

            if ($folderId) {
                $folder = $request->user()->favoriteFolders()->findOrFail($folderId);
            } else {
                $folder = $request->user()->favoriteFolders()
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($folderName)])
                    ->first();

                $folder ??= $request->user()->favoriteFolders()->create(['name' => $folderName]);
            }

            DB::table('favorites')->updateOrInsert(
                ['user_id' => $request->user()->id, 'ad_id' => $ad->id],
                ['folder_id' => $folder->id, 'created_at' => now()]
            );

            return ['limit_reached' => false, 'folder' => $folder];
        });

        if ($result['limit_reached']) {
            $message = 'Você atingiu o limite de 20 favoritos. Apague um favorito para salvar um novo.';

            return response()->json([
                'message' => $message,
                'errors' => ['favorite' => [$message]],
                'limit' => self::MAX_FAVORITES,
            ], 422);
        }

        /** @var FavoriteFolder $folder */
        $folder = $result['folder'];

        return response()->json([
            'message' => "Anúncio salvo na pasta {$folder->name}.",
            'favorite' => true,
            'folder' => [
                'id' => $folder->id,
                'name' => $folder->name,
            ],
        ]);
    }

    public function destroy(Request $request, Ad $ad): JsonResponse
    {
        DB::table('favorites')
            ->where('user_id', $request->user()->id)
            ->where('ad_id', $ad->id)
            ->delete();

        return response()->json([
            'message' => 'Anúncio removido dos favoritos.',
            'favorite' => false,
        ]);
    }
}
