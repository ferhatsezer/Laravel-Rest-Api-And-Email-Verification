<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate(
            [
                'name' => '',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
            ]
        );

        $validatedData['password'] = bcrypt($validatedData['password']);

        $user = User::create($validatedData);
        event(new Registered($user));
        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
        ], 200);
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate(
            [
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]
        );


        if (Auth::attempt($validatedData)) {

            $user = Auth::user();

            if (!$user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email not verified'], 401);
            }

            $tokenResult = $user->createToken('Login');
            $response = [
                'message' => 'Login successful',
                'user' => $user,
                'access_token' => $tokenResult->accessToken,
                'expires_in' => Carbon::parse($tokenResult->token->expires_at)->timestamp,
            ];

            return response()->json($response, 200);

        } else {
            return response()->json([
                'message' => 'Email or password is incorrect',
            ], 401);
        }
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate(
            [
                'email' => 'email|unique:users,email,' . $request->user()->getKey(),
                'password' => 'min:8|confirmed',
            ]
        );

        $validatedData['password'] = bcrypt($validatedData['password']);
        if ($validatedData['email'] AND $request->user()->email != $validatedData['email']) {
            $request->user()->markEmailAsNotVerified();
            event(new Registered($request->user()));
        }


        $request->user()->update($validatedData);
        return response()->json([
            'message' => 'Update is Successful',
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Logout is successful'
        ], 200);
    }
}
