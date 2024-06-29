<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class BlogModel extends Model
{
    use HasFactory;

    protected $table = 'blog';

    static public function getSingle($id)
    {
        return self::find($id);
    }
    static public function getRecordSlug($slug)
    {
        return   self::select('blog.*', 'users.name as user_name', 'category.name as category_name')
            ->join('users', 'users.id', '=', 'blog.user_id')
            ->join('category', 'category.id', '=', 'blog.category_id')
            ->where('blog.status','=',1)
            ->where('blog.is_publish','=',1)
            ->where('blog.is_delete','=',0)
            ->where('blog.slug','=',$slug)
            
            ->first();
    }
    static public function getRecordFront()
    {
        $return = self::select('blog.*', 'users.name as user_name', 'category.name as category_name')
            ->join('users', 'users.id', '=', 'blog.user_id')
            ->join('category', 'category.id', '=', 'blog.category_id');
    
        if (!empty(request()->get('q'))) {
            $return = $return->where('blog.title', 'like', '%' . request()->get('q') . '%');
        }
    
        $return = $return->where('blog.status', '=', 1)
            ->where('blog.is_publish', '=', 1)
            ->where('blog.is_delete', '=', 0)
            ->orderBy('blog.id', 'desc')
            ->paginate(20);
    
        return $return;
    }
    static public function getRecordFrontCategory($category_id)
    {
        return   self::select('blog.*', 'users.name as user_name', 'category.name as category_name')
            ->join('users', 'users.id', '=', 'blog.user_id')
            ->join('category', 'category.id', '=', 'blog.category_id')
            ->where('blog.category_id','=',$category_id)
            ->where('blog.status','=',1)
            ->where('blog.is_publish','=',1)
            ->where('blog.is_delete','=',0)
            ->orderBy('blog.id','desc')
            ->paginate(20);
    }
    static public function getRecentPost()
    {
        return   self::select('blog.*', 'users.name as user_name', 'category.name as category_name')
            ->join('users', 'users.id', '=', 'blog.user_id')
            ->join('category', 'category.id', '=', 'blog.category_id')
            ->where('blog.status','=',1)
            ->where('blog.is_publish','=',1)
            ->where('blog.is_delete','=',0)
            ->orderBy('blog.id','desc')
            ->limit(3)
            ->get();
    }
    static public function getRelatedPost($category_id,$id)
    {
        return   self::select('blog.*', 'users.name as user_name', 'category.name as category_name')
            ->join('users', 'users.id', '=', 'blog.user_id')
            ->join('category', 'category.id', '=', 'blog.category_id')
            ->where('blog.id','!=', $id)
            ->where('blog.category_id','=', $category_id)
            ->where('blog.status','=',1)
            ->where('blog.is_publish','=',1)
            ->where('blog.is_delete','=',0)
            ->orderBy('blog.id','desc')
            ->limit(5)
            ->get();
    }

    static public function getRecord()
    {
        $return  = self::select('blog.*', 'users.name as user_name', 'category.name as category_name')
            ->join('users', 'users.id', '=', 'blog.user_id')
            ->join('category', 'category.id', '=', 'blog.category_id');

            if(!empty(Auth::check()) && Auth::user()->is_admin != 1)
            {
                $return = $return->where('blog.user_id','=',Auth::user()->id);
            }
            if(!empty(request()->get('id')))
            {
                $return = $return->where('blog.id','=',request()->get('id'));
            }
            if(!empty(request()->get('username')))
            {
                $return = $return->where('users.name','like', '%'.request()->get('username').'%');
            }
            if(!empty(request()->get('title')))
            {
                $return = $return->where('blog.title','like', '%'.request()->get('title').'%');
            }
            if(!empty(request()->get('category')))
            {
                $return = $return->where('category.name','like', '%'.request()->get('category').'%');
            }
            if(!empty(request()->get('is_publish')))
            {
                $is_publish = request()->get('is_publish');
                if($is_publish == 100)
                {
                    $is_publish = 0;
                }
                $return = $return->where('blog.is_publish','=',request()->get('is_publish'));
            }
            if(!empty(request()->get('status')))
            {
                $status = request()->get('status');
                if($status == 100)
                {
                    $status = 0;
                }
                $return = $return->where('blog.status','=',request()->get('status'));
            }
            if(!empty(request()->get('start_date')))
            {
                $return = $return->whereDate('blog.created_at','>=',request()->get('start_date'));
            }
            if(!empty(request()->get('end_date')))
            {
                $return = $return->whereDate('blog.created_at','<=',request()->get('end_date'));
            }
            $return = $return->where('blog.is_delete', '=', 0)
            ->orderBy('blog.id', 'desc')
            ->paginate(30);
        return $return;
    }
    public function getImage()
    {
        if (!empty($this->image_file) && file_exists('upload/blog/' . $this->image_file)) {
            return url('upload/blog/' . $this->image_file);
        } else {
            return "";
        }
    }
    public function getTag()
    {
        return $this->hasMany(BlogTagsModel::class, 'blog_id');
    }
}
