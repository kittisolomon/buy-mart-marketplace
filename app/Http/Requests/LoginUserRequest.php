<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\HasJsonResponse;
use App\Support\HttpConstants;
class LoginUserRequest extends FormRequest
{
    use HasJsonResponse;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

       protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        throw new HttpResponseException(
            $this->jsonResponse(
                HttpConstants::HTTP_VALIDATION_ERROR,
                'Validation failed',
                ['errors' => $errors]
            )
        );
    }
}
