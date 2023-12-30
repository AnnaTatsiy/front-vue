<?php

namespace Database\Factories;

use App\Http\Helpers\Utils;
use App\Models\User;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factories\Factory;

class CoachFactory extends Factory
{
    protected $faker;

    public function definition(): array
    {
        $faker = app(Generator::class);

        $gender = $faker->randomElements(['male', 'female'])[0];

        $patronymic = $faker->randomElements(Utils::$patronymic)[0];
        $patronymic .= ($gender == 'male') ? "ич" : "на";

        $name = $faker->firstName($gender);

        $user = User::create(
            [
                'email' => $faker->freeEmail,
                'password' => bcrypt('password'),
                'image_id' => 1,
                'role' => 'coach',
            ]
        );

        return [

            'surname' =>  $faker->lastName($gender),
            'name'=> $name,
            'patronymic' =>  $patronymic,
            'passport' => $faker->isbn10,
            'birth' => $faker->date('Y-m-d', '2001-12-01'),
            'number' => $faker->phoneNumber,
            'user_id' => $user->id,
            'registration' => $faker->address,
            'sale' => true
        ];
    }
}
