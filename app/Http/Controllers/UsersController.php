<?php

namespace App\Http\Controllers;


use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

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

    public function newUserAction(Request $request) {
        try {
            $query = json_decode($request->getContent(), true);
            $name = $query['name'];
            $email = $query['email'];
            $formId = $query['formId'];
            $role = $query['role'];
            $password = $query['password'];
            $user = new User(
                [
                    'name'=>$name,
                    'email'=>$email,
                    'role'=>$role,
                    'form_id'=>$formId,
                    'password'=>Hash::make($password)
                ]
            );
            $user->save();
            return response()->json(['message'=>'user added']);
        } catch (\Exception $exception) {
            return response()->json(['message'=>$exception->getMessage()], 400);
        }
    }

    public function editUserAction(Request $request) {
        try {
            $query = json_decode($request->getContent(), true);
            $userId = $query['userId'];
            $name = $query['name'];
            $email = $query['email'];
            $formId = $query['formId'];
            $role = $query['role'];
            $assignedUserIds = $query['assignedUserIds'];
            $user = User::find($userId);
            $userIds = [];
            foreach ($assignedUserIds as $assignedUserId) {
                $userIds[] = [$assignedUserId=>false];
            }
            $user->update(
                [
                    'name'=>$name,
                    'email'=>$email,
                    'role'=>$role,
                    'form_id'=>$formId,
                    'assigned_user_ids'=>$userIds
                ]
            );

        } catch (\Exception $exception) {
            return response()->json(['message'=>$exception->getMessage()], 400);
        }
    }
}




