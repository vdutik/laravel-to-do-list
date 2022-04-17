<?php


namespace  App\Http\Requests;


class RequestToDoIssue extends AbstractRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'description' => 'required|string',
            'priority' => 'required|numeric',
//            'status' => 'in:done,todo',
        ];
    }
}
