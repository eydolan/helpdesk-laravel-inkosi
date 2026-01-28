<?php

namespace App\Http\Requests;

use App\Services\MTCaptchaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public access
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isLoggedIn = auth()->check();
        
        $rules = [
            'unit_id' => 'nullable|exists:units,id',
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'voucher_number' => 'nullable|string',
        ];

        // If not logged in, require name, phone, and MTCaptcha (email is optional)
        if (!$isLoggedIn) {
            $rules['name'] = 'required|string|max:255';
            $rules['email'] = 'nullable|email|max:255';
            $rules['phone'] = [
                'required',
                'string',
                Rule::unique('users', 'phone')->ignore($this->user()?->id),
            ];
            // Only require MTCaptcha token if MTCaptcha is enabled and should be shown
            $mtcaptchaService = app(MTCaptchaService::class);
            if ($mtcaptchaService->shouldShow()) {
                $rules['mtcaptcha_token'] = 'required';
            }
        } else {
            // If logged in, email is optional, phone still required
            $rules['email'] = 'nullable|email|max:255';
            $rules['phone'] = [
                'required',
                'string',
                Rule::unique('users', 'phone')->ignore(auth()->id()),
            ];
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate MTCaptcha if not logged in
            if (!auth()->check()) {
                $mtcaptchaService = app(MTCaptchaService::class);
                $token = $this->input('mtcaptcha_token');
                
                if ($mtcaptchaService->shouldShow() && !$mtcaptchaService->validateToken($token)) {
                    $validator->errors()->add('mtcaptcha_token', 'The MTCaptcha verification failed. Please try again.');
                }
            }

            // Validate phone format consistency
            if ($this->has('phone')) {
                $phone = $this->input('phone');
                // Basic phone validation - can be enhanced
                $phone = preg_replace('/[^0-9]/', '', $phone);
                if (strlen($phone) < 9) {
                    $validator->errors()->add('phone', 'Please enter a valid phone number.');
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'email.email' => 'Please enter a valid email address.',
            'phone.required' => 'Phone number is required.',
            'phone.unique' => 'This phone number is already registered.',
            'unit_id.required' => 'Please select a work unit.',
            'unit_id.exists' => 'Selected work unit is invalid.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'Selected category is invalid.',
            'title.required' => 'Title is required.',
            'description.required' => 'Description is required.',
            'mtcaptcha_token.required' => 'Please complete the captcha verification.',
        ];
    }
}
