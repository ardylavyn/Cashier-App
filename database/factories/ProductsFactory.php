<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductsFactory extends Factory
{
    public function definition(): array
    {
        $products = [

            // Main Course
            ['Grilled Chicken Steak', 55000],
            ['Beef Steak', 85000],
            ['Chicken Parmigiana', 62000],
            ['Fish and Chips', 58000],
            ['Chicken Katsu', 48000],
            ['BBQ Chicken', 52000],
            ['Grilled Salmon', 89000],
            ['Beef Teriyaki', 57000],
            ['Chicken Schnitzel', 59000],
            ['Roasted Chicken', 65000],

            // Rice Bowl
            ['Chicken Teriyaki Bowl', 38000],
            ['Salted Egg Chicken Bowl', 42000],
            ['Beef Black Pepper Bowl', 45000],
            ['Chicken Katsu Bowl', 40000],
            ['Spicy Beef Bowl', 47000],
            ['Gyudon Bowl', 48000],
            ['Chicken Sambal Matah Bowl', 39000],
            ['Korean Chicken Bowl', 43000],

            // Pasta
            ['Spaghetti Bolognese', 45000],
            ['Spaghetti Carbonara', 48000],
            ['Aglio Olio', 42000],
            ['Creamy Mushroom Pasta', 47000],
            ['Seafood Marinara', 58000],
            ['Fettuccine Alfredo', 50000],
            ['Lasagna', 55000],
            ['Penne Arrabbiata', 45000],

            // Pizza
            ['Margherita Pizza', 65000],
            ['Pepperoni Pizza', 72000],
            ['Hawaiian Pizza', 70000],
            ['Meat Lovers Pizza', 79000],
            ['BBQ Chicken Pizza', 75000],
            ['Cheese Pizza', 62000],
            ['Seafood Pizza', 82000],

            // Appetizer & Snack
            ['French Fries', 25000],
            ['Cheese Fries', 30000],
            ['Onion Rings', 28000],
            ['Chicken Wings', 38000],
            ['Chicken Popcorn', 32000],
            ['Mozzarella Sticks', 35000],
            ['Potato Wedges', 28000],
            ['Garlic Bread', 22000],
            ['Nachos', 36000],
            ['Mix Platter', 52000],
            ['Spring Roll', 25000],
            ['Fried Banana', 24000],

            // Dessert
            ['Chocolate Lava Cake', 35000],
            ['Cheesecake', 36000],
            ['Tiramisu', 38000],
            ['Brownies with Ice Cream', 34000],
            ['Waffle Ice Cream', 39000],
            ['Pancake Maple Syrup', 32000],
            ['Red Velvet Cake', 37000],
            ['Croffle', 33000],

            // Coffee
            ['Espresso', 22000],
            ['Americano', 25000],
            ['Cappuccino', 30000],
            ['Cafe Latte', 32000],
            ['Flat White', 32000],
            ['Mocha', 34000],
            ['Caramel Macchiato', 36000],
            ['Hazelnut Latte', 35000],
            ['Vanilla Latte', 35000],
            ['Affogato', 34000],

            // Non Coffee
            ['Chocolate Latte', 32000],
            ['Matcha Latte', 34000],
            ['Red Velvet Latte', 35000],
            ['Taro Latte', 34000],
            ['Thai Tea', 28000],
            ['Green Tea Latte', 33000],
            ['Cookies and Cream', 35000],
            ['Fresh Milk', 25000],

            // Tea
            ['Lemon Tea', 24000],
            ['Lychee Tea', 28000],
            ['Peach Tea', 28000],
            ['Jasmine Tea', 24000],
            ['Earl Grey Tea', 27000],
            ['Chamomile Tea', 29000],

            // Fresh Juice
            ['Orange Juice', 30000],
            ['Avocado Juice', 32000],
            ['Mango Juice', 32000],
            ['Watermelon Juice', 28000],
            ['Apple Juice', 30000],
            ['Pineapple Juice', 30000],

            // Smoothies
            ['Strawberry Smoothie', 36000],
            ['Mango Smoothie', 36000],
            ['Banana Smoothie', 34000],
            ['Mixed Berry Smoothie', 38000],
            ['Chocolate Smoothie', 36000],

            // Mocktail
            ['Virgin Mojito', 34000],
            ['Blue Ocean', 36000],
            ['Sunrise Mocktail', 35000],
            ['Berry Blast', 36000],
            ['Tropical Breeze', 37000],
            ['Passion Spark', 36000],
        ];

        $product = fake()->randomElement($products);

        return [
            'image' => fake()->imageUrl(640, 480, 'food'),
            'name' => $product[0],
            'price' => $product[1],
            'stock' => fake()->numberBetween(10, 80),
        ];
    }
}
