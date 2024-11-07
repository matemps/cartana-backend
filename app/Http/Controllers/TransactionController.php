<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\Reservation;
use App\Models\Transaction;
use Illuminate\Validation\Rule;


class TransactionController extends Controller
{
    public function getTransactions(Request $request)
    {
        $user_id = auth()->user()->id;

        $result = [];
        $transactions = Transaction::select('*')->where('user_id', '=', $user_id)->get();
        foreach ($transactions as $t)
        {
            $filtered_t = collect($t)->filter(function ($val) {
                return !is_null($val);
            });
            array_push($result, $filtered_t);
        }
        return $result;
    }

    public function getTransactionById(Request $request, $transaction_id)
    {
        $user_id = auth()->user()->id;

        $transaction = Transaction::select('*')->where('user_id', '=', $user_id)->where('id', '=', $transaction_id)->first();
        $filtered_transaction = collect($transaction)->filter(function ($val) {
            return !is_null($val);
        });
        return $filtered_transaction;
    }
    public function createTransaction(Request $request)
    {
        $user_id = auth()->user()->id;
        $states = [
            'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA', 'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 
            'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY'
        ];        
        $request->validate(
            [
                'car_id' => 'required|integer|digits_between:1,11',
                'state' => ['required', 'string', Rule::in($states)],
                'city' => 'required|string|min:1|max:32',
                'zip' => 'required|string|size:5',
                'address' => 'required|string|min:1|max:32',
                'payment' => ['required', 'string', Rule::in(['chkBank', 'chkCard'])]
            ]
        );

        $reservation = Reservation::select('*')->where('car_id', '=', $request->car_id)->where('user_id', '=', $user_id);
        if (!$reservation->exists())
        {
            return response(
                [
                    'message' => 'reservation does not exist.'
                ],
                404
            );
        }

        if ($request->payment == 'chkBank')
        {
            $request->validate(
                [
                    'acctType' => ['required', 'string', Rule::in(['checkings', 'bankings'])],
                    'accountFName' => 'required|string|min:1|max:32',
                    'accountLName' => 'required|string|min:1|max:32',
                    'accountNumber' => 'required|string|min:1|max:32',
                    'routing' => 'required|string|size:9'
                ]
            );

            $result = Transaction::create(
                [
                    'user_id' => $user_id,
                    'car_id' => $request->car_id,
                    'state' => $request->state,
                    'city' => $request->city,
                    'zip' => $request->zip,
                    'address' => $request->address,
                    'payment' => $request->payment,
                    'acctType' => $request->acctType,
                    'accountFName' => $request->accountFName,
                    'accountLName' => $request->accountLName,
                    'accountNumber' => $request->accountNumber,
                    'routing' => $request->routing
                ]
            );

            // clear car visibility
            $car = Car::find($request->car_id);
            $car->visible = 0;
            $car->save();

            return response($result, 201);
        }
        else if ($request->payment == 'chkCard')
        {
            $request->validate(
                [
                    'cardFName' => 'required|string|min:1|max:32',
                    'cardLName' => 'required|string|min:1|max:32',
                    'cardNumber' => 'required|string|min:1|max:32',
                    'exp' => 'required|string|size:4',
                    'cvv' => 'required|string|size:3'
                ]
            );

            $expMonth = substr($request->exp, 0, 2);
            $expYear = substr($request->exp, 2);
            $timezone = new \DateTimeZone('UTC');
            $exp = \DateTime::createFromFormat('my', $expMonth.$expYear, $timezone)->modify('+1 month first day of midnight');
            $now = \DateTime::createFromFormat('my', 'now', $timezone);
            if ($exp < $now)
            {
                return response(
                    [
                        'message' => 'card expired.'
                    ],
                    406
                );
            }

            $result = Transaction::create(
                [
                    'user_id' => $user_id,
                    'car_id' => $request->car_id,
                    'state' => $request->state,
                    'city' => $request->city,
                    'zip' => $request->zip,
                    'address' => $request->address,
                    'payment' => $request->payment,
                    'cardFName' => $request->cardFName,
                    'cardLName' => $request->cardLName,
                    'cardNumber' => $request->cardNumber,
                    'exp' => $request->exp,
                    'cvv' => $request->cvv
                ]
            );

            // clear car visibility
            $car = Car::find($request->car_id);
            $car->visible = 0;
            $car->save();

            // delete reservation
            $reservation = Reservation::select('*')->where('car_id', '=', $request->car_id)->first();
            $reservation->delete();

            return response($result, 201);
        }
    }
}
