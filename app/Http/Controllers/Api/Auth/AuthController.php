<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;
use App\Jobs\Api\Auth\SendMailResetPassword;
use App\Mail\Api\Auth\ResetPassword;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

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
        $user = User::where('email' , $email);
        if ($user->count() <= 0)
        {
            return $this->errorResponse(__('auth.reset_password_check_email') ,Response::HTTP_BAD_REQUEST);
        }

        $user = $user->first();
        $user->remember_token = csrf_token();
        $user->save();
        SendMailResetPassword::dispatch($user)->delay(now()->addSeconds(10));
        return $this->successResponse(null ,__('auth.reset_password_send_email'));
    }

    public function changeResetPassword(Request $request , $token)
    {
        $user = User::where('remember_token' , $token)->count();
        if ($user <= 0)
        {
            abort(404);
        }
        return view('auth.reset-password' , compact('token'));
    }

    public function changePasswordForget(Request $request)
    {
        return $request->all();
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
