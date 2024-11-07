<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Car;
use Illuminate\Validation\Rule;

class CarController extends Controller
{
    public function show($car_id)
    {
        $car = Car::find($car_id);

        if ($car->visible) return $car;
        return response(
            [
                'message' => 'Car is no longer listed.'
            ],
            403
        );
    }

    public function search(Request $request)
    {
        $start = $request->start; // car offset
        $count = $request->count; // number of cars returned

        // validate fields
        $makes = DB::table('cars')->select('manufacturer')->distinct()->get()->pluck('manufacturer');
        $models = DB::table('cars')->select('model')->distinct()->get()->pluck('model');
        $bodies = DB::table('cars')->select('body')->distinct()->get()->pluck('body');
        $colors = DB::table('cars')->select('color')->distinct()->get()->pluck('color');
        $request->validate(
            [
                'start' => 'integer',
                'count' => 'integer',
                'manufacturer' => ['string', Rule::in($makes)],
                'model' => ['string', Rule::in($models)],
                'body' => ['string', Rule::in($bodies)],
                'color' => ['string', Rule::in($colors)],
                'priceMin' => 'integer|min:0|max:1000000',
                'priceMax' => 'integer|min:0|max:1000000',
                'odoMin' => 'integer|min:0|max:1000000',
                'odoMax' => 'integer|min:0|max:1000000',
                'yearMin' => 'integer|min:1985|max:2021',
                'yearMax' => 'integer|min:1985|max:2021'
            ]
        );

        $cars = Car::select("*")
            ->when($request->has('manufacturer'), function ($query) use ($request) {
                $query->where('manufacturer', $request->manufacturer);
            })
            ->when($request->has('model'), function ($query) use ($request) {
                $query->where('model', $request->model);
            })
            ->when($request->has('priceMin'), function ($query) use ($request) {
                $query->where('price', '>', $request->priceMin);
            })
            ->when($request->has('priceMax'), function ($query) use ($request) {
                $query->where('price', '<', $request->priceMax);
            })
            ->when($request->has('odoMin'), function ($query) use ($request) {
                $query->where('odo', '>', $request->odoMin);
            })
            ->when($request->has('odoMax'), function ($query) use ($request) {
                $query->where('odo', '<', $request->odoMax);
            })
            ->when($request->has('yearMin'), function ($query) use ($request) {
                $query->where('year', '>', $request->yearMin);
            })
            ->when($request->has('yearMax'), function ($query) use ($request) {
                $query->where('year', '<', $request->yearMax);
            })
            ->when($request->has('body'), function ($query) use ($request) {
                $query->where('body', $request->body);
            })
            ->when($request->has('color'), function ($query) use ($request) {
                $query->where('color', $request->color);
            })
            ->where('visible', '=', '1')
            ->skip($start)->take($count)->get();
        
        $carsCount = Car::select("*")
            ->when($request->has('manufacturer'), function ($query) use ($request) {
                $query->where('manufacturer', $request->manufacturer);
            })
            ->when($request->has('model'), function ($query) use ($request) {
                $query->where('model', $request->model);
            })
            ->when($request->has('priceMin'), function ($query) use ($request) {
                $query->where('price', '>', $request->priceMin);
            })
            ->when($request->has('priceMax'), function ($query) use ($request) {
                $query->where('price', '<', $request->priceMax);
            })
            ->when($request->has('odoMin'), function ($query) use ($request) {
                $query->where('odo', '>', $request->odoMin);
            })
            ->when($request->has('odoMax'), function ($query) use ($request) {
                $query->where('odo', '<', $request->odoMax);
            })
            ->when($request->has('yearMin'), function ($query) use ($request) {
                $query->where('year', '>', $request->yearMin);
            })
            ->when($request->has('yearMax'), function ($query) use ($request) {
                $query->where('year', '<', $request->yearMax);
            })
            ->when($request->has('body'), function ($query) use ($request) {
                $query->where('body', $request->body);
            })
            ->when($request->has('color'), function ($query) use ($request) {
                $query->where('color', $request->color);
            })
            ->where('visible', '=', '1')
            ->count();

        return [
            'cars' => $cars,
            'count' => $carsCount
        ];
    }
}
