<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        return [
            'username' => 'required',
            'password' => 'required',
            'camera_id' => 'required',
            'video' => 'required|file',
        ];
    }

    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages()
    {
        return [
            'username.required' => 'Email is required!',
            'camera_id.required' => 'Name is required!',
            'password.required' => 'Password is required!',
            'video.required' => 'Video is required!'
        ];
    }
}
