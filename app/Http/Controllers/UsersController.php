<?php

namespace App\Http\Controllers;


use App\Relation;
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

    public function getUser(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $userId = $request->get('userId');
//            $cycle = $query['cycle'];
            $user = User::find($userId);
            $result = [
                'id' =>$userId,
                'name'=>$user->name,
                'email'=>$user->email,
                'role'=>$user->role

            ];
            $assignedUsers = Relation::where('appraisal_id', $userId)->get();
            $employees = [];
            if($assignedUsers->isNotEmpty()) {
                foreach ($assignedUsers as $assignedUser) {
                    $employee = User::find($assignedUser['appraiser_id']);
                    $employees[] = [
                        'id' => $employee->id,
                        'name'=>$employee->name,
                        'email'=>$employee->email,
                        'weight'=>$assignedUser->weight,
                        'type'=>$assignedUser->type
                    ];
                    $formId= $assignedUser->form_id;
                }
                $result['assigned_users'] = $employees;
                $result['form_id'] = $formId;
            } else {
                $result['assigned_users'] = null;
                $result['form_id'] = null;
            }
            return response()->json($result, 200);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }
    }

    public function managementAction(Request $request) {

        try {
            $allForms = \App\Forms::all();
            $forms = [];

            foreach ($allForms as $allForm) {
                $forms[] = ['id' => $allForm['id'], 'name'=> $allForm['name']];
            }

            $users = User::all(['id', 'email', 'name']);

            return response()->json(['users'=>$users, 'forms'=>$forms]);

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
            $oldPassword = $query['password'];

            if (Hash::check($oldPassword, $user->password)) {
                $newPassword = $query['newPassword'];
                $confirmPassword = $query['confirmPassword'];
                if ($newPassword != $confirmPassword) {
                    return response()->json(['message'=>'confirm password does not match'], 400);
                }
                $user->update(['password'=>Hash::make($newPassword)]);
                return response()->json(['message'=>'Password changed'], 200);
            } else {
                return response()->json([
                    'error' => 'password is not correct.'
                ], 400);
            }
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
            $assignedUsers = isset($query['assignedUsers'])?$query['assignedUsers']:null;
            $user = new User(
                [
                    'name'=>$name,
                    'email'=>$email,
                    'role'=>$role,
                    'password'=>Hash::make($password)
                ]
            );
            $user->save();

            foreach ($assignedUsers as $assignedUser) {
                $relation = new Relation(
                    [
                        'appraiser_id'=>$assignedUser['id'],
                        'appraisal_id'=>$user->id,
                        'form_id'=>$formId,
                        'weight'=>$assignedUser['weight']
                    ]
                );
                $relation->save();
            }
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
            return response()->json(['message'=>'User updated.'], 200);

        } catch (\Exception $exception) {
            return response()->json(['message'=>$exception->getMessage()], 400);
        }
    }
}