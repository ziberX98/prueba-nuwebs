<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\Tags\HasTags;

class Post extends Model
{
    use HasFactory;
    use HasTags;
    use Sluggable;
    use SluggableScopeHelpers;

    protected $guarded = [
        'private'
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'id' => 'nan',
            'name' => 'ANON',
        ]);
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    /**
     * Returns a Post model searching by its slug. Returns 404 if not found.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param $slug
     * @return Post
     */
    public static function getPostWithChecks($slug)
    {
        $post = static::findBySlugOrFail($slug);
        if ($post['visible'] && !$post->private) {
            return $post;
        }
        $auth = Auth::check();
        // If the post is visible and its private the user must be authenticated 
        if ($post['visible'] && $post->private && $auth) {
            return $post;
        }
        // If the post is not visible the user should not see it unless he is the owner of the post or have permissions of updating
        if (!$post['visible'] && $auth && Gate::allows('update', $post)) {
            return $post;
        }
        abort(403);
    }

}