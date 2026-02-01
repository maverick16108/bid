<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function sendCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $this->normalizePhone($request->phone);
        
        // Self-healing check: Look for both normalized and common formatted version
        $formattedPhone = '+7 (' . substr($phone, 1, 3) . ') ' . substr($phone, 4, 3) . '-' . substr($phone, 7, 2) . '-' . substr($phone, 9, 2);

        // Check if user exists (Normalized OR Formatted)
        if (!User::whereIn('phone', [$phone, $formattedPhone])->exists()) {
             return response()->json(['message' => 'Пользователь с таким номером не найден'], 404);
        }
        
        // Generate 4-digit code
        $code = rand(1000, 9999);
        
        // Store in cache for 5 minutes
        Cache::put('sms_code_' . $phone, $code, 300);
        
        // Send SMS
        $message = "Код подтверждения: {$code}";
        $sent = $this->smsService->send($phone, $message);

        if (!$sent) {
            return response()->json(['message' => 'Не удалось отправить СМС'], 500);
        }

        return response()->json(['message' => 'Код отправлен']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string'
        ]);

        $phone = $this->normalizePhone($request->phone);
        $cachedCode = Cache::get('sms_code_' . $phone);

        // Backdoor for demo/testing if needed, or stick to strict check
        if (!$cachedCode || $cachedCode != $request->code) {
           throw ValidationException::withMessages([
               'code' => ['Неверный код или срок действия истек']
           ]);
        }

        // Clear code
        Cache::forget('sms_code_' . $phone);

        // Self-healing: Check for duplicates (Normalized vs Formatted)
        $formattedPhone = '+7 (' . substr($phone, 1, 3) . ') ' . substr($phone, 4, 3) . '-' . substr($phone, 7, 2) . '-' . substr($phone, 9, 2);
        
        $users = User::whereIn('phone', [$phone, $formattedPhone])->get();

        if ($users->isEmpty()) {
             // Should not happen if sendCode checked existence, but handle anyway
             return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        $targetUser = null;

        if ($users->count() > 1) {
            // DUPLICATE DETECTED: Merge logic
            // Prefer the user with a custom name (not starting with 'Client' or 'Клиент')
            $richUser = $users->first(function($u) {
                return !str_starts_with($u->name, 'Клиент') && !str_starts_with($u->name, 'Client');
            });

            // If no rich user, just take the first one
            $targetUser = $richUser ?? $users->first();
            
            // Delete others
            foreach ($users as $u) {
                if ($u->id !== $targetUser->id) {
                    // Reassign orders if necessary (optional, but good practice)
                    $u->orders()->update(['user_id' => $targetUser->id]);
                    $u->delete();
                }
            }
        } else {
            $targetUser = $users->first();
        }

        // Normalize Phone if needed
        if ($targetUser->phone !== $phone) {
            try {
                $targetUser->phone = $phone;
                $targetUser->save();
            } catch (\Exception $e) {
                // Ignore unique constraint violation if race condition
            }
        }

        $token = $targetUser->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $targetUser
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        return $request->user();
    }

    protected function normalizePhone($phone)
    {
        // Simple normalization, keep digits only
        return preg_replace('/\D/', '', $phone);
    }
}