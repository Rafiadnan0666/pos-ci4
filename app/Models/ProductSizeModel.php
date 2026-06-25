<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductSizeModel extends Model
{
    protected $table            = 'product_sizes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['product_id', 'size', 'stock'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $validationRules = [
        'product_id' => 'required|integer|is_natural_no_zero',
        'size'       => 'required|max_length[50]',
        'stock'      => 'required|integer|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;

    public function getByProduct(int $productId)
    {
        return $this->where('product_id', $productId)
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function getTotalStock(int $productId): int
    {
        $row = $this->select('COALESCE(SUM(stock), 0) as total')
            ->where('product_id', $productId)
            ->get()
            ->getRow();

        return $row ? (int) $row->total : 0;
    }

    public function deleteByProduct(int $productId): void
    {
        $this->where('product_id', $productId)->delete();
    }
}
