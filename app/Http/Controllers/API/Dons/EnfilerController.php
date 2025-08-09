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
     *         name="type",
     *         in="query",
     *         description="Filtrer par type (individual ou company)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"individual", "company"})
     *     ),
     *     @OA\Parameter(
     *         name="donationType",
     *         in="query",
     *         description="Filtrer par type de don",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Filtrer par statut actif",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="has_motivation",
     *         in="query",
     *         description="Filtrer par présence de motivation",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Terme de recherche pour nom, email, téléphone, motivation, etc.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dons récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Dons récupérés avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                     @OA\Property(property="slug", type="string", example="ENF-12345678-1234-1234-1234-123456789abc"),
     *                     @OA\Property(property="type", type="string", example="individual"),
     *                     @OA\Property(property="name", type="string", example="Jean Dupont"),
     *                     @OA\Property(property="bossName", type="string", nullable=true, example="Pierre Dupont"),
     *                     @OA\Property(property="donationType", type="string", example="Financier"),
     *                     @OA\Property(property="email", type="string", example="jean@example.com"),
     *                     @OA\Property(property="phone", type="string", example="+33123456789"),
     *                     @OA\Property(property="motivation", type="string", nullable=true, example="Pour soutenir votre cause"),
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
        $query = Enfiler::query();

        // Filtrage par type
        if ($request->has('type') && $request->type) {
            $query->byType($request->type);
        }

        // Filtrage par type de don
        if ($request->has('donationType') && $request->donationType) {
            $query->byDonationType($request->donationType);
        }

        // Filtrage par statut actif
        if ($request->has('is_active') && $request->is_active !== null) {
            if ($request->is_active) {
                $query->active();
            } else {
                $query->inactive();
            }
        } else {
            // Par défaut, on ne retourne que les actifs
            $query->active();
        }

        // Filtrage par présence de motivation
        if ($request->has('has_motivation') && $request->has_motivation !== null) {
            if ($request->has_motivation) {
                $query->withMotivation();
            } else {
                $query->withoutMotivation();
            }
        }

        // Recherche textuelle
        if ($request->has('q') && $request->q) {
            $q = $request->q;
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('name', 'like', "%$q%")
                    ->orWhere('bossName', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%")
                    ->orWhere('donationType', 'like', "%$q%")
                    ->orWhere('motivation', 'like', "%$q%");
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
                ]
            ],
            'Dons récupérés avec succès'
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
     *             required={"name", "email", "type", "donationType"},
     *             @OA\Property(property="type", type="string", enum={"individual", "company"}, example="individual", description="Type de donateur"),
     *             @OA\Property(property="name", type="string", example="Jean Dupont", description="Nom du donateur"),
     *             @OA\Property(property="bossName", type="string", nullable=true, example="Pierre Dupont", description="Nom du responsable (pour les entreprises)"),
     *             @OA\Property(property="donationType", type="string", example="Financier", description="Type de don"),
     *             @OA\Property(property="email", type="string", format="email", example="jean@example.com", description="Email du donateur"),
     *             @OA\Property(property="phone", type="string", nullable=true, example="+33123456789", description="Téléphone du donateur"),
     *             @OA\Property(property="motivation", type="string", nullable=true, example="Pour soutenir votre cause", description="Motivation du don")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Don créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Don créé avec succès"),
     *             @OA\Property(property="data", type="array", @OA\Items())
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
            try {
                Mail::to($enfiler->email)->send(new EnfilerMail($enfiler));
                Log::info('Email sent to '. $enfiler->email);
            } catch (Exception $emailException) {
                Log::warning("Email notification failed for enfiler {$enfiler->email}: " . $emailException->getMessage());
            }

            // mail de notification a l'admin
            try {
                Mail::to(env('MAIL_FROM_ADDRESS'))->send(new EnfilerMail($enfiler, true));
                Log::info('email sent to the owner');
            } catch (Exception $adminEmailException) {
                Log::warning("Admin email notification failed: " . $adminEmailException->getMessage());
            }

            return $this->sendResponse([], 'Don créé avec succès', [], 201);
        } catch (Exception $th) {
            Log::error("Error creating don: " . $th->getMessage());
            return $this->sendError('Erreur lors de la création du don', [], 500);
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
     *             @OA\Property(property="message", type="string", example="Don récupéré avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="object",
     *                     @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                     @OA\Property(property="slug", type="string", example="ENF-12345678-1234-1234-1234-123456789abc"),
     *                     @OA\Property(property="type", type="string", example="individual"),
     *                     @OA\Property(property="name", type="string", example="Jean Dupont"),
     *                     @OA\Property(property="bossName", type="string", nullable=true, example="Pierre Dupont"),
     *                     @OA\Property(property="donationType", type="string", example="Financier"),
     *                     @OA\Property(property="email", type="string", example="jean@example.com"),
     *                     @OA\Property(property="phone", type="string", nullable=true, example="+33123456789"),
     *                     @OA\Property(property="motivation", type="string", nullable=true, example="Pour soutenir votre cause"),
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
        return $this->sendResponse(['items' => new EnfilerResource($enfiler)], 'Don récupéré avec succès');
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
     *             @OA\Property(property="message", type="string", example="Don désactivé avec succès"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Don non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Don non trouvé")
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
            $enfiler->deactivate();

            return $this->sendResponse([], 'Don désactivé avec succès');
        } catch (Exception $th) {
            Log::error("Error deactivating enfiler: " . $th->getMessage());
            return $this->sendError('Erreur lors de la désactivation du don');
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/dons/{enfiler}/activate",
     *     operationId="activateDon",
     *     tags={"Dons"},
     *     summary="Activer un don",
     *     description="Activer un don en définissant is_active à true",
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
     *         description="Don activé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Don activé avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Don non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Don non trouvé")
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
    public function activate(Enfiler $enfiler)
    {
        try {
            $enfiler->activate();
            return $this->sendResponse([], 'Don activé avec succès');
        } catch (Exception $th) {
            Log::error("Error activating enfiler: " . $th->getMessage());
            return $this->sendError('Erreur lors de l\'activation du don');
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/dons/{enfiler}/toggle-active",
     *     operationId="toggleActiveDon",
     *     tags={"Dons"},
     *     summary="Basculer le statut actif",
     *     description="Basculer le statut actif d'un don",
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
     *         description="Statut basculé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Statut basculé avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Don non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Don non trouvé")
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
    public function toggleActive(Enfiler $enfiler)
    {
        try {
            $enfiler->toggleActive();
            return $this->sendResponse([], 'Statut basculé avec succès');
        } catch (Exception $th) {
            Log::error("Error toggling enfiler status: " . $th->getMessage());
            return $this->sendError('Erreur lors du basculement du statut');
        }
    }
}
