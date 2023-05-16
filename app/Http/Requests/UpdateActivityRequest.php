<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required'],
            'description' => ['required'],
            'start_time'  => ['required', 'date'],
            'price'       => ['required', 'integer'],
            'image'       => ['image', 'nullable'],
            'guides'      => ['required', 'exists:users,id'],
        ];
    }
}
