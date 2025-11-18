<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand\Http\Controllers\Central;

use AltDesign\FleetCommand\DTO\UserDTO;
use AltDesign\FleetCommand\Models\Instance;
use AltDesign\FleetCommand\Models\InstanceUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserController
{
    public function fetch(Request $request): JsonResponse
    {
        // Validate the incoming request data.
        $validatedData = $request->validate([
            'instance_url' => 'required|url',
        ]);

        // Get the authenticated user and convert it to a UserDTO instance.
        $user = Auth::user();
        $userDTO = UserDTO::make(
            id: $user->id,
            name: $user->name,
            email: $user->email
        );

        $decodedInstanceUrl = urldecode($validatedData['instance_url']);
        $strippedURL = Str::after($decodedInstanceUrl, '://');

        // If the user is not authenticated, return a 401 Unauthorized response.
        $instance = Instance::where('base_url', Str::finish('http://'.$strippedURL, '/'))
            ->orWhere('base_url', Str::finish('https://'.$strippedURL, '/'))
            ->first();
        if (! $instance) {
            return response()->json([
                'message' => 'Invalid Instance URL.',
            ], 400);
        }

        // Check if the user is authorized to access the instance.
        $instanceUser = InstanceUser::getUserInstance(
            user: $userDTO,
            instance: $instance,
        );

        if (! $instanceUser) {
            return response()->json([
                'message' => 'Instance User record not found.',
            ], 401);
        }

        return response()->json($user);
    }

    public function update(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'user' => 'required|string',
        ]);

        $userModel = config('alt-fleet-cmd.central.user_model');
        $user = $userModel::find($validatedData['user']);

        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
            ], 400);
        }

        $fields = json_decode($request->get('fields'), true);
        foreach ($fields as $key => $value) {
            $user->{$key} = $value;
        }
        $user->save();

        return response()->json([
            'message' => 'User updated.',
        ], 200);
    }
}
