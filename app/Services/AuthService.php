<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class authService
{
    /**
     * Verify an account verification OTP.
     */
    public function accountVerificationOtp(User $user, string $otpCode, string $type)
    {
        $otp = Otp::where('user_id', $user->id)
            ->where('code', $otpCode)
            ->where('type', $type)
            ->where('expires_at', '>', now())
            ->where('verified', false)
            ->latest()
            ->first();

        if (!$otp) {
            return false;
        }

        DB::transaction(function () use ($otp, $user) {
            $otp->update(['verified' => true]);
            $user->update(['email_verified_at' => now()]);
        });

        return true;
    }

    public function dispatchOtp(User $user, string $type): void
    {
        $otpCode = random_int(100000, 999999);

        $otp = Otp::create([
            'user_id' => $user->id,
            'code' => $otpCode,
            'type' => $type,
            'expires_at' => now()->addMinutes(10),
            'verified' => false,
        ]);

        Mail::to($user->email)->send(new SendOtpMail($otpCode, $user->name));

    }
}