<?php

namespace Thaliak\HTTP\Controllers;

use Illuminate\Http\Request;
use Thaliak\Models\PasswordReset;
use Thaliak\Models\User;
use Thaliak\Support\User as UserSupport;

class PasswordResetController extends Controller
{
    public function request(Request $request): PasswordReset
    {
        $this->validate($request, ['email' => 'required|email|exists:users']);
        return UserSupport::issuePasswordReset($request->email);
    }

    public function reset(Request $request): User
    {
        $this->validate($request, [
            'token' => 'required',
            'email' => 'required|email|exists:password_resets',
            'password' => 'required|confirmed|min:6',
        ]);

        return UserSupport::resetPassword(
            $request->token,
            $request->email,
            $request->password
        );
    }
}
