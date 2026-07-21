<?php

namespace App\Http\Controllers;

use App\Http\Resources\WalletResource;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show(Request $request): WalletResource
    {
        return WalletResource::make($request->user()->wallet);
    }
}
