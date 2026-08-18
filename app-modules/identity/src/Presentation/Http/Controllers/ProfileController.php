<?php

namespace Modules\Identity\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Identity/Profile/Edit', [
            'profile' => [
                'user' => [
                    'id' => $user->getKey(),
                    'name' => $user->name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                ],
            ],
        ]);
    }
}
