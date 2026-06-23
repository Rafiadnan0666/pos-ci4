<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'slug', 'description', 'category', 'category_id', 'price', 'stock', 'weight_grams', 'image'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $validationRules = [
        'name'         => 'required|max_length[200]',
        'slug'         => 'required|is_unique[products.slug]|max_length[200]',
        'price'        => 'required|numeric|greater_than[0]',
        'stock'        => 'required|integer|greater_than_equal_to[0]',
        'weight_grams' => 'required|integer|greater_than[0]',
        'category'     => 'required|max_length[50]',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;

    public function getByCategory(string $category)
    {
        return $this->where('category', $category)->findAll();
    }

    public function getCategories(): array
    {
        try {
            $catModel = model('App\Models\CategoryModel');
            return $catModel->orderBy('name', 'ASC')->findAll();
        } catch (\Throwable $e) {
            $cats = $this->distinct()->select('category as name')->orderBy('category', 'ASC')->findAll();
            $result = [];
            foreach ($cats as $c) {
                $obj       = new \stdClass();
                $obj->id   = 0;
                $obj->name = $c->name;
                $obj->slug = url_title($c->name, '-', true);
                $obj->icon = '📦';
                $result[] = $obj;
            }
            return $result;
        }
    }

    public function getLowStock(int $threshold = 5)
    {
        return $this->where('stock <', $threshold)->where('stock >', 0)->orderBy('stock', 'ASC')->findAll();
    }

    public function getOutOfStock()
    {
        return $this->where('stock', 0)->findAll();
    }

    public function decrementStock(int $productId, int $quantity): bool
    {
        return $this->where('id', $productId)
            ->set('stock', "stock - {$quantity}", false)
            ->update();
    }
}
