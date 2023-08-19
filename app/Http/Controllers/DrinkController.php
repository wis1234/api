<?php

namespace App\Http\Controllers;

use App\Models\Drink;
use App\Models\DrinkImageNew;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DrinkController extends Controller
{
    public function index()
    {
        $drinks = Drink::all();
        return response()->json($drinks);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->only(['name', 'price', 'availability', 'restaurant_code', 'images']), [
            'name' => 'required|string',
            'price' => 'string',
            'availability' => 'string',
            'restaurant_code' => 'required|exists:restaurants,restaurant_code',
            'images' => 'array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Allow multiple image files
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $restaurant = Restaurant::where('restaurant_code', $request->input('restaurant_code'))->first();

        $drinkData = $request->only(['name', 'price', 'availability']);
        $drinkData['restaurant_id'] = $restaurant->id;
        $drinkData['restaurant_name'] = $restaurant->name;

        $drink = Drink::create($drinkData);

        $this->storeImages($drink, $request->file('images'));

        return response()->json(['message' => 'Drink created successfully', 'data' => $drink, 'restaurant_name' => $restaurant->name]);
    }

    public function show($id)
    {
        $drink = Drink::findOrFail($id);
        return response()->json($drink);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->only(['name', 'price', 'availability', 'restaurant_code', 'images']), [
            'name' => 'required|string',
            'price' => 'required|string',
            'availability' => 'required|string',
            'restaurant_code' => 'required|exists:restaurants,restaurant_code',
            'images' => 'array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Allow multiple image files
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $restaurant = Restaurant::where('restaurant_code', $request->input('restaurant_code'))->first();

        $drink = Drink::findOrFail($id);
        $drink->update($request->only(['name', 'price', 'availability']));
        $drink->restaurant_id = $restaurant->id;
        $drink->restaurant_name = $restaurant->name;
        $drink->save();

        $this->storeImages($drink, $request->file('images'));

        return response()->json(['message' => 'Drink updated successfully', 'data' => $drink, 'restaurant_name' => $restaurant->name]);
    }

    public function destroy($id)
    {
        $drink = Drink::findOrFail($id);
        $drink->delete();

        return response()->json(['message' => 'Drink deleted successfully'], Response::HTTP_OK);
    }

    protected function storeImages(Drink $drink, $images)
    {
        if ($images) {
            foreach ($images as $image) {
                $imagePath = $image->store('drink_images', 'public');

                $drink->images()->create([
                    'drink_name' => $drink->name,
                    'image_path' => $imagePath,
                ]);
            }
        }
    }
}
