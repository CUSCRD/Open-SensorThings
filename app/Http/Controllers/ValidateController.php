<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class ValidateController extends Controller
{
    public static function validateImage(Request $request)
    {
        $validationArray = [
            'image' => [
                'required',
                'image',
                // 'file_extension:jpeg,png',
                'mimes:jpeg,png',
                'mimetypes:image/jpeg,image/png',
                'max:2048',
            ]
        ];
        $customMessages = [];
        $validator = Validator::make($request->all(), $validationArray, $customMessages);
        return $validator;
    }
}
