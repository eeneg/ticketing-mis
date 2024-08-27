<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\Office;
use App\Models\Request;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'email' => 'admin',
            'password' => 1,
            'role' => 'admin',
            'name' => 'Admin',
        ]);

        // $office_name = Office::factory(count: 15)->create();

        // $user = User::factory(count: 20)->make()->each(function ($user) use ($office_name) {
        //     $user->office_id = $office_name->random()->id;
        //     $user->save();
        // });
        // $categories = Category::factory(count: 9)->make()->each(function ($category) use ($office_name) {
        //     $category->office_id = $office_name->random()->id;
        //     $category->save();
        // });
        // $subcategories = $categories->each(function ($category) {
        //     $subcategory = Subcategory::factory(rand(2, 4))->create([
        //         'category_id' => $category->id,
        //     ]);
        // });
        // Request::factory(count: 35)->make()->each(function ($request) use ($categories, $subcategories, $office_name, $user) {
        //     $category = $categories->random();
        //     $filteredSubcategories = $subcategories->where('category_id', $category->id);

        //     if ($filteredSubcategories->isNotEmpty()) {
        //         $subcategory = $filteredSubcategories->random();
        //     } else {
        //         $subcategory = Subcategory::firstOrCreate(['category_id' => $category->id, 'name' => 'Default Subcategory']);
        //     }
        //     $request->category_id = $categories->random()->id;
        //     $request->subcategory_id = $subcategory->id;
        //     $request->office_id = $office_name->random()->id;
        //     $request->requestor_id = $user->random()->id;
        //     $request->save();
        // });
    }
}
