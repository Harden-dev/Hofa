<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * @OA\SecurityScheme(
 *     securityScheme="Bearer",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Utilisez un jeton JWT pour l'authentification."
 * )
 */
class AuthController extends Controller
{

    /**
     * @OA\Schema(
     *     schema="User",
     *     type="object",
     *     title="Utilisateur",
     *     required={"id", "name", "email", "password"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="L'identifiant unique de l'utilisateur"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Nom de l'utilisateur"
     *     ),
     *     @OA\Property(
     *         property="last_name",
     *         type="string",
     *         description="Nom de famille de l'utilisateur"
     *     ),
     *
     *     @OA\Property(
     *         property="password",
     *         type="string",
     *         description="Le mot de passe"
     *     )
     * )
     */
    //protected $guard = 'api';


    /**
     * Get a JWT via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     tags={"Auth"},
     *     summary="Obtenir un JWT via les identifiants fournis",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="jean.dupont@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="JWT généré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants non valides",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Email ou mot de passe incorrect'], 401);
        }

        $user = Auth::guard('api')->user();

        // Vérifier si l'utilisateur doit changer son mot de passe temporaire

        if (!$user->is_password_modified) {
            Log::info('Utilisateur non connecté: ' . $user->email, 'mot de passe non modifié');
            return response()->json([
                'error' => 'Vous devez changer votre mot de passe temporaire',
                'requires_password_change' => true,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60
            ], 200);
        }

        return $this->respondWithToken($token);
    }


    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Get(
     *     path="/api/v1/me",
     *     tags={"Auth"},
     *     summary="Obtenir les informations de l'utilisateur connecté",
     *     security={{"Bearer": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informations de l'utilisateur récupérées avec succès",
     *
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé, jeton manquant ou invalide",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */

    public function me()
    {

        $user = Auth::guard('api')->user();

        return response()->json(
            $user
        );
    }
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */


    /**
     * @OA\Post(
     *     path="/api/v1/logout",
     *     tags={"Auth"},
     *     summary="Déconnexion de l'utilisateur",
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }



    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *     path="/api/v1/refresh",
     *     tags={"Auth"},
     *     summary="Rafraîchir le token JWT",
     *     @OA\Response(
     *         response=200,
     *         description="Token rafraîchi avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer")
     *         )
     *     )
     * )
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => Auth::guard('api')->user(),
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => Auth::factory()->getTTL() * 60
            ]

        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/change-password",
     *     tags={"Auth"},
     *     summary="Changer le mot de passe de l'utilisateur",
     * security={{"Bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="current_password", type="string", example="ancienMotDePasse123"),
     *             @OA\Property(property="new_password", type="string", example="nouveauMotDePasse123"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="nouveauMotDePasse123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe changé avec succès",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Mot de passe actuel incorrect",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur du serveur",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string|min:6',
            'new_password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                function ($attribute, $value, $fail) {
                    // Vérifie si le nouveau mot de passe est différent de l'ancien
                    if (Hash::check($value, Auth::user()->password)) {
                        $fail('Le nouveau mot de passe doit être différent de l\'ancien mot de passe.');
                    }
                }
            ],
        ]);

        $user = Auth::guard('api')->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Le mot de passe actuel est incorrect'], 401);
        }

        try {
            $user->password = Hash::make($request->new_password);
            $user->is_password_modified = true;
            $user->save();

            Log::info('Le mot de passe a été modifié avec succès pour l\'utilisateur: ' . $user->id);

            return response()->json(['message' => 'Le mot de passe a été modifié avec succès'], 200);
        } catch (Exception $e) {
            Log::error('Erreur lors de la modification du mot de passe : ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de la modification du mot de passe'], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/reset/password/mail",
     *     summary="Réinitialiser le mot de passe de l'utilisateur",
     *     description="Cette fonction permet de réinitialiser le mot de passe d'un utilisateur et d'envoyer un nouveau mot de passe par email.",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Mot de passe réinitialisé et envoyé par email",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Un nouveau mot de passe a été envoyé à votre adresse e-mail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="L'utilisateur n'existe pas")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Une erreur est survenue lors de la réinitialisation du mot de passe")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Le champ email est obligatoire.")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Adresse e-mail de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="string", format="email")
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */


    public function ResetPasswordMail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $user = User::where('email', $request->email)->first();
            if (!$user) {

                return response()->json(['error' => 'L\'utilisateur n\'existe pas'], 404);
            }

            if ($user->deleted_at) {

                return response()->json(['error' => 'L\'utilisateur est désactivé'], 403);
            }

            $newPassword = Str::random(8);

            $user->password = Hash::make($newPassword);
            $user->is_password_modified = false; // L'utilisateur devra changer le mot de passe
            $user->save();

            // Envoyer l'email avec le mot de passe temporaire
            Mail::to($user->email)->send(new PasswordResetMail($user, $newPassword));

            Log::info('Mot de passe temporaire envoyé à l\'utilisateur: ' . $user->email);

            return response()->json(['message' => 'Un nouveau mot de passe temporaire a été envoyé à votre adresse e-mail'], 200);
        } catch (\Exception $th) {

            Log::error('Une erreur est survenue lors de la réinitialisation du mot de passe : ' . $th->getMessage());
            return response()->json(['error' => "Une erreur est survenue lors de la réinitialisation du mot de passe"], 500);
        }
    }
}
