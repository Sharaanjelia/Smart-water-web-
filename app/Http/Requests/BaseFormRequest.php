<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    abstract public function authorize(): bool;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    abstract public function rules(): array;

    /**
     * Custom handler for failed validation on both API and Web requests.
     */
    protected function failedValidation(Validator $validator)
    {
        $firstError = $validator->errors()->first();

        if ($this->expectsJson() || $this->is('api/*') || $this->isJson()) {
            throw new HttpResponseException(
                response()->json([
                    'ok'      => false,
                    'error'   => $firstError,
                    'message' => $firstError,
                    'errors'  => $validator->errors(),
                ], 422)
            );
        }

        if (function_exists('session')) {
            session()->flash('error', $firstError);
        }

        parent::failedValidation($validator);
    }

    /**
     * Custom handler for failed authorization on both API and Web requests.
     */
    protected function failedAuthorization()
    {
        $message = 'Akses ditolak: Anda tidak memiliki wewenang untuk tindakan ini.';

        if ($this->expectsJson() || $this->is('api/*') || $this->isJson()) {
            throw new HttpResponseException(
                response()->json([
                    'ok'      => false,
                    'error'   => $message,
                    'message' => $message,
                ], 403)
            );
        }

        abort(403, $message);
    }

    /**
     * Get the currently authenticated actor, checking Auth facade and session fallback.
     */
    protected function currentActor(): ?User
    {
        $user = $this->user() ?? Auth::user();
        if ($user) {
            return $user;
        }

        $sessionUserId = session('swc_user.id') ?? ($_SESSION['swc_user']['id'] ?? null);
        if ($sessionUserId) {
            return User::find($sessionUserId);
        }

        return null;
    }

    /**
     * Check if the current user/actor has staff or admin privilege for complaint operations.
     * Hanya Admin dan Petugas Lapangan yang berhak menangani pengaduan.
     */
    protected function isStaffOrAdmin(): bool
    {
        $actor = $this->currentActor();
        if ($actor) {
            return $actor->isAdmin() || $actor->isFieldOfficer();
        }

        return false;
    }

    /**
     * Safe validator for Base64 image payload (JPEG, PNG, WEBP, GIF, etc.).
     */
    protected function isValidBase64Image(?string $value, int $maxSizeBytes = 10485760): bool
    {
        if (empty($value)) {
            return true;
        }

        // Data URI format: data:image/png;base64,xxxx
        if (preg_match('/^data:image\/(jpeg|png|jpg|webp|gif);base64,/i', $value)) {
            $parts = explode(',', $value);
            $raw = base64_decode($parts[1] ?? '', true);
            if ($raw === false) {
                return false;
            }
            return strlen($raw) <= $maxSizeBytes;
        }

        // Plain Base64 string
        $raw = base64_decode($value, true);
        if ($raw === false) {
            return false;
        }
        return strlen($raw) <= $maxSizeBytes;
    }
}
