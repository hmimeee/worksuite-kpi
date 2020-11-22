<?php

namespace Modules\KPI\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInfractionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id|numeric',
            'infraction_type_id' => 'required_with:from_list|exists:infraction_types,id|numeric',
            'infraction_type' => 'required_without:from_list|string',
            'reduction_points' => 'required_with:from_list|numeric',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
