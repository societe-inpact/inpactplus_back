<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Misc\User;
use App\Traits\JSONResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class PasswordController extends Controller
{
    use JSONResponseTrait;

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? $this->successResponse('', __($status))
            : $this->errorResponse(__($status));
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return $this->successResponse('', __($status));
        }
        return $this->errorResponse(__($status));
    }

    // Fonction permettant la mise à jour du mot de passe lorsque l'utilisateur est connecté
    public function changePassword(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'actual_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        $user = User::findOrFail($id);

        if (!Hash::check($request->actual_password, $user->password)) {
            return $this->errorResponse('L\'ancien mot de passe est incorrect', 401);
        }

        $user->password = Hash::make($request->new_password);

        if ($user->save()) {
            return $this->successResponse('', 'Mot de passe changé avec succès');
        } else {
            return $this->errorResponse('Impossible de changer votre mot de passe', 500);
        }
    }

}
