<?php

namespace App\Http\Controllers;


use App\User;
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
}