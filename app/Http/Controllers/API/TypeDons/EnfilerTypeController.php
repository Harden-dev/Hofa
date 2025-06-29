<?php

namespace App\Http\Controllers\API\TypeDons;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\EnfileType\EnfileTypeRequest;
use App\Http\Resources\TypeDons\EnfilerTypeResource;
use App\Models\Enfiler;
use App\Models\EnfilerType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Types de Dons",
 *     description="API Endpoints pour la gestion des types de dons"
 * )
 */
class EnfilerTypeController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/enfiler-types",
     *     operationId="indexTypesDons",
     *     tags={"Types de Dons"},
     *     summary="Récupérer tous les types de dons",
     *     description="Récupérer une liste paginée des types de dons avec filtrage et recherche optionnels",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrer par statut du type de don",
     *         required=false,
     *         @OA\Schema(type="string", enum={"activated", "desactivated"}, default="activated")
     *     ),
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Terme de recherche pour le label",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Types de dons récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="enfiler Types retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                     @OA\Property(property="slug", type="string", example="E-TYPE-12345678-1234-1234-1234-123456789abc"),
     *                     @OA\Property(property="label", type="string", example="Don financier"),
     *                     @OA\Property(property="description", type="string", example="Don en argent pour soutenir nos actions"),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z")
     *                 )),
     *                 @OA\Property(property="pagination", type="object",
     *                     @OA\Property(property="total", type="integer", example=10),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        // Recherche
        $query = EnfilerType::query();

        // Filtrage par status
        $status = $request->get('status');

        if ($status === 'activated') {
            $query->where('is_active', 1);
        } elseif ($status === 'desactivated') {
            $query->where('is_active', 0);
        } else {
            $query->where('is_active', 1); // Par défaut, on ne retourne que les activés
        }
        
        if ($request->has('q') && $request->q) {
            $q = $request->q;
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('label', 'like', "%$q%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 10);

        $enfilerTypes = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->sendResponse(

            [
                'items' => EnfilerTypeResource::collection($enfilerTypes),

                'pagination' => [
                    'total' => $enfilerTypes->total(),
                    'per_page' => $enfilerTypes->perPage(),
                    'current_page' => $enfilerTypes->currentPage(),
                    'last_page' => $enfilerTypes->lastPage(),
                ]
            ],
            'enfiler Types retrieved successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/enfiler-types/{enfilerType}",
     *     operationId="showTypeDon",
     *     tags={"Types de Dons"},
     *     summary="Récupérer un type de don spécifique",
     *     description="Récupérer les détails d'un type de don par son ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="enfilerType",
     *         in="path",
     *         description="ID du type de don",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type de don récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="enfiler Type retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="object",
     *                     @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                     @OA\Property(property="slug", type="string", example="E-TYPE-12345678-1234-1234-1234-123456789abc"),
     *                     @OA\Property(property="label", type="string", example="Don financier"),
     *                     @OA\Property(property="description", type="string", example="Don en argent pour soutenir nos actions"),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de don non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Type de don not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function show(EnfilerType $enfilerType)
    {
        return $this->sendResponse( ['items' => new EnfilerTypeResource($enfilerType)], 'enfiler Type retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/enfiler-types",
     *     operationId="storeTypeDon",
     *     tags={"Types de Dons"},
     *     summary="Créer un nouveau type de don",
     *     description="Créer un nouveau type de don pour catégoriser les dons",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"label"},
     *             @OA\Property(property="label", type="string", example="Don financier", description="Libellé du type de don"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Don en argent pour soutenir nos actions", description="Description détaillée du type de don")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Type de don créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="enfiler Type created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                 @OA\Property(property="slug", type="string", example="E-TYPE-12345678-1234-1234-1234-123456789abc"),
     *                 @OA\Property(property="label", type="string", example="Don financier"),
     *                 @OA\Property(property="description", type="string", example="Don en argent pour soutenir nos actions"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function store(EnfileTypeRequest $request)
    {
        $data = $request->all();
        $data['slug'] = 'E-TYPE-' . Str::uuid();
        try {
            $enfilerType = EnfilerType::create($data);
            Log::info("enfiler Type created successfully");
            return $this->sendResponse(new EnfilerTypeResource($enfilerType), 'enfiler Type created successfully');
        } catch (Exception $th) {
            Log::info("Error creating enfiler Type: " . $th->getMessage());
            return $this->sendError('Error creating enfiler Type');
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/enfiler-types/{enfilerType}",
     *     operationId="updateTypeDon",
     *     tags={"Types de Dons"},
     *     summary="Modifier un type de don",
     *     description="Modifier les informations d'un type de don existant",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="enfilerType",
     *         in="path",
     *         description="ID du type de don",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="label", type="string", example="Don financier", description="Libellé du type de don"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Don en argent pour soutenir nos actions", description="Description détaillée du type de don")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type de don modifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="enfiler Type updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                 @OA\Property(property="slug", type="string", example="E-TYPE-12345678-1234-1234-1234-123456789abc"),
     *                 @OA\Property(property="label", type="string", example="Don financier"),
     *                 @OA\Property(property="description", type="string", example="Don en argent pour soutenir nos actions"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de don non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Type de don not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function update(EnfileTypeRequest $request, EnfilerType $enfilerType)
    {
        $data = $request->all();
        try {
            $enfilerType->update($data);
            return $this->sendResponse(new EnfilerTypeResource($enfilerType), 'enfiler Type updated successfully');
        } catch (Exception $th) {
            return $this->sendError('Error updating enfiler Type');
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/enfiler-types/{enfilerType}",
     *     operationId="desactivateTypeDon",
     *     tags={"Types de Dons"},
     *     summary="Désactiver un type de don",
     *     description="Désactiver un type de don (soft delete en mettant is_active à false)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="enfilerType",
     *         in="path",
     *         description="ID du type de don",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type de don désactivé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="enfiler Type deleted successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de don non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Type de don not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function desactivate(EnfilerType $enfilerType)
    {
        try {
            $enfilerType->is_active = false;
            $enfilerType->save();
            return $this->sendResponse([], 'enfiler Type deleted successfully');
        } catch (Exception $th) {
            return $this->sendError('Error deleting enfiler Type');
        }
    }
}
