<?php

namespace App\Http\Controllers\API\Dons;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Enfiler\EnfilerFormRequest;
use App\Http\Resources\Dons\EnfilerResource;
use App\Mail\EnfilerMail;
use App\Models\Enfiler;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Dons",
 *     description="API Endpoints pour la gestion des dons"
 * )
 */
class EnfilerController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/dons",
     *     operationId="indexDons",
     *     tags={"Dons"},
     *     summary="Récupérer tous les dons",
     *     description="Récupérer une liste paginée des dons avec filtrage et recherche optionnels",
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
     *         description="Filtrer par statut du don",
     *         required=false,
     *         @OA\Schema(type="string", enum={"activated", "desactivated"}, default="activated")
     *     ),
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Terme de recherche pour nom, téléphone et email",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dons récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Enfilers retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                     @OA\Property(property="slug", type="string", example="ENF-12345678-1234-1234-1234-123456789abc"),
     *                     @OA\Property(property="name", type="string", example="Jean Dupont"),
     *                     @OA\Property(property="email", type="string", example="jean@example.com"),
     *                     @OA\Property(property="phone", type="string", example="+33123456789"),
     *                    
     *                     @OA\Property(property="type_enfiler", type="object",
     *                         @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                         @OA\Property(property="name", type="string", example="Don financier"),
     *                         @OA\Property(property="description", type="string", example="Don en argent")
     *                     ),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z")
     *                 )),
     *                 @OA\Property(property="pagination", type="object",
     *                     @OA\Property(property="total", type="integer", example=50),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=5)
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
        $query = Enfiler::query()->with('type_enfiler');
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
                $subQuery->where('name', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 10);

        $enfilers = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->sendResponse(
            [
                'items' => EnfilerResource::collection($enfilers),

                'pagination' => [
                    'total' => $enfilers->total(),
                    'per_page' => $enfilers->perPage(),
                    'current_page' => $enfilers->currentPage(),
                    'last_page' => $enfilers->lastPage(),
                ],

            ],
            'Enfilers retrieved successfully'

        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/dons",
     *     operationId="storeDon",
     *     tags={"Dons"},
     *     summary="Créer un nouveau don",
     *     description="Créer un nouveau don et envoyer un email de confirmation",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email","enfiler_type_id"},
     *             @OA\Property(property="name", type="string", example="Jean Dupont", description="Nom du donateur"),
     *             @OA\Property(property="email", type="string", format="email", example="jean@example.com", description="Email du donateur"),
     *             @OA\Property(property="phone", type="string", nullable=true, example="+33123456789", description="Téléphone du donateur"),
     *            
     *             @OA\Property(property="enfiler_type_id", type="string", example="01jywbsemp4vdwx02h17z5mgah", description="ID du type de don"),
     *             @OA\Property(property="message", type="string", nullable=true, example="Merci pour votre travail", description="Message accompagnant le don")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Don créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Don created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                 @OA\Property(property="slug", type="string", example="ENF-12345678-1234-1234-1234-123456789abc"),
     *                 @OA\Property(property="name", type="string", example="Jean Dupont"),
     *                 @OA\Property(property="email", type="string", example="jean@example.com"),
     *                 @OA\Property(property="phone", type="string", nullable=true, example="+33123456789"),
     *                
     *                 @OA\Property(property="type_enfiler", type="object",
     *                     @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                     @OA\Property(property="name", type="string", example="Don financier"),
     *                     @OA\Property(property="description", type="string", example="Don en argent")
     *                 ),
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
     *     )
     * )
     */
    public function store(EnfilerFormRequest $request)
    {
        $data = $request->all();
        $data['slug'] = 'ENF-' . Str::uuid();
        try {
            $enfiler = Enfiler::create($data);

            // Mail de remerciement au donateur
            Mail::to($enfiler->email)->send(new EnfilerMail($enfiler));
            Log::info('Email sent to '. $enfiler->email);
            // mail de notification a l'admin

            Mail::to(env('MAIL_FROM_ADDRESS'))->send(new EnfilerMail($enfiler, true));
            Log::info('email sent to the owner');

            return $this->sendResponse(new EnfilerResource($enfiler), 'Don created successfully');
        } catch (Exception $th) {
            Log::info("Error creating don: " . $th->getMessage());
            return $this->sendError('Error creating don');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dons/{enfiler}",
     *     operationId="showDon",
     *     tags={"Dons"},
     *     summary="Récupérer un don spécifique",
     *     description="Récupérer les détails d'un don par son ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="enfiler",
     *         in="path",
     *         description="ID du don",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Don récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Enfiler retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="object",
     *                     @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                     @OA\Property(property="slug", type="string", example="ENF-12345678-1234-1234-1234-123456789abc"),
     *                     @OA\Property(property="name", type="string", example="Jean Dupont"),
     *                     @OA\Property(property="email", type="string", example="jean@example.com"),
     *                     @OA\Property(property="phone", type="string", nullable=true, example="+33123456789"),
     *                    
     *                     @OA\Property(property="type_enfiler", type="object",
     *                         @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                         @OA\Property(property="name", type="string", example="Don financier"),
     *                         @OA\Property(property="description", type="string", example="Don en argent")
     *                     ),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Don non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Don not found")
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
    public function show(Enfiler $enfiler)
    {
        return $this->sendResponse(['items' => new EnfilerResource($enfiler)], 'Enfiler retrieved successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/dons/{enfiler}",
     *     operationId="desactivateDon",
     *     tags={"Dons"},
     *     summary="Désactiver un don",
     *     description="Supprimer un don en définissant is_active à false",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="enfiler",
     *         in="path",
     *         description="ID du don",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Don désactivé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Enfiler deleted successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Don non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Don not found")
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
    public function desactivate(Enfiler $enfiler)
    {
        try {
            $enfiler->is_active = false;
            $enfiler->save();

            return $this->sendResponse([], 'Enfiler deleted successfully');
        } catch (Exception $th) {
            return $this->sendError('Error deleting enfiler');
        }
    }
}
