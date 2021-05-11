<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ImageRepository
{
    /**
     * Upload the image
     * @param $image
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function uploadImage($image, $name=null)
    {
        return $this->upload($image);
    }
    /**
     * Upload the image
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    private function upload($user_id, $image, $name=null)
    {
        try{
            $name == null ? $name = uniqid() : $name = $name;
            $path = Storage::disk('public')->put('images', $image);
            $user = User::where('id', '=', $user_id)->get()->first();
            $user->fill(['avatar' => $path,]);
            $user->save();
            return $uploadedImage;
        }catch (\Exception $exception){
            return response('Internal Server Error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}