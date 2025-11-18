<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand\Http\Controllers\Instance;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController
{
    public function create(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'id' => 'required',
            'name' => 'string|required',
            'email' => 'string|required|email',
        ]);

        $userModel = config('alt-fleet-cmd.instance.user_model');

        if ($userModel::find($validatedData['id'])) {
            return response()->json([
                'message' => 'User already exists.',
            ], 400);
        }

        $user = new $userModel;
        $user->fill($validatedData);
        $user->id = $validatedData['id'];
        $user->save();

        return response()->json([
            'message' => 'User created.',
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
        ]);

        $userModel = config('alt-fleet-cmd.instance.user_model');

        $user = $userModel::find($validated['id']);
        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
            ], 400);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted.',
        ]);
    }
}
