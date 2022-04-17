<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UserIssue extends Model
{
    protected $table = 'user_issues';

    protected array $fillable = ['user_id', 'issue_id'];
    public $timestamps = false;
}
