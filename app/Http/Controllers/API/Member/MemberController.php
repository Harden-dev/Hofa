<?php

namespace App\Http\Controllers\API\Member;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\MemberRequest;
use App\Http\Resources\Member\MemberResource;
use App\Mail\MemberNotification;
use App\Models\Member;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Membres",
 *     description="API Endpoints pour la gestion des membres"
 * )
 */
class MemberController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/membres",
     *     operationId="indexMembres",
     *     tags={"Membres"},
     *     summary="Récupérer tous les membres",
     *     description="Récupérer une liste paginée des membres avec recherche optionnelle",
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
     *         description="Terme de recherche pour nom, email, téléphone, nationalité, etc.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type (individual ou company)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"individual", "company"})
     *     ),
     *     @OA\Parameter(
     *         name="is_volunteer",
     *         in="query",
     *         description="Filtrer par statut bénévole",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Filtrer par statut actif",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Membres récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Membres récupérés avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                     @OA\Property(property="slug", type="string", example="MEM-12345678-1234-1234-1234-123456789abc"),
     *                     @OA\Property(property="type", type="string", example="individual"),
     *                     @OA\Property(property="name", type="string", example="Jean Dupont"),
     *                     @OA\Property(property="bossName", type="string", nullable=true, example="Pierre Dupont"),
     *                     @OA\Property(property="email", type="string", example="jean.dupont@example.com"),
     *                     @OA\Property(property="phone", type="string", example="+33123456789"),
     *                     @OA\Property(property="gender", type="string", nullable=true, example="Masculin"),
     *                     @OA\Property(property="nationality", type="string", nullable=true, example="Française"),
     *                     @OA\Property(property="matrimonial", type="string", nullable=true, example="Marié"),
     *                     @OA\Property(property="is_volunteer", type="boolean", example=true),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="habit", type="string", nullable=true, example="Paris"),
     *                     @OA\Property(property="bio", type="string", nullable=true, example="Biographie du membre"),
     *                     @OA\Property(property="job", type="string", nullable=true, example="Ingénieur"),
     *                     @OA\Property(property="volunteer", type="string", nullable=true, example="Bénévole actif"),
     *                     @OA\Property(property="origin", type="string", nullable=true, example="France"),
     *                     @OA\Property(property="web", type="string", nullable=true, example="https://example.com"),
     *                     @OA\Property(property="activity", type="string", nullable=true, example="Activités du membre"),
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
        $query = Member::query();

        // Filtrage par type
        if ($request->has('type') && $request->type) {
            $query->byType($request->type);
        }

        // Filtrage par statut bénévole
        if ($request->has('is_volunteer') && $request->is_volunteer !== null) {
            if ($request->is_volunteer) {
                $query->volunteers();
            } else {
                $query->nonVolunteers();
            }
        }

        // Filtrage par statut actif
        if ($request->has('is_active') && $request->is_active !== null) {
            if ($request->is_active) {
                $query->active();
            } else {
                $query->inactive();
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
                    ->orWhere('nationality', 'like', "%$q%")
                    ->orWhere('matrimonial', 'like', "%$q%")
                    ->orWhere('habit', 'like', "%$q%")
                    ->orWhere('bio', 'like', "%$q%")
                    ->orWhere('job', 'like', "%$q%")
                    ->orWhere('volunteer', 'like', "%$q%")
                    ->orWhere('origin', 'like', "%$q%")
                    ->orWhere('activity', 'like', "%$q%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 10);

        $members = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->sendResponse(
            [
                'items' => MemberResource::collection($members),
                'pagination' => [
                    'total' => $members->total(),
                    'per_page' => $members->perPage(),
                    'current_page' => $members->currentPage(),
                    'last_page' => $members->lastPage(),
                ]
            ],
            'Membres récupérés avec succès'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/membres/{member}",
     *     operationId="showMembre",
     *     tags={"Membres"},
     *     summary="Récupérer un membre spécifique",
     *     description="Récupérer les détails d'un membre par son ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="member",
     *         in="path",
     *         description="ID du membre",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Membre récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Membre récupéré avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="object",
     *                     @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                     @OA\Property(property="slug", type="string", example="MEM-12345678-1234-1234-1234-123456789abc"),
     *                     @OA\Property(property="type", type="string", example="individual"),
     *                     @OA\Property(property="name", type="string", example="Jean Dupont"),
     *                     @OA\Property(property="bossName", type="string", nullable=true, example="Pierre Dupont"),
     *                     @OA\Property(property="email", type="string", example="jean.dupont@example.com"),
     *                     @OA\Property(property="phone", type="string", example="+33123456789"),
     *                     @OA\Property(property="gender", type="string", nullable=true, example="Masculin"),
     *                     @OA\Property(property="nationality", type="string", nullable=true, example="Française"),
     *                     @OA\Property(property="matrimonial", type="string", nullable=true, example="Marié"),
     *                     @OA\Property(property="is_volunteer", type="boolean", example=true),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="habit", type="string", nullable=true, example="Paris"),
     *                     @OA\Property(property="bio", type="string", nullable=true, example="Biographie du membre"),
     *                     @OA\Property(property="job", type="string", nullable=true, example="Ingénieur"),
     *                     @OA\Property(property="volunteer", type="string", nullable=true, example="Bénévole actif"),
     *                     @OA\Property(property="origin", type="string", nullable=true, example="France"),
     *                     @OA\Property(property="web", type="string", nullable=true, example="https://example.com"),
     *                     @OA\Property(property="activity", type="string", nullable=true, example="Activités du membre"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Membre non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Membre non trouvé")
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
    public function show(Member $member)
    {
        return $this->sendResponse(
            ['items' => new MemberResource($member)],
            'Membre récupéré avec succès'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/membres",
     *     operationId="storeMembre",
     *     tags={"Membres"},
     *     summary="Créer un nouveau membre",
     *     description="Créer un nouveau membre et envoyer un email de confirmation",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type", "name", "email", "phone"},
     *             @OA\Property(property="type", type="string", example="individual", description="Type de membre (individual ou company)", enum={"individual", "company"}),
     *             @OA\Property(property="name", type="string", example="Jean Dupont", description="Nom du membre"),
     *             @OA\Property(property="bossName", type="string", nullable=true, example="Pierre Dupont", description="Nom du responsable (pour les entreprises)"),
     *             @OA\Property(property="email", type="string", format="email", example="jean.dupont@example.com", description="Email du membre"),
     *             @OA\Property(property="phone", type="string", example="+33123456789", description="Téléphone du membre"),
     *             @OA\Property(property="gender", type="string", nullable=true, example="Masculin", description="Genre"),
     *             @OA\Property(property="nationality", type="string", nullable=true, example="Française", description="Nationalité"),
     *             @OA\Property(property="matrimonial", type="string", nullable=true, example="Marié", description="Statut matrimonial"),
     *             @OA\Property(property="is_volunteer", type="boolean", example=true, description="Est-ce un bénévole ?"),
     *             @OA\Property(property="habit", type="string", nullable=true, example="Paris", description="Lieu de résidence"),
     *             @OA\Property(property="bio", type="string", nullable=true, example="Biographie du membre", description="Biographie"),
     *             @OA\Property(property="job", type="string", nullable=true, example="Ingénieur", description="Profession"),
     *             @OA\Property(property="volunteer", type="string", nullable=true, example="Bénévole actif", description="Description du bénévolat"),
     *             @OA\Property(property="origin", type="string", nullable=true, example="France", description="Origine"),
     *             @OA\Property(property="web", type="string", nullable=true, example="https://example.com", description="Site web"),
     *             @OA\Property(property="activity", type="string", nullable=true, example="Activités du membre", description="Activités")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Membre créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Membre créé avec succès"),
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
   public function store(MemberRequest $request)
   {
    $data = $request->all();
    try {
        $data['slug'] = Str::uuid();
        $data['is_approved'] = false;
        $data['is_rejected'] = false;

        $member = Member::create($data);
        return $this->sendResponse([], 'Membre créé avec succès',[], 201);
        Log::info('Member created successfully');
    } catch (Exception $th) {
        Log::error('Error creating member: ' . $th->getMessage());
        return $this->sendError('Erreur lors de la création du membre', [], 500);
    }
   }

    /**
     * @OA\Put(
     *     path="/api/v1/membres/{member}",
     *     operationId="updateMembre",
     *     tags={"Membres"},
     *     summary="Modifier un membre",
     *     description="Modifier les informations d'un membre existant",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="member",
     *         in="path",
     *         description="ID du membre",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", example="individual", description="Type de membre (individual ou company)", enum={"individual", "company"}),
     *             @OA\Property(property="name", type="string", example="Jean Dupont", description="Nom du membre"),
     *             @OA\Property(property="bossName", type="string", nullable=true, example="Pierre Dupont", description="Nom du responsable (pour les entreprises)"),
     *             @OA\Property(property="email", type="string", format="email", example="jean.dupont@example.com", description="Email du membre"),
     *             @OA\Property(property="phone", type="string", example="+33123456789", description="Téléphone du membre"),
     *             @OA\Property(property="gender", type="string", nullable=true, example="Masculin", description="Genre"),
     *             @OA\Property(property="nationality", type="string", nullable=true, example="Française", description="Nationalité"),
     *             @OA\Property(property="matrimonial", type="string", nullable=true, example="Marié", description="Statut matrimonial"),
     *             @OA\Property(property="is_volunteer", type="boolean", example=true, description="Est-ce un bénévole ?"),
     *             @OA\Property(property="habit", type="string", nullable=true, example="Paris", description="Lieu de résidence"),
     *             @OA\Property(property="bio", type="string", nullable=true, example="Biographie du membre", description="Biographie"),
     *             @OA\Property(property="job", type="string", nullable=true, example="Ingénieur", description="Profession"),
     *             @OA\Property(property="volunteer", type="string", nullable=true, example="Bénévole actif", description="Description du bénévolat"),
     *             @OA\Property(property="origin", type="string", nullable=true, example="France", description="Origine"),
     *             @OA\Property(property="web", type="string", nullable=true, example="https://example.com", description="Site web"),
     *             @OA\Property(property="activity", type="string", nullable=true, example="Activités du membre", description="Activités")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Membre modifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Membre modifié avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                 @OA\Property(property="slug", type="string", example="MEM-12345678-1234-1234-1234-123456789abc"),
     *                 @OA\Property(property="type", type="string", example="individual"),
     *                 @OA\Property(property="name", type="string", example="Jean Dupont"),
     *                 @OA\Property(property="bossName", type="string", nullable=true, example="Pierre Dupont"),
     *                 @OA\Property(property="email", type="string", example="jean.dupont@example.com"),
     *                 @OA\Property(property="phone", type="string", example="+33123456789"),
     *                 @OA\Property(property="gender", type="string", nullable=true, example="Masculin"),
     *                 @OA\Property(property="nationality", type="string", nullable=true, example="Française"),
     *                 @OA\Property(property="matrimonial", type="string", nullable=true, example="Marié"),
     *                 @OA\Property(property="is_volunteer", type="boolean", example=true),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="habit", type="string", nullable=true, example="Paris"),
     *                 @OA\Property(property="bio", type="string", nullable=true, example="Biographie du membre"),
     *                 @OA\Property(property="job", type="string", nullable=true, example="Ingénieur"),
     *                 @OA\Property(property="volunteer", type="string", nullable=true, example="Bénévole actif"),
     *                 @OA\Property(property="origin", type="string", nullable=true, example="France"),
     *                 @OA\Property(property="web", type="string", nullable=true, example="https://example.com"),
     *                 @OA\Property(property="activity", type="string", nullable=true, example="Activités du membre"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Membre non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Membre non trouvé")
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
    public function update(MemberRequest $request, Member $member)
    {
        $data = $request->all();
        try {
            $member->update($data);
            Log::info("Member updated successfully");
            return $this->sendResponse(new MemberResource($member), 'Membre modifié avec succès');
        } catch (Exception $th) {
            Log::info("Error updating member: " . $th->getMessage());
            return $this->sendError('Erreur lors de la modification du membre');
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/membres/{member}",
     *     operationId="desactivateMembre",
     *     tags={"Membres"},
     *     summary="Désactiver un membre",
     *     description="Supprimer un membre en définissant is_active à false",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="member",
     *         in="path",
     *         description="ID du membre",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Membre désactivé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Membre désactivé avec succès"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Membre non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Membre non trouvé")
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
    public function desactivate(Member $member)
    {
        try {
            $member->deactivate();

            return $this->sendResponse([], 'Membre désactivé avec succès');
        } catch (Exception $th) {
            Log::info("Error deleting member: " . $th->getMessage());
            return $this->sendError('Erreur lors de la désactivation du membre');
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/membres/{member}/activate",
     *     operationId="activateMembre",
     *     tags={"Membres"},
     *     summary="Activer un membre",
     *     description="Activer un membre en définissant is_active à true",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="member",
     *         in="path",
     *         description="ID du membre",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Membre activé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Membre activé avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Membre non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Membre non trouvé")
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
    public function activate(Member $member)
    {
        try {
            $member->activate();
            return $this->sendResponse([], 'Membre activé avec succès');
        } catch (Exception $th) {
            Log::info("Error activating member: " . $th->getMessage());
            return $this->sendError('Erreur lors de l\'activation du membre');
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/membres/{member}/toggle-volunteer",
     *     operationId="toggleVolunteerMembre",
     *     tags={"Membres"},
     *     summary="Basculer le statut bénévole",
     *     description="Basculer le statut bénévole d'un membre",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="member",
     *         in="path",
     *         description="ID du membre",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statut bénévole basculé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Statut bénévole basculé avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Membre non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Membre non trouvé")
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
    public function toggleVolunteer(Member $member)
    {
        try {
            $member->toggleVolunteer();
            return $this->sendResponse([], 'Statut bénévole basculé avec succès');
        } catch (Exception $th) {
            Log::info("Error toggling volunteer status: " . $th->getMessage());
            return $this->sendError('Erreur lors du basculement du statut bénévole');
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/membres/{member}/approve",
     *     operationId="approveMembre",
     *     tags={"Membres"},
     *     summary="Approuver un membre",
     *     description="Approuver la demande d'inscription d'un membre",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="member",
     *         in="path",
     *         description="ID du membre",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="custom_message", type="string", nullable=true, example="Bienvenue dans notre communauté !", description="Message personnalisé pour le membre")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Membre approuvé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Membre approuvé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                 @OA\Property(property="is_approved", type="boolean", example=true),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="approved_at", type="string", format="date-time", example="2025-08-01T10:30:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Membre non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Membre non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Membre déjà approuvé ou rejeté",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Ce membre a déjà été traité")
     *         )
     *     )
     * )
     */
    public function approve(Request $request, Member $member)
    {
        try {
            if (!$member) {
                return $this->sendError('Membre non trouvé', [], 404);
            }
            // Vérifier si le membre a déjà été traité
            if ($member->is_approved || $member->is_rejected) {
                return $this->sendError('Ce membre a déjà été traité (approuvé ou rejeté)', [], 400);
            }

            // Mettre à jour le statut d'approbation
            $member->update([
                'is_approved' => true,
                'is_rejected' => false,
                'is_active' => true, // Activer le compte quand approuvé
                'approved_at' => now(),
                'rejected_at' => null,
                'rejection_reason' => null
            ]);

            // Message personnalisé optionnel
            $customMessage = $request->input('custom_message');

            // Email de notification au membre
            try {
                Mail::to($member->email)->send(new MemberNotification($member, false, 'approved', $customMessage));
            } catch (Exception $emailException) {
                Log::warning("Approval email notification failed for member {$member->email}: " . $emailException->getMessage());
            }

            // Email de notification à l'admin
            try {
                Mail::to(env('MAIL_FROM_ADDRESS'))->send(new MemberNotification($member, true, 'approved', $customMessage));
            } catch (Exception $adminEmailException) {
                Log::warning("Admin approval notification failed: " . $adminEmailException->getMessage());
            }

            return $this->sendResponse([
                'id' => $member->id,
                'is_approved' => $member->is_approved,
                'is_active' => $member->is_active,
                'approved_at' => $member->approved_at
            ], 'Membre approuvé avec succès');

        } catch (Exception $th) {
            Log::error("Error approving member {$member->id}: " . $th->getMessage());
            return $this->sendError('Erreur lors de l\'approbation du membre');
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/membres/{member}/reject",
     *     operationId="rejectMembre",
     *     tags={"Membres"},
     *     summary="Rejeter un membre",
     *     description="Rejeter la demande d'inscription d'un membre",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="member",
     *         in="path",
     *         description="ID du membre",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rejection_reason"},
     *             @OA\Property(property="rejection_reason", type="string", example="Informations incomplètes", description="Raison du rejet"),
     *             @OA\Property(property="custom_message", type="string", nullable=true, example="Nous ne pouvons pas accepter votre demande pour le moment.", description="Message personnalisé pour le membre")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Membre rejeté avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Membre rejeté avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                 @OA\Property(property="is_rejected", type="boolean", example=true),
     *                 @OA\Property(property="is_active", type="boolean", example=false),
     *                 @OA\Property(property="rejected_at", type="string", format="date-time", example="2025-08-01T10:30:00.000000Z"),
     *                 @OA\Property(property="rejection_reason", type="string", example="Informations incomplètes")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Membre non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Membre non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Membre déjà approuvé ou rejeté",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Ce membre a déjà été traité")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données de validation invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="La raison du rejet est requise")
     *         )
     *     )
     * )
     */
    public function reject(Request $request, Member $member)
    {
        try {
            if (!$member) {
                return $this->sendError('Membre non trouvé', [], 404);
            }

            // Validation de la raison du rejet
            $request->validate([
                'rejection_reason' => 'required|string|max:500'
            ], [
                'rejection_reason.required' => 'La raison du rejet est requise',
                'rejection_reason.string' => 'La raison du rejet doit être une chaîne de caractères',
                'rejection_reason.max' => 'La raison du rejet ne peut pas dépasser 500 caractères'
            ]);

            // Vérifier si le membre a déjà été traité
            if ($member->is_approved || $member->is_rejected) {
                return $this->sendError('Ce membre a déjà été traité (approuvé ou rejeté)');
            }

            // Mettre à jour le statut de rejet
            $member->update([
                'is_approved' => false,
                'is_rejected' => true,
                'is_active' => false, // Garder le compte inactif quand rejeté
                'rejected_at' => now(),
                'approved_at' => null,
                'rejection_reason' => $request->rejection_reason
            ]);

            // Message personnalisé optionnel
            $customMessage = $request->input('custom_message');

            // Email de notification au membre
            try {
                Mail::to($member->email)->send(new MemberNotification($member, false, 'rejected', $customMessage, $request->rejection_reason));
            } catch (Exception $emailException) {
                Log::warning("Rejection email notification failed for member {$member->email}: " . $emailException->getMessage());
            }

            // Email de notification à l'admin
            try {
                Mail::to(env('MAIL_FROM_ADDRESS'))->send(new MemberNotification($member, true, 'rejected', $customMessage, $request->rejection_reason));
            } catch (Exception $adminEmailException) {
                Log::warning("Admin rejection notification failed: " . $adminEmailException->getMessage());
            }

            return $this->sendResponse([
                'id' => $member->id,
                'is_rejected' => $member->is_rejected,
                'is_active' => $member->is_active,
                'rejected_at' => $member->rejected_at,
                'rejection_reason' => $member->rejection_reason
            ], 'Membre rejeté avec succès');

        } catch (ValidationException $e) {
            return $this->sendError('Erreur de validation', $e->errors(), 422);
        } catch (Exception $th) {
            Log::error("Error rejecting member {$member->id}: " . $th->getMessage());
            return $this->sendError('Erreur lors du rejet du membre');
        }
    }
}
