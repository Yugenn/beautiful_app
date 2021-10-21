<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\IdentityProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;

class OAuthController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function oauthCallback($provider)
    {
        // 認証情報が返ってこなかった場合はログイン画面にリダイレクト
        try {
            $socialUser = Socialite::with($provider)->user();
        } catch(\Exception $e) {
            // dd($e);
            return redirect('/login')->withErrors(['oauth' => '予期せぬエラーが発生しました']);
        }

        // emailで検索してユーザーが見つかればそのユーザーを、見つからなければ新しいインスタンスを生成
        $user = User::firstOrNew(['email' => $socialUser->getEmail()]);

        // 新規ユーザーの処理
        if ($user->exists) {
            if ($user->identityProvider->name != $provider) {
                return redirect('/login')->withErrors(['oauth_error', 'このメールアドレスはすでに別の認証で使われてます']);
            }
        } else {
            $user->name = $socialUser->getNickname() ?? $socialUser->name;
            $identityProvider = new IdentityProvider([
                'id' => $socialUser->getId(),
                'name' => $provider
            ]);

            DB::beginTransaction();
            try {
                $user->save();
                $user->identityProvider()->save($identityProvider);
                DB::commit();
            } catch (\Exception $e) {
                // dd($e);
                DB::rollBack();
                return redirect()
                    ->route('login')
                    ->withErrors(['transaction_error' => '保存に失敗しました']);
            }
        }

        // ログイン
        Auth::login($user);
        return redirect(RouteServiceProvider::HOME);
    }
}
