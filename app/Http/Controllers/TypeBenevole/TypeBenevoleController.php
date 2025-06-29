<?php

namespace App\Http\Controllers\TypeBenevole;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\TypeBenevole\TypeBenevoleRequest;
use App\Http\Resources\TypeBenevole\TypeBenevoleResource;
use App\Models\BenevoleType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mockery\Matcher\Type;

/**
 * @OA\Tag(
 *     name="Types de Bénévoles",
 *     description="API Endpoints pour la gestion des types de bénévoles"
 * )
 */
class TypeBenevoleController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/type-benevoles",
     *     operationId="indexTypesBenevoles",
     *     tags={"Types de Bénévoles"},
     *     summary="Récupérer tous les types de bénévoles",
     *     description="Récupérer une liste paginée des types de bénévoles avec recherche optionnelle",
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
     *         name="q",
     *         in="query",
     *         description="Terme de recherche pour le label",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Types de bénévoles récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Liste des types de benevoles"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                     @OA\Property(property="slug", type="string", example="TBV-12345678-1234-1234-1234-123456789abc"),
     *                     @OA\Property(property="label", type="string", example="Bénévole événementiel"),
     *                     @OA\Property(property="description", type="string", example="Bénévole pour les événements et manifestations"),
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
        //get all type benevoles
        $query = BenevoleType::query();

        if ($request->has('q') && $request->q) {
            $q = $request->q;
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('label', 'like', "%$q%");
            });
        }

        $perPage = $request->get('per_page', 10);

        $typeBenevoles = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->sendResponse(
            [
                'items' => TypeBenevoleResource::collection($typeBenevoles),
                'pagination' => [
                    'total' => $typeBenevoles->total(),
                    'per_page' => $typeBenevoles->perPage(),
                    'current_page' => $typeBenevoles->currentPage(),
                    'last_page' => $typeBenevoles->lastPage(),
                ]
            ],
            'Liste des types de benevoles'
        );
    }
    
    /**
     * @OA\Post(
     *     path="/api/v1/type-benevoles",
     *     operationId="storeTypeBenevole",
     *     tags={"Types de Bénévoles"},
     *     summary="Créer un nouveau type de bénévole",
     *     description="Créer un nouveau type de bénévole pour catégoriser les bénévoles",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"label"},
     *             @OA\Property(property="label", type="string", example="Bénévole événementiel", description="Libellé du type de bénévole"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Bénévole pour les événements et manifestations", description="Description détaillée du type de bénévole")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Type de bénévole créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Type benevole created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                 @OA\Property(property="slug", type="string", example="TBV-12345678-1234-1234-1234-123456789abc"),
     *                 @OA\Property(property="label", type="string", example="Bénévole événementiel"),
     *                 @OA\Property(property="description", type="string", example="Bénévole pour les événements et manifestations"),
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
    public function store(TypeBenevoleRequest $request)
    {
        //
        $data = $request->all();
        $data['slug'] = 'TBV-' . Str::uuid();

        try {
            $typeBenevole = BenevoleType::create($data);
            return $this->sendResponse(new TypeBenevoleResource($typeBenevole), 'Type benevole created successfully');
        } catch (Exception $th) {
            Log::info("Error creating type benevole: " . $th->getMessage());
            return $this->sendError('Error creating type benevole');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/type-benevoles/{typeBenevole}",
     *     operationId="showTypeBenevole",
     *     tags={"Types de Bénévoles"},
     *     summary="Récupérer un type de bénévole spécifique",
     *     description="Récupérer les détails d'un type de bénévole par son ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="typeBenevole",
     *         in="path",
     *         description="ID du type de bénévole",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type de bénévole récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Type benevole retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="object",
     *                     @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                     @OA\Property(property="slug", type="string", example="TBV-12345678-1234-1234-1234-123456789abc"),
     *                     @OA\Property(property="label", type="string", example="Bénévole événementiel"),
     *                     @OA\Property(property="description", type="string", example="Bénévole pour les événements et manifestations"),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de bénévole non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Type benevole not found")
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
    public function show(BenevoleType $typeBenevole)
    {
        return $this->sendResponse(['items' => new TypeBenevoleResource($typeBenevole)], 'Type benevole retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/type-benevoles/{typeBenevole}",
     *     operationId="updateTypeBenevole",
     *     tags={"Types de Bénévoles"},
     *     summary="Modifier un type de bénévole",
     *     description="Modifier les informations d'un type de bénévole existant",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="typeBenevole",
     *         in="path",
     *         description="ID du type de bénévole",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="label", type="string", example="Bénévole événementiel", description="Libellé du type de bénévole"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Bénévole pour les événements et manifestations", description="Description détaillée du type de bénévole")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type de bénévole modifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Type benevole updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="object",
     *                     @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                     @OA\Property(property="slug", type="string", example="TBV-12345678-1234-1234-1234-123456789abc"),
     *                     @OA\Property(property="label", type="string", example="Bénévole événementiel"),
     *                     @OA\Property(property="description", type="string", example="Bénévole pour les événements et manifestations"),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de bénévole non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Type benevole not found")
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
    public function update(TypeBenevoleRequest $request, BenevoleType $typeBenevole)
    {
        $data = $request->all();
        try {
            $typeBenevole->update($data);
            return $this->sendResponse(['items' => new TypeBenevoleResource($typeBenevole)], 'Type benevole updated successfully');
        } catch (Exception $th) {
            return $this->sendError('Error updating type benevole');
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/type-benevoles/{typeBenevole}",
     *     operationId="desactivateTypeBenevole",
     *     tags={"Types de Bénévoles"},
     *     summary="Désactiver un type de bénévole",
     *     description="Désactiver un type de bénévole (soft delete en mettant is_active à false)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="typeBenevole",
     *         in="path",
     *         description="ID du type de bénévole",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type de bénévole désactivé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Type benevole deleted successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de bénévole non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Type benevole not found")
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
    public function desactivate(BenevoleType $typeBenevole)
    {
        try {
            $typeBenevole->is_active = false;
            $typeBenevole->save();
            return $this->sendResponse([], 'Type benevole deleted successfully');
        } catch (Exception $th) {
            return $this->sendError('Error deleting type benevole');
        }
    }
}


