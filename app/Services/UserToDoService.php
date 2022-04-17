<?php

namespace App\Services;

use App\Models\ToDoIssue;
use App\Models\User;
use App\Models\UserIssue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UserToDoService
{

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param array $attribute
     * @return ToDoIssue
     */
    private function createToDoService(array $attribute): ToDoIssue
    {
        $issue = new ToDoIssue();
        /** TODO можна зробити через DTO, щоб знати що за масив сюди заходить */

        $issue->fill($attribute);
        $issue->save();

        return $issue;
    }

    /**
     * @param array $attribute
     * @return bool
     */
    public function createUserToDoService(array $attribute): bool
    {
        return DB::transaction(function () use ($attribute) {
            $todoIssue = $this->createToDoService($attribute);
            $userIssue = new UserIssue(['user_id' => $this->user->id, 'issue_id' => $todoIssue->id]);
            return $userIssue->save();
        });

        return false;
    }


    /**
     * @param ToDoIssue $doIssue
     * @param $attribute
     * @return bool
     */
    public function update(ToDoIssue $doIssue, $attribute): bool
    {
        /** TODO можна зробити через DTO */

        $doIssue->fill($attribute);
        return $doIssue->save();
    }

    /**
     * @param ToDoIssue $doIssue
     * @return bool|null
     */
    public function delete(ToDoIssue $doIssue)
    {
        if ($this->checkIssueForDelete($doIssue)) {
            return DB::transaction(function () use ($doIssue) {
                UserIssue::query()
                    ->where(['user_id' => $this->user->id, 'issue_id' => $doIssue->id])
                    ->delete();
                return $doIssue->delete();
            });
        } else {
            return false;
        }
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getList(Request $request)
    {
        $query = $this->user->issues()->getQuery();
        if (isset($request->filters)) {
            //реалізовував тільки для одного набору фільтру  ?filters=[priority=1,2]&sort=[priority=asc]
            $this->setFilter($query, $request->filters);
        }else{
            $query->where('parent_id', null);
        }
        if (isset($request->sort)) {
            $this->setSort($query, $request->sort);
        }

        $data = $this->buildToDoIssuesData($query->get());
        return $data;

    }

    public function buildToDoIssuesData(Collection $collection)
    {
       return $collection->map(function (ToDoIssue $item) {
            $data = $this->todoIssueMap($item);
            if ($item->children()->count() > 0) {
                $dataItems = $item->children->map(function (ToDoIssue $item) {
                    return $this->todoIssueMap($item);
                });
                $data['subIssues'] = $dataItems;
            }
            return $data;
        });
    }

    private function todoIssueMap(ToDoIssue $toDoIssue)
    {
        return [
            'id' => $toDoIssue->id,
            'status' => $toDoIssue->status,
            'name' => $toDoIssue->name,
            'description' => $toDoIssue->description,
            'priority' => $toDoIssue->priority,
        ];
    }

    /**
     * @param string $string
     * @return array
     * тут типу парсер
     */
    private function prepareParams(string $string)
    {
        $string = rtrim($string, "]");
        $string = ltrim($string, "[");
        $param = explode("=", $string);

        $params = [$param[0] => $param[1]];
        return $params;
    }

    private function setFilter(Builder $query, string $filters)
    {

        $filters = $this->prepareParams($filters);
        foreach ($filters as $key => $filter) {
            switch ($key) {
                case 'priority':
                    list($from, $to) = explode(',', $filter);
                    $query->whereBetween($key, [$from ?? 0, $to ?? 5]);
                    break;
                case 'name':

                    $query->where($key, 'like', '%' . $filter . '%');
                    break;
                case 'status':
                    $query->where($key, $filter);
                    break;
            }
            //можна через $query->when теж саме зробити
        }
    }

    private function setSort(Builder $query, string $sort)
    {
        $sort = $this->prepareParams($sort);
        foreach ($sort as $key => $value) {
            switch ($key) {
                case 'priority':
                    $query->orderBy($key, $value);
                    break;
                case 'date':
                    $query->orderBy('create_at', $value);
                    break;
            }
        }
    }

    /**
     * @param ToDoIssue $toDoIssue
     * @param string $status
     * @return bool
     */
    public function changeStatus(ToDoIssue $toDoIssue, string $status): bool
    {
        if (ToDoIssue::STATUS_DONE == $status) {
            if (!$this->checkIssueForDone($toDoIssue)) {
                return false;
            }
        }
        // тут можна ще зробити перевірку на зміну статусу на туду


        return $this->update($toDoIssue, ['status' => $status]);

    }

    public function checkIssueForDelete(ToDoIssue $toDoIssue, bool &$can = true)
    {

        if ($toDoIssue->children()->count() > 0) {
            return false;
        }
        if ($toDoIssue->status == ToDoIssue::STATUS_DONE) {
            return false;
        }
        return $can;

        // рекурсивно перевіряє на закриті задачі у всіх дочірніх
//        if (!$can){
//            return false;
//        }
//        if ($toDoIssue->children()->count() > 0) {
//            foreach ($toDoIssue->children as $child) {
//                if ($this->checkIssueForDelete($child, $can)) {
//                    return $can;
//                }
//            }
//        }
//        if ($toDoIssue->status == ToDoIssue::STATUS_DONE) {
//            $can = false;
//            return $can;
//        }
//        return $can;
    }

    private function checkIssueForDone(ToDoIssue $toDoIssue, bool &$can = true)
    {

        if ($toDoIssue->children()->count() > 0) {
            foreach ($toDoIssue->children as $child) {
                if ($child->status == ToDoIssue::STATUS_TODO) {
                    $can = false;
                    return $can;
                }
                if (!$this->checkIssueForDone($child)) {
                    $can = false;
                    return $can;
                }
            }
        }

        return $can;
    }

}
