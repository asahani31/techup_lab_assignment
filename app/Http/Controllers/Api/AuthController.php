<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Http\JsonResponse;

class AuthController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        if (!$user) {
            return $this->sendError('error', 'Server Error', 400);
        }else{
            $success['token'] =  $user->createToken('auth_token')->plainTextToken;
            $success['name'] =  $user->name;
            return $this->sendResponse($success, 'User registered successfully.',200);
        }
       
       
    }
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $check_user = User::where('email', $request->email)->first();
        if ($check_user == null) {
            return $this->sendError('error', 'User not found.', 422);
        }
        if (Hash::check($request->password, $check_user->password) == false) {
            return $this->sendError('error', 'Invalid password.', 422);
        }
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('auth_token')->plainTextToken; 
            $success['name'] =  $user->name;
            return $this->sendResponse($success, 'User login successfully.');
        }else{ 
            return $this->sendError('error', 'Unauthorised', 400);
        } 
    }
}