<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Services\SMSService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


/**
 * @OA\Info(title="User Registration API", version="1.0")
 */



class RegisterController extends Controller
{
    protected $smsService;

    public function __construct(SMSService $smsService)
    {
        $this->smsService = $smsService;
    }
 

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="phone", type="string", example="09123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="کد ثبت نام شما پیامک شد."),
     *             @OA\Property(property="password_set", type="integer", example=0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="phone", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="SMS sending error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="مشکلی در ارسال پیامک وجود دارد. لطفا مجدداً تلاش کنید.")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        // اعتبارسنجی ورودی
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:10|max:15',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        // کد تصادفی برای تأیید
        $verificationCode = random_int(1000, 9999);
    
        // پیدا کردن کاربر با شماره تلفن
        $user = User::where('phone', $request->phone)->first();
    
        if (!$user) {
            // اگر کاربر وجود ندارد، کاربر جدید ایجاد کنید
            $user = User::create([
                'phone' => $request->phone,
                'verification_code' => $verificationCode,
                'verification_code_expires_at' => Carbon::now()->addMinutes(2), // زمان انقضای کد تأیید
                'is_verified' => false,
            ]);
            $passwordSet = 0;
            $message = 'کد ثبت نام شما پیامک شد.';
        } else {
            // اگر کاربر وجود دارد ولی هنوز تأیید نشده است، فقط کد تأیید را بروزرسانی کنید
            $user->verification_code = $verificationCode;
            $user->verification_code_expires_at = Carbon::now()->addMinutes(2); // بروزرسانی زمان انقضای کد
            $user->is_verified = false; // حالت تأیید کاربر را به حالت نادرست برگردانید
            $user->save(); // ذخیره تغییرات
    
            $passwordSet = !is_null($user->password) ? 1 : 0;
            
            $message = 'کد ورود شما پیامک شد.';
        }
    
        // ارسال پیامک
        $smsSent = $this->smsService->sendVerificationCode($request->phone, $verificationCode);
    
        if (!$smsSent) {
            return response()->json(['message' => 'مشکلی در ارسال پیامک وجود دارد. لطفا مجدداً تلاش کنید.'], 500);
        }
    
        return response()->json([
            'message' => $message,
            'password_set' => $passwordSet,
        ], 201);
    }




/**
     * @OA\Post(
     *     path="/api/verify",
     *     summary="Verify user code",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone", "verification_code"},
     *             @OA\Property(property="phone", type="string", example="09123456789"),
     *             @OA\Property(property="verification_code", type="string", example="1234")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code verified successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid code"
     *     )
     * )
     */
    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:10|max:15',
            'verification_code' => 'required|string|size:4',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // پیدا کردن کاربر با شماره تلفن
        $user = User::where('phone', $request->phone)->first();

        // بررسی اینکه کاربر وجود دارد
        if (!$user) {
            return response()->json(['message' => 'کاربر یافت نشد.'], 404);
        }

        // بررسی زمان اعتبار کد تأیید
        if ($user->verification_code_expires_at < Carbon::now()) {
            return response()->json(['message' => 'کد تأیید منقضی شده است.'], 400);
        }

        // بررسی کد تأیید
        if ($user->verification_code !== $request->verification_code) {
            return response()->json(['message' => 'کد تأیید نادرست است.'], 400);
        }

        // اگر کد تأیید درست است
        $user->is_verified = true; // کاربر تأیید شده
        $user->save(); // ذخیره تغییرات

        // تولید توکن JWT
        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json(['error' => 'مشکلی در تولید توکن وجود دارد.'], 500);
        }

        return response()->json([
            'message' => 'کد تأیید موفق بود. حساب شما فعال شد.',
            'token' => $token // ارسال توکن JWT به کاربر
        ], 200);
    }

/**
     * @OA\Post(
     *     path="/api/login/password",
     *     summary="Login with phone and password",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone", "password"},
     *             @OA\Property(property="phone", type="string", example="09123456789"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="ورود موفقیت‌آمیز بود."),
     *             @OA\Property(property="token", type="string", example="jwt_token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid password",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="رمز عبور نادرست است.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="کاربر یافت نشد.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="phone", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="password", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Token generation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="مشکلی در تولید توکن وجود دارد.")
     *         )
     *     )
     * )
     */
    public function loginWithPassword(Request $request)
{
    // اعتبارسنجی ورودی
    $validator = Validator::make($request->all(), [
        'phone' => 'required|string|min:10|max:15',
        'password' => 'required|string|min:8',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    // پیدا کردن کاربر با شماره تلفن
    $user = User::where('phone', $request->phone)->first();

    // بررسی اینکه کاربر وجود دارد
    if (!$user) {
        return response()->json(['message' => 'کاربر یافت نشد.'], 404);
    }

    // بررسی رمز عبور
    if (!Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'رمز عبور نادرست است.'], 400);
    }

    // تولید توکن JWT
    try {
        $token = JWTAuth::fromUser($user);
    } catch (JWTException $e) {
        return response()->json(['error' => 'مشکلی در تولید توکن وجود دارد.'], 500);
    }

    return response()->json([
        'message' => 'ورود موفقیت‌آمیز بود.',
        'token' => $token // ارسال توکن JWT به کاربر
    ], 200);
}

/**
 * @OA\Get(
 *     path="/api/userinfo",
 *     tags={"User"},
 *     summary="Retrieve authenticated user",
 *     description="Get the details of the currently authenticated user.",
 *     @OA\Response(
 *         response=200,
 *         description="User retrieved successfully.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="phone", type="string", example="09127771122"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="User not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid token.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Invalid token")
 *         )
 *     ),
 *     security={
 *         {"bearer": {}}
 *     }
 * )
 */

public function getUser()   {
         try {
             if (! $user = JWTAuth::parseToken()->authenticate()) {
                 return response()->json(['error' => 'User not found'], 404);
             }
         } catch (JWTException $e) {
             return response()->json(['error' => 'Invalid token'], 400);
         }
 
         return response()->json(compact('user'));
     }
 
}
