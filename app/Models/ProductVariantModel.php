<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductVariantModel extends Model
{
    protected $table            = 'product_variants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['product_id', 'sku', 'price', 'stock', 'image', 'sort_order', 'attributes'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $validationRules = [
        'product_id' => 'required|integer|is_natural_no_zero',
        'sku'        => 'permit_empty|max_length[100]',
        'price'      => 'permit_empty|numeric|greater_than_equal_to[0]',
        'stock'      => 'required|integer|greater_than_equal_to[0]',
        'sort_order' => 'permit_empty|integer',
    ];

    public function getByProduct(int $productId)
    {
        return $this->where('product_id', $productId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function getVariantByAttributes(int $productId, array $attributes)
    {
        $variants = $this->getByProduct($productId);
        foreach ($variants as $v) {
            $vAttrs = json_decode($v->attributes, true) ?? [];
            $match = true;
            foreach ($attributes as $key => $val) {
                if (!isset($vAttrs[$key]) || $vAttrs[$key] !== $val) {
                    $match = false;
                    break;
                }
            }
            if ($match) return $v;
        }
        return null;
    }

    public function getDistinctAttributes(int $productId): array
    {
        $variants = $this->getByProduct($productId);
        $attrs = [];
        foreach ($variants as $v) {
            $vAttrs = json_decode($v->attributes, true) ?? [];
            foreach ($vAttrs as $key => $val) {
                if (!isset($attrs[$key])) $attrs[$key] = [];
                if (!in_array($val, $attrs[$key])) $attrs[$key][] = $val;
            }
        }
        return $attrs;
    }

    public function deleteByProduct(int $productId): void
    {
        $this->where('product_id', $productId)->delete();
    }

    public function getTotalStock(int $productId): int
    {
        $row = $this->select('COALESCE(SUM(stock), 0) as total')
            ->where('product_id', $productId)
            ->get()
            ->getRow();
        return $row ? (int) $row->total : 0;
    }
}
