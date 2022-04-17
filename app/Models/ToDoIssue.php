<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ToDoIssue
 * @package App\Models
 * @property ToDoIssue $parent
 * @property string $name
 * @property string $description
 * @property integer priority
 * @property string $status
 * @property HasMany $children
 */
class ToDoIssue extends Model
{
    use HasFactory;
    /**
     * @var string[]
     */
    public const STATUS_DONE = 'done';
    public const STATUS_TODO = 'todo';

    protected $table = 'to_do_issues';
    protected array $fillable = ['id', 'parent_id', 'name', 'description','priority', 'status'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ToDoIssue::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(ToDoIssue::class, 'parent_id');
    }

}
