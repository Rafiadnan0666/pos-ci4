<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductImageModel extends Model
{
    protected $table            = 'product_images';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['product_id', 'image', 'sort_order'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $validationRules = [
        'product_id' => 'required|integer|is_natural_no_zero',
        'image'      => 'required|max_length[255]',
        'sort_order' => 'permit_empty|integer',
    ];

    public function getByProduct(int $productId)
    {
        return $this->where('product_id', $productId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function deleteByProduct(int $productId): void
    {
        $images = $this->getByProduct($productId);
        foreach ($images as $img) {
            if ($img->image && file_exists(ROOTPATH . 'public/' . $img->image)) {
                @unlink(ROOTPATH . 'public/' . $img->image);
            }
        }
        $this->where('product_id', $productId)->delete();
    }
}
