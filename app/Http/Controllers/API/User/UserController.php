<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserFormRequest;
use App\Http\Resources\User\UserResource;
use App\Mail\UserMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Utilisateurs",
 *     description="API Endpoints pour la gestion des utilisateurs"
 * )
 */
class UserController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     operationId="indexUsers",
     *     tags={"Utilisateurs"},
     *     summary="Récupérer tous les utilisateurs",
     *     description="Récupérer une liste paginée des utilisateurs avec recherche optionnelle",
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
     *         description="Terme de recherche (nom, email)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateurs récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Users retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                     @OA\Property(property="slug", type="string", example="USER-12345678-1234-1234-1234-123456789abc"),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *
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
        $query = User::query();

        if ($request->has('q') && $request->q) {
            $q = $request->q;
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('name', 'like', "%$q%")
                  //  ->orWhere('phone', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 10);

        $enfilers = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->sendResponse(
            [
                'items' => UserResource::collection($enfilers),

                'pagination' => [
                    'total' => $enfilers->total(),
                    'per_page' => $enfilers->perPage(),
                    'current_page' => $enfilers->currentPage(),
                    'last_page' => $enfilers->lastPage(),
                ]

            ],
            'Users retrieved successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{user}",
     *     operationId="showUser",
     *     tags={"Utilisateurs"},
     *     summary="Récupérer un utilisateur spécifique",
     *     description="Récupérer les détails d'un utilisateur par son ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="object",
     *                     @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                     @OA\Property(property="slug", type="string", example="USER-12345678-1234-1234-1234-123456789abc"),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found")
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
    public function show(User $user)
    {
        return $this->sendResponse( ['items' => new UserResource($user)], 'User retrieved successfully', [], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users",
     *     operationId="storeUser",
     *     tags={"Utilisateurs"},
     *     summary="Créer un nouvel utilisateur",
     *     description="Créer un nouvel utilisateur dans le système",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe", description="Nom complet de l'utilisateur"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com", description="Adresse email de l'utilisateur"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="Mot de passe de l'utilisateur"),
     *
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                 @OA\Property(property="slug", type="string", example="USER-12345678-1234-1234-1234-123456789abc"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),

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
    public function store(UserFormRequest $request)
    {
        $data = $request->all();
        if (isset($data['role']) && $data['role'] === 'admin') {
            $data['slug'] = 'ADM-' . Str::uuid();
        } else {
            $data['slug'] = 'USER-' . Str::uuid();
        }
        $data['password'] = Hash::make(Str::random(10));

        try {
            $user = User::create($data);

            Mail::to($user->email)->send(new UserMail($user));
            Log::info("User created successfully: " . $user->email);
            return $this->sendResponse([], 'User created successfully', [], 201);
        } catch (Exception $th) {
            Log::info("Error creating user: " . $th->getMessage());
            return $this->sendError('Error creating user');
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{user}",
     *     operationId="updateUser",
     *     tags={"Utilisateurs"},
     *     summary="Modifier un utilisateur",
     *     description="Modifier les informations d'un utilisateur existant (mot de passe, is_active et slug ne peuvent pas être modifiés)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe", description="Nom complet de l'utilisateur"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com", description="Adresse email de l'utilisateur"),

     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur modifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="object",
     *                     @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                     @OA\Property(property="slug", type="string", example="USER-12345678-1234-1234-1234-123456789abc"),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found")
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
    public function update(UserFormRequest $request, User $user)
    {
        $data = collect($request->all())->except('password', 'is_active', 'slug')->toArray();

        try {
            $user->update($data);
            Log::info('User updated successfully');
            return $this->sendResponse(['items'=> new UserResource($user)], 'User updated successfully');
        } catch (Exception $th) {
            Log::info("Error updating user: " . $th->getMessage());
            return $this->sendError('Error updating user');
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/users/{user}",
     *     operationId="desactivateUser",
     *     tags={"Utilisateurs"},
     *     summary="Désactiver un utilisateur",
     *     description="Désactiver un utilisateur (soft delete en mettant is_active à false)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur désactivé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User deleted successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found")
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
    public function desactivate(User $user)
    {
        try {
            $user->is_active = false;
            $user->save();

            //  Mail::to($user->email)->send(new UserMail($user));
            return $this->sendResponse([], 'User deleted successfully');
        } catch (Exception $th) {
            Log::info("Error deleting user: " . $th->getMessage());
            return $this->sendError('Error deleting user');
        }
    }
}
