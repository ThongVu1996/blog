<?php

namespace App\Services;

use App\Helpers\ResponseHelper;
use App\Models\Category;
use App\Models\Post;
use App\Services\BaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryService extends BaseService
{
    protected $auth;


    protected $responseHelper;

    public function __construct(ResponseHelper $responseHelper) {
        $this->auth = auth('api'); 
        $this->responseHelper = $responseHelper;
    }

    /**
     * @return Eloquent | QueryBuilder
     */
    public function makeNewQuery()
    {
    }

    /**
     * store
     */
    public function store(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * index
     */
    public function index()
    {
        return Category::all();
    }

    public function update(Request $request, $id): bool | array
    {
        $category = Category::find($id);

        if (!$category) {
            return ['error' => 'Category not found'];
        }
        return $category->update($request->toArray());
    }

    public function delete($id): bool | array
    {
        try {
            DB::beginTransaction();
            if ($id == 1) {
            return ['error' => 'Không thể xóa chuyên mục mặc định'];
            }
            $category = Category::find($id);
            if (!$category) {
                return ['error' => 'Category not found'];
            }
            Post::where('category_id', $id)->update(['category_id' => 1]);
            $category->delete();
            DB::commit();
            return true;
        }
        catch (\Exception $e) {
            DB::rollBack();
            return ['error' => 'Error deleting category: ' . $e->getMessage()];
        }
    }
}
