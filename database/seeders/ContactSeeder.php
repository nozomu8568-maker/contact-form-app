<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Faker\Factory;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('ja_JP');
        $categoryIds = Category::pluck('id');
        $tagIds = Tag::pluck('id');

        for ($i = 0; $i < 20; $i++) {
            $contact = Contact::create([
                'category_id' => $categoryIds->random(),
                'first_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'gender' => $faker->numberBetween(1, 3),
                'email' => $faker->safeEmail(),
                'tel' => $faker->randomElement(['090', '080', '070']).$faker->numerify('########'),
                'address' => $faker->prefecture().$faker->city().$faker->streetAddress(),
                'building' => $faker->boolean(60) ? $faker->lastName().'ビル'.$faker->numberBetween(101, 999).'号室' : null,
                'detail' => $faker->realText(100),
            ]);

            $contact->tags()->attach($tagIds->random(random_int(1, 3))->all());
        }
    }
}
