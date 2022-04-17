<?php


namespace App\Http\Controllers;


use App\Http\Requests\RequestToDoIssue;
use App\Models\ToDoIssue;
use App\Models\User;
use App\Models\UserIssue;
use App\Services\UserToDoService;
use Illuminate\Http\Request;

class ToDoController extends Controller
{
    public function __construct(UserToDoService $service)
    {
        $this->service = $service;
    }

    private UserToDoService $service;


    public function get(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $this->service->setUser($user);
        $data = $this->service->getList($request);

        return response()->json($data);
    }

    public function changeStatus(Request $request, ToDoIssue $issue, string $status)
    {
        $issue = $request->user()->issues()->where('id', $issue->id)->first();
        if (!$issue) {
            return response()->json(['message' => 'not found issue']);
        }
        if ($status == $issue->status) {
            return response()->json(['message' => 'Status olrady is ' . $status]);
        }
        $this->service->setUser($request->user());
        return $this->service->changeStatus($issue, $status)
            ? response()->json(['message' => 'status changed'])
            : response()->json(['message' => 'status not changed']);

    }

    public function createOrUpdate(RequestToDoIssue $request, ToDoIssue $issue = null)
    {
        $this->service->setUser($request->user());

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'priority' => $request->priority
        ];

        if (!$issue) {
            $result = $this->service->createUserToDoService($data);
            return $result
                ? response()->json(['message' => 'Task has been created.'])
                : response()->json(['message' => 'somth went wrong']);
        } else {
            $result = $this->service->update($issue, $request->attributes());
            return $result
                ? response()->json(['message' => 'Task has been updated.'])
                : response()->json(['message' => 'somth went wrong']);
        }
    }

    public function createChild(RequestToDoIssue $request, ToDoIssue $issue)
    {
        $issue = $request->user()->issues()->where('id', $issue->id)->first();
        if (!$issue) {
            return response()->json(['message' => 'not found parent id']);
        }
        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'priority' => $request->priority,
            'parent_id' => $issue->id
        ];
        $this->service->setUser($request->user());
        $result = $this->service->createUserToDoService($data);

        return $result
            ? response()->json(['message' => 'Task has been created for parent todo with id ' . $issue->id])
            : response()->json(['message' => 'some error']);

    }

    public function delete(ToDoIssue $issue, Request $request)
    {
        $issue = $request->user()->issues()->where('id', $issue->id)->first();
        if (!$issue) {
            return response()->json(['message' => 'not found issue']);
        } else {
            $this->service->setUser($request->user());
            return $this->service->delete($issue)
                ? response()->json(['message' => 'Task has been deleted'])
                : response()->json(['message' => 'Can`t delete (check status or child issues)']);
        }
    }


    public function getIssue(ToDoIssue $issue, Request $request)
    {
        $item = $request->user()->issues()->where('id', $issue->id)->get();
        if ($item->first()) {
            $data = $this->service->buildToDoIssuesData($item);
            return response()->json(reset($data));
        }
    }

}
