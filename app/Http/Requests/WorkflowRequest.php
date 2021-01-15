<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Workflow;

class WorkflowRequest extends Request
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
     * @return array
     */
    public function rules()
    {
        $params = $this->route()->parameters();
        $id = null;
        if (isset($params['workflow'])) {
            $id = (int)$params['workflow'];
        }

        $rules = [
            'start_at' => 'required|date|unique:workflow,start_at,' . $id . ',id,deleted_at,NULL,author_id,' . $this->user()->id,
            'duration' => 'required|integer|min:10|max:600',
            'type' => 'required|in:' . implode(',', array_keys(Workflow::$typeTranslate))
        ];

        if ($this->type === 'sick_leave' || $this->type === 'vacation') {
            $rules['end_at'] = 'date|after:start_at';
            unset($rules['duration']);
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'start_at.unique' => 'В один день не може бути більше 1 події',
            'start_at.required' => 'Дата є обов\'язковою',
            'duration.max' => 'Завелика тривалість',
            'duration.min' => 'Замала тривалість',
            'duration.integer' => 'Невірний формат тривалості',
            'duration.required' => 'Поле тривалість є обов\'язкове'
        ];
    }
}
