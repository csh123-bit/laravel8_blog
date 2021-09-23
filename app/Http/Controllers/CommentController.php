<?php

namespace App\Http\Controllers;

use App\Comments;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request){
        $input['from_user']=$request->user()->id;
        $input['on_post']=$request->input('on_post');
        $input['body']=$request->input('body');
        $slug=$request->input('slug');
        Comments::create($input);
        return redirect($slug)->with('message','댓글 작성 성공');
    }
}
