<?php

namespace App\Http\Controllers\API\Contact;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\ContactFormRequest;
use App\Http\Resources\Contact\ContactResource;
use App\Mail\ContactNotification;
use App\Models\Contact;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Contacts",
 *     description="API Endpoints for managing contacts"
 * )
 */
class ContactController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/contacts",
     *     operationId="indexContacts",
     *     tags={"Contacts"},
     *     summary="Get all contacts",
     *     description="Retrieve a paginated list of contacts with optional filtering and search",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by contact status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"activated", "desactivated"}, default="activated")
     *     ),
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search term for name and email",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contacts retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="contacts retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                     @OA\Property(property="slug", type="string", example="CONT-12345678-1234-1234-1234-123456789abc"),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="phone", type="string", nullable=true, example="+1234567890"),
     *                     @OA\Property(property="message", type="string", example="Contact message content"),
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
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        // Recherche
        $query = Contact::query();

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
                    // ->orWhere('phone', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 10);

        $contacts = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->sendResponse(
            [
                'items' => ContactResource::collection($contacts),
                'pagination' => [
                    'total' => $contacts->total(),
                    'per_page' => $contacts->perPage(),
                    'current_page' => $contacts->currentPage(),
                    'last_page' => $contacts->lastPage(),
                ]
            ],
            'contacts retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/contacts",
     *     operationId="storeContact",
     *     tags={"Contacts"},
     *     summary="Create a new contact",
     *     description="Create a new contact message and optionally send email notification",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "message"},
     *             @OA\Property(property="name", type="string", example="John Doe", description="Contact name"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com", description="Contact email"),
     *             @OA\Property(property="phone", type="string", nullable=true, example="+1234567890", description="Contact phone number"),
     *             @OA\Property(property="message", type="string", example="Hello, I would like to get more information about your services.", description="Contact message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Contact created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contact created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                 @OA\Property(property="slug", type="string", example="CONT-12345678-1234-1234-1234-123456789abc"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="phone", type="string", nullable=true, example="+1234567890"),
     *                 @OA\Property(property="message", type="string", example="Hello, I would like to get more information about your services."),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(ContactFormRequest $request)
    {
        $data = $request->all();
        $data['slug'] = 'CONT-'. Str::uuid();      
         try {
            $contact = Contact::create($data);
           
             Mail::to(env('MAIL_FROM_ADDRESS'))->send(new ContactNotification($contact));
             Log::info('email sent to ' . env('MAIL_FROM_ADDRESS'));
            return $this->sendResponse(new ContactResource($contact), 'Contact created successfully');
        } catch (Exception $th) {
            Log::info("Error creating contact: " . $th->getMessage());
            return $this->sendError('Error creating contact', $th->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/contacts/{contact}",
     *     operationId="desactivateContact",
     *     tags={"Contacts"},
     *     summary="Deactivate a contact",
     *     description="Soft delete a contact by setting is_active to false",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="contact",
     *         in="path",
     *         description="Contact ID",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact deactivated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contact deleted successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contact not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Contact not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function desactivate(Contact $contact)
    {
        try {
            $contact->is_active = false;
            $contact->save();
            return $this->sendResponse([], 'Contact deleted successfully');
        } catch (Exception $th) {
            return $this->sendError('Error deleting contact');
        }
    }
}
