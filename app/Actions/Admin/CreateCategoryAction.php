<?php

namespace App\Actions\Admin;

use App\Models\Admin;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CreateCategoryAction
{
    public function execute(array $data, ?Admin $admin = null): Category
    {
        return DB::transaction(function () use ($data, $admin) {
            if ($admin) {
                $data['created_by_admin_id'] = $admin->id;
            }

            return Category::create($data);
        });
    }
}
