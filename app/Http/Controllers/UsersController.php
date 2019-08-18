<?php

namespace App\Http\Controllers;


use App\User;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Request;

class UsersController extends Controller
{
    public function getAllUsers(Request $request)
    {
        try {
            $users = User::all(['id', 'email', 'name']);

            return response()->json($users, 200);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $userId = $request->auth->id;
            $user = User::find($userId);
            $query = json_decode($request->getContent(), true);
            $newPassword = $query['newPassword'];
            $confirmPassword = $query['confirmPassword'];
            if ($newPassword != $confirmPassword) {
                return response()->json(['message'=>'confirm password does not match'], 400);
            }
            $user->update(['password'=>Hash::make($newPassword)]);
            return response()->json(['message'=>'Password changed'], 200);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }

    }
}