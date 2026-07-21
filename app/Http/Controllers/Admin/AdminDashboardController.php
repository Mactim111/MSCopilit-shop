<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'productsCount'   => Product::count(),
            'offersCount'     => 10, // пока заглушка
            'categoriesCount' => Category::count(),
            'ordersCount'     => Order::count(),
            'usersCount'      => User::count(),
        ]);
    }
}
