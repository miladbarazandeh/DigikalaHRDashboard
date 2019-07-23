<?php
namespace App\Http\Controllers\Auth;
use Validator;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller;
use App\User;
class AuthController extends Controller
{
    private $request;
    protected function jwt(User $user) {
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + 60*60 // Expiration time
        ];

        // As you can see we are passing `JWT_SECRET` as the second parameter that will
        // be used to decode the token in the future.

        return JWT::encode($payload, env('JWT_SECRET'));
    }
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function userAuthenticate(User $user) {
        $this->validate($this->request, [
            'login'     => 'required',
            'password'  => 'required'
        ]);
        $login_type = filter_var($this->request->json('login'), FILTER_VALIDATE_EMAIL ) ? 'email' : 'username'; //check if the login variable is an email or a username
        if($login_type == 'email'){
            $user = User::where('email', $this->request->json('login'))->first(); // Find the user by email
        }else{
            $user = User::where('username', $this->request->json('login'))->first(); // Find the user by username
        }
        if (!$user) {

            return response()->json([
                'error' => 'User does not exist.'
            ], 400);
        }

        // Verify the password and generate the token
        if (Hash::check($this->request->json('password'), $user->password)) {

            return response()->json([
                'status'  => 200,
                'message' => 'Login Successful',
                'data'    => ['token' => $this->jwt($user) ] // return token
            ], 200);
        }
        return response()->json([
            'error' => 'Login details provided does not exit.'
        ], 400);
    }
}