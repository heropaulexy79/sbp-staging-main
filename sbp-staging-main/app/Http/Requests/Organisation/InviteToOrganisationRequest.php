<?php

namespace App\Http\Requests\Organisation;

use Illuminate\Foundation\Http\FormRequest;

class InviteToOrganisationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();


        // $organisation = $this->route('organisation');

        return $user->isAdminInOrganisation($user->organisationNew->organisation);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'invites' => 'required|array|min:1',
            'invites.*.email' => 'required|email|unique:users,email'
        ];
    }


    public function messages()
    {
        return [
            'invites.*.email.unique' => ':input has already been taken.',
        ];
    }
}
