<?php

namespace App\Http\Controllers\API\NewLetter;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\NewLetter\NewLetterRequest;
use App\Http\Resources\NewLetter\NewLetterResource;
use App\Models\NewLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewLetterController extends BaseController
{
    //
    public function index(Request  $request)
    {
        $query = NewLetter::query();

        // filtrage par status
        $status = $request->get('status');

        if ($status === 'activated') {
            $query->where('is_active', 1);
        } elseif ($status === 'desactivated') {
            $query->where('is_active', 0);
        } else {
            $query->where('is_active', 1);
        }

        // recherche par email
        if ($request->has('q') && $request->q) {
            $q = $request->q;
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('email', 'like', '%' . $q . '%');
            });
        }

        // pagination
        $perPage = $request->get('per_page', 10);

        $newLetters = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->sendResponse(
            [
                'items' => NewLetterResource::collection($newLetters),
                'pagination' => [
                    'total' => $newLetters->total(),
                    'per_page' => $newLetters->perPage(),
                    'current_page' => $newLetters->currentPage(),
                    'last_page' => $newLetters->lastPage(),
                ]
            ],
            'Liste des emails abonnés'
        );
    }


    public function store(NewLetterRequest $request)
    {
        $data = $request->all();

        $data['slug'] = 'NL-' . Str::uuid();

        if (NewLetter::where('email', $data['email'])->exists()) {
            return $this->sendUniqueEmailError('Email déjà abonné');
        }

        $newLetter = NewLetter::create($data);

        return $this->sendResponse([], 'Email abonné ajouté avec succès');
    }

    // desactivation d'un email
    public function desactivate(NewLetter $newLetter)
    {
        $newLetter->is_active = false;
        $newLetter->save();

        return $this->sendResponse(new NewLetterResource($newLetter), 'Email abonné désactivé avec succès');
    }
}
