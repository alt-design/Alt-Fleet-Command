<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand\Http\Controllers\Instance;

use Illuminate\Http\Request;

class UserController
{
    public function create(
        Request $request
    ): mixed {

        $validatedData = $request->validate([
            'id' => 'required',
            'name' => 'string|required',
            'email' => 'string|required|email',
        ]);

        $userModel = config('alt-fleet-cmd.instance.user_model');

        if ($userModel::find($validatedData['id'])) {
            return response(json_encode(['message' => 'User already exists']), 400);
        }

        $userModel::create($validatedData);
        return response("User Created", 200);
    }
}