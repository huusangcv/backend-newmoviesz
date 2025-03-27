<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    // Register API, Login API, Profile API, Logout API

    //  POST [name, email, password]
    public function register(Request $request)
    {
        // Validation
        $request->validate([
            "name" => "required|string",
            "email" => "required|string|email|unique:users",
            "password" => "required|confirmed",
        ]);

        $randomToken = Str::random(10);
        // User
        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "last_verification_email_sent_at" => now(),
            "email_verification_token" =>  $randomToken,
            "password" => bcrypt($request->password),
        ]);


        if ($user) {
            $user->notify(new VerifyEmailNotification($user));
            return response()->json([
                "status" => true,
            ]);
        }
    }

    // POST [email, password]
    public function login(Request $request)
    {

        //Validation
        $request->validate([
            "email" => "required|email|string",
            "password" => "required"
        ]);

        // Email check 
        $user = User::where("email", $request->email)->first();

        if (!empty($user)) {
            if ($user->email_verified_at !== null) {
                // User exists
                if (Hash::check($request->password, $user->password)) {
                    $token = $user->createToken("mytoken")->plainTextToken;

                    // Thiết lập cookie HttpOnly
                    // $cookie = cookie('auth_token', $token, 60, null, null, false, true); // Secure = false, HttpOnly = true

                    return response()->json([
                        "status" => true,
                        "message" => "User logged in",
                        "token" => $token,
                    ]);
                } else {
                    return response()->json([
                        "status" => false,
                        "message" => "Mật khẩu không chính xác",
                    ]);
                }
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Vui lòng xác thực email trước khi đăng nhập",
                ]);
            }
        } else {
            return response()->json([
                "status" => false,
                "message" => "Email không tồn tại, vui lòng đăng ký mới hoặc nhập lại chính xác",
            ]);
        }
    }

    // GET [Auth: Token]
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            "status" => true,
            "message" => "User logged out",
            "data" => []
        ]);
    }


    // GET [Auth: Token]
    public function profile()
    {
        $userData = auth()->user();

        return response()->json([
            "status" => true,
            "message" => "Profile Information",
            "data" => $userData,
            "id" => auth()->user()->id
        ]);
    }

    // PATCH [Auth: Profile Update] 
    public function updateProfile(Request $request)
    {
        $request->validate([
            "email" => "required|email|string",
        ]);
        $user = User::where("id", $request->id)->first();
        $user2 = User::where("email", $request->email)->first();

        if ($user2) {
            return response()->json([
                "status" => false,
                "message" => "Tài khoản đã tồn tại trên hệ thống"
            ]);
        }


        if ($request->email) {

            if ($user->email === $request->email) {
                return response()->json([
                    "status" => false,
                    "message" => "Email đã tồn tại trong hệ thống"
                ]);
            }

            $user->email = $request->email;
            $user->notify(new VerifyEmailNotification($user));

            return response()->json([
                "status" => true,
                "message" => "Đã gửi một 1 link xác thực về mail {$request->email}"
            ]);
        }
    }





    public function verifyUpdateProfile($id, $email)
    {
        $randomEmailCode = Str::random(100);
        $code = $randomEmailCode;
        $user = User::where('id', $id)->first();

        if (!$user) {
            return redirect("http://localhost:3000/confirmNewEmail?emailCode={$code}");
        }

        $user->email = $email;
        $user->email_code = $code;
        $user->save();

        return redirect("http://localhost:3000/confirmNewEmail?emailCode={$code}");
    }

    public function confirmUpdateProfile($id, $emailCode)
    {
        $user = User::where('email_code', $emailCode)->first();
        $userSimilar = User::where('id', $id)->first();
        if (!empty($user)) {
            if ($user->id === $userSimilar->id) {
                return '
                <h1 class="title">Xác nhận địa chỉ email mới</h1>
                <div class="message is-success">
                    <div class="message-body">
                        <p>Quá trình đổi email mới đã thành công. Từ giờ bạn hãy nhớ đăng nhập bằng địa chỉ email mới này!</p>
                    </div>
                </div>';
            }

            return '
            <h1 class="title">Xác nhận địa chỉ email mới</h1>
            <div class="message is-warning">
                <div class="message-body">
                    <p>Một tài khoản khác đã sử dụng địa chỉ email này.</p>
                </div>
            </div>';
        }
        return '
                <h1 class="title">Xác nhận địa chỉ email mới</h1>
                <div class="message is-warning">
                    <div class="message-body">
                        <p>Một tài khoản khác đã sử dụng địa chỉ email này.</p>
                    </div>
                </div>';
    }

    public function verifyEmail($token)
    {
        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return redirect('http://localhost:3000/verify/' . $token);
        }

        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->remember_token = $token;
        $user->save();

        return redirect('http://localhost:3000/verify/' . $token);
    }


    public function emailToken($token)
    {
        $user = User::where('remember_token', $token)->first();

        if (!empty($user)) {
            $token = Crypt::encrypt($user->remember_token);
            // Xác nhận thành công
            $user->remember_token = null;
            $user->save();
            $html = '<article class="message is-success">
                        <div class="message-header">
                            <p>Xác nhận thành công</p>
                        </div>
                        <div class="message-body">
                            Tài khoản của bạn đã được xác nhận thành công. Bạn có thể <a href="/">Đăng nhập</a> vào tài khoản.
                        </div>
                    </article>';
        } else {
            // Xác nhận không thành công
            $html = '<article class="message is-warning">
                        <div class="message-header">
                            <p>Xác nhận không thành công</p>
                        </div>
                        <div class="message-body">
                            <p>Mã xác nhận không hợp lệ hoặc tài khoản của bạn đã được xác nhận từ trước.</p>Thử <a href="/">đăng nhập</a> vào tài khoản.
                        </div>
                    </article>';
        }
        return $html;
    }

    public function resendVerification(Request $request)
    {
        $request->validate([
            "email" => "required|email|string",
        ]);

        // Email check 
        $user = User::where("email", $request->email)->first();

        $randomToken = Str::random(10);
        if (!empty($user)) {

            $currentTime = now();
            // Get time is minute in last_verification_email_sent_at table user
            $carbonDate = new Carbon($user->last_verification_email_sent_at);

            //Get time now is minute
            $carbonDateNow = new Carbon($currentTime);

            if ($user->email_verified_at === null) {
                if ($carbonDateNow->minute - $carbonDate->minute >= 2) {
                    $user->email_verification_token = $randomToken;
                    $user->last_verification_email_sent_at = now();
                    $user->save();
                    $user->notify(new VerifyEmailNotification($user));

                    // Log success
                    // \Log::info('Verification email sent to: ' . $user->email);

                    return response()->json([
                        "status" => true,
                        "message" => "Đã gửi một email để xác thực lại"
                    ]);
                } else {
                    return response()->json([
                        "status" => false,
                        "message" => "Vui lòng đợi một chút trước khi gửi lại email xác minh"
                    ]);
                }
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Tài khoản đã được xác thực"
                ]);
            }
        }
        return response()->json([
            "status" => false,
            "message" => "Tài khoản không tồn tại trong hệ thống, hãy đăng ký mới"
        ]);
    }

    //Post [Forgot Password]
    public function forgotPassword(Request $request)
    {
        $user = User::where("email", $request->email)->first();
        if (empty($user)) {
            return response()->json([
                "status" => false,
                "message" => "Tài khoản không tồn tại trong hệ thống, hãy đăng ký mới"
            ]);
        }

        if ($user->email_verified_at  === null) {
            return response()->json([
                "status" => false,
                "message" => "Tài khoản chưa được xác thực"
            ]);
        }

        $randomToken = '_' . Str::random(9, 'abcdefghijklmnopqrstuvwxyz0123456789');
        $user->password_retrieval_code = $randomToken;
        $user->save();
        if ($user->password_retrieval_code) {
            $user->notify(new VerifyEmailNotification($user));

            return response()->json([
                "status" => true,
                "message" => "Đã gửi mail hướng dẫn lấy lại mật khẩu"
            ]);
        }
    }


    public function resetPassword($password_retrieval_code)
    {
        if ($password_retrieval_code) {
            return redirect("http://localhost:3000/retrievePassword?passwordRetrievalCode={$password_retrieval_code}&action=retrieve");
        } else {
            return redirect("http://localhost:3000/retrievePassword?passwordRetrievalCode={$password_retrieval_code}&action=retrieve");
        }
    }

    public function renewPassword(Request $request)
    {
        $user = User::where("password_retrieval_code", $request->password_retrieval_code)->first();

        if (!empty($user)) {
            $user->password = bcrypt($request->password);
            $user->password_retrieval_code = null;
            $user->save();
            return response()->json([
                "status" => true,
                "message" => "Đặt mật khẩu mới thành công, hãy",
                "user" => $user
            ]);
        }

        return response()->json([
            "status" => false,
            "message" => "Link tạo mật khẩu không hợp lệ hoặc đã được sử dụng",
        ]);
    }
    public function updatePassword(Request $request)
    {
        $messages = [
            'password.required' => 'Mật khẩu là bắt buộc.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
        ];

        $request->validate([
            "password" => "required|min:6",
        ], $messages);

        $user = Auth::user();
        if (!empty($user)) {
            $user->password = bcrypt($request->password);
            $user->save();
            return response()->json([
                "status" => true,
                "message" => "Đặt mật khẩu mới thành công",
                "user" => $user
            ]);
        }

        return response()->json([
            "status" => false,
            "message" => "Đổi mật khẩu không thành công",
        ]);
    }
}
