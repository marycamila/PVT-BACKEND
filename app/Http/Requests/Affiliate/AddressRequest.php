<?php

namespace App\Http\Requests\Affiliate;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'city_address_id' => 'exists:cities,id',
            'zone' => 'nullable',
            'street' => 'nullable',
            'description' => 'nullable|min:3'
        ];
        switch ($this->method()) {
            case 'POST': {
                    foreach (array_slice($rules, 0, 1) as $key => $rule) {
                        $rules[$key] = implode('|', ['required', $rule]);
                    }
                    return $rules;
                }
            case 'PUT':
            case 'PATCH': {
                    return $rules;
                }
        }
    }
    public function filters()
    {
        return [
            'zone' => 'trim|uppercase',
            'street' => 'trim|uppercase',
            'number_address' => 'trim|uppercase',
            'description' => 'trim|uppercase'
        ];
    }
}
