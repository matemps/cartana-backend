<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\Reservation;

class ReservationController extends Controller
{
    public function getReservation(Request $request)
    {
        $user_id = auth()->user()->id;

        $request->validate(
            [
                'car_id' => 'required|integer|digits_between:1,11'
            ]
        );

        $reservation = Reservation::select('*')->where('car_id', '=', $request->car_id)->where('user_id', '=', $user_id)->first();
        if ($reservation)
        {
            $timeNow = new \DateTime('now');
            $reservation_exp = new \DateTime($reservation->exp_time);
            if ($timeNow > $reservation_exp)
            {
                $reservation->delete();
                return response(
                    [
                        'message' => 'reservation time expired.'
                    ]
                );
            }

            return response($reservation);
        }

        return response(
            [
                'message' => 'reservation not found'
            ],
            404
        );
    }
    
    public function createReservation(Request $request)
    {
        $user_id = auth()->user()->id;

        $request->validate(
            [
                'car_id' => 'required|integer|digits_between:1,11',
            ]
        );

        $car = Car::find($request->car_id);
        if (!$car->visible)
        {
            return response(
                [
                    'message' => 'cannot reserve an unavailable car'
                ],
                403
            );
        }

        $reservation = Reservation::select('*')->where('car_id', '=', $request->car_id)->first();
        $timeNow = new \DateTime('now');

        if ($reservation)
        {
            $reservation_exp = new \DateTime($reservation->exp_time);
            if ($timeNow > $reservation_exp)
            {
                $reservation->delete();
            }
            else
            {
                return response(
                    [
                        'message' => 'reservation currently active'
                    ]
                );
            }
        }

        $expTime = $timeNow->add(new \DateInterval('PT30M')); // 30 minutes
        Reservation::create(
            [
                'car_id' => $request->car_id,
                'user_id' => $user_id,
                'exp_time' => $expTime
            ]
        );

        return response(['message' => 'reservation created'], 201);
    }
}
