<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\PostFormRequest;
use App\Http\Controllers\Controller;
use App\Posts;

class PostController extends Controller
{
    public function index(){
        $posts = Posts::where('active',1)->orderBy('created_at','desc')->paginate(5);
        $title = 'Latest Posts';

        return view('home')->withPosts($posts)->withTitle($title);//이부분 보류
    }

    public function create(Request $request){
        if($request->user()->can_post()){
            return view('posts.create');
        }else{
            return redirect('/')->withErrors('당신은 글쓰기에 대한 충분한 허가가 없습니다.');
        }
    }

    public function store(PostFormRequest $request){
        $post = new Posts();
        $post->title=$request->get('title');
        $post->body=$request->get('body');
        $post->slug=Str::slug($post->title);//일단 보류

        $duplicate = Posts::where('slug',$post->slug)->first();
        if($duplicate){
            return redirect('new-post')->withErrors('같은 제목이 이미 존재합니다.')
            ->withInput();
        }
        $post->author_id=$request->user()->id;
        if($request->has('save')){
            $post->active=0;
            $message='post saved successfully';
        }else{
            $post->active=1;
            $message='post published successfully';
        }
        $post->save();
        return redirect('edit/'.$post->slug)->withMessage($message);

    }

    public function show($slug){
        $post = Posts::where('slug',$slug)->first();
        if(!$post){
            return redirect('/')->withErrors('요청하신 페이지가 없습니다.');
        }
        $comments = $post->comments;
        return view('posts.show')->withPost($post)->withComments($comments);//나중에 검색

    }

    public function edit(Request $request, $slug){
        $post = Posts::where('slug',$slug)->first();
        if($post&&($request->user()->id ==$post->author_id||$request->user()->is_admin()))
            return view('posts.edit')->with('post',$post);
        return redirect('/')->withErrors('당신은 충분한 권한이 없습니다.');
    }

    public function update(Request $request){
        $post_id = $request->input('post_id');
        $post = Posts::find($post_id);
        if($post&&($post->author_id==$request->user()->id||$request->user()->is_admin())){
            $title = $request->input('title');
            $slug =Str::slug($title);
            $duplicate = Posts::where('slug',$slug)->first();
            if($duplicate){
                if($duplicate->id!=$post_id){
                    return redirect('edit/'.$post->slug)->withErrors('같은제목이 이미 존재합니다')->withInput();
                }else{
                    $post->slug=$slug;
                }
            }
            $post->title=$title;
            $post->body = $request->input('body');

            if($request->has('save')){
                $post->active=0;
                $message='성공적으로 저장되었습니다.';
                $landing = 'edit/'.$post->slug;
            }else{
                $post->active=1;
                $message='업데이트가 성공적으로 이뤄졌습니다.';
                $landing=$post->slug;
            }
            $post->save();
            return redirect($landing)->withMessage($message);
        }else{
            return redirect('/')->withErrors('당신은 충분한 허가가 없습니다');
        }

    }
    public function destroy(Request $request, $id){
        $post = Posts::find($id);
        if($post&&($post->author_id==$request->user()->id||$request->user()->is_admin())){
            $post->delete();
            $data['message']='성공적으로 삭제되었습니다.';
        }else{
            $data['errors']='잘못된 동작입니다.';
        }return redirect('/')->with($data);
    }
}
