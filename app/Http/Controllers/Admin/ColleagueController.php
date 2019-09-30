<?php

namespace App\Http\Controllers\Api;

use App\Colleague;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ColleagueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Colleague::all();

        $response = [
            'msg'   => 'User Created',
            'data_1'   => $data,
        ];
        return response()->json($response, 201);
        // return $response;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, Colleague::rules(false));

        if (!Colleague::create($request->all())) {
            return [
                'message' => 'Bad Request',
                'code' => 400,
            ];
        } else {
            return [
                'message' => 'OK',
                'code' => 200,
            ];
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Colleague  $colleague
     * @return \Illuminate\Http\Response
     */
    public function show(Colleague $colleague)
    {
        return $colleague;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Colleague  $colleague
     * @return \Illuminate\Http\Response
     */
    public function edit(Colleague $colleague)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Colleague  $colleague
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Colleague $colleague)
    {
        $this->validate($request, Colleague::rules(true, $colleague->id));

        if (!$colleague->update($request->all())) {
            return [
                'message' => 'Bad Request',
                'code' => 400,
            ];
        } else {
            return [
                'message' => 'OK',
                'code' => 201,
            ];
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Colleague  $colleague
     * @return \Illuminate\Http\Response
     */
    public function destroy(Colleague $colleague)
    {
        if ($colleague->delete()) {
            return [
                'message' => 'OK',
                'code' => 204,
            ];
        } else {
            return [
                'message' => 'Bad Request',
                'code' => 400,
            ];
        }
    }
}