<?php

namespace App\Http\Controllers\Api\Auth;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\PersonalAccessToken;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Jobs\Api\Auth\SendMailResetPassword;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\Auth\ChangeResetPasswordRequest;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->all();
        if (Auth::attempt($credentials))
        {
            $token = Auth::user()->createToken('token')->plainTextToken;
            return $this->successResponse($this->dataAuth($token , Auth::user()));
        }
       return $this->errorResponse(__('auth.login') , Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */

    public function logout()
    {
        Auth::user()->tokens()->delete();
        return $this->successResponse(null ,__('auth.logout'));
    }

    /**
     * @param  ChangePasswordRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function changePassword(ChangePasswordRequest $request)
    {
        $passwordOld = Auth::user()->password;
        $getPasswordOld = $request->input('password_old');
        $checkPasswordOld = Hash::check($getPasswordOld , $passwordOld);
        if (!$checkPasswordOld)
        {
            return $this->errorResponse(__('auth.message_password_check'), Response::HTTP_BAD_REQUEST);
        }
        Auth::user()->password = Hash::make($request->password);
        Auth::user()->save();
        return $this->successResponse(null ,__('auth.success_change_password'));
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $email = $request->input('email');
        $queryPasswordReset = PasswordReset::Email($email);
        $user = User::Email($email)->count();
        if ($user <= 0)
        {
            return $this->errorResponse(__('auth.reset_password_check_email') ,Response::HTTP_BAD_REQUEST);
        }
        if($queryPasswordReset->count() > 0)
        {
            $queryPasswordReset->delete();
        }
        $passwordReset = PasswordReset::create([
            'email' => $email,
            'token' => Str::random(40),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        SendMailResetPassword::dispatch($passwordReset->email , $passwordReset->token)->delay(now()->addSeconds(10));
        return $this->successResponse(null ,__('auth.reset_password_send_email'));
    }

    public function changeResetPassword(Request $request , $token)
    {
        $query = PasswordReset::Token($token);
        $user =  $query->first();
        $expire = Carbon::parse($user->created_at)->addMinutes(1);
        if(Carbon::now()->greaterThanOrEqualTo($expire))
        {
            $query->delete();
        }
        if ($user->count() <= 0)
        {
            abort(404);
        }
        return view('auth.reset-password' , compact('token'));
    }

    public function changePasswordForget(ChangeResetPasswordRequest $request)
    {
        $getPasswordAndToken = $request->only(['token' ,'password']);
        $query = PasswordReset::where('token' , data_get($getPasswordAndToken ,'token'));
        $passwordReset = $query->first();
        $user = User::Email($passwordReset->email)->first();
        $user->password = Hash::make(data_get($getPasswordAndToken ,'password'));
        $user->save();
        $query->delete();
        return __('auth.reset_password_success');
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function refreshToken(Request $request)
    {
        $token = $request->header('authorization');
        $token = trim(str_replace('Bearer', '', $token));

        $tokenHas = PersonalAccessToken::findToken($token);

        if (!$token || !$tokenHas) {
            return $this->errorResponse(__('auth.message_token'), Response::HTTP_BAD_REQUEST);
        }

        if (!$tokenHas->tokenable instanceof User) {
            return $this->errorResponse(__('auth.message_not_user_instance'), Response::HTTP_BAD_REQUEST);
        }

        $expire = Carbon::parse($tokenHas->created_at)->addMinutes(config('sanctum.expiration'));

        $now = Carbon::now();

        if ($now->greaterThanOrEqualTo($expire)) {
            $tokenHas->tokenable->tokens()->delete();
            $token = $tokenHas->tokenable->createToken('token')->plainTextToken;
            return $this->successResponse($this->dataAuth($token, $tokenHas->tokenable));
        }

        return $this->successResponse($this->dataAuth($token, $tokenHas->tokenable));

    }

    /**
     * @param $token
     * @param $user
     * @return array
     */

    private function dataAuth($token , $user)
    {
        return [
            'user'         => $user,
            "access_token" => $token,
            "token_type"   => "Bearer",
            "expiration"   => Config::get('sanctum.expiration')
        ];
    }
}
