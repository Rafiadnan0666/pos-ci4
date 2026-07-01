<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderItemModel extends Model
{
    protected $table            = 'order_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['order_id', 'product_id', 'size', 'variant_id', 'variant_label', 'quantity', 'price', 'subtotal'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $validationRules = [
        'order_id'   => 'required|integer',
        'product_id' => 'required|integer',
        'quantity'   => 'required|integer|greater_than[0]',
        'price'      => 'required|numeric',
        'subtotal'   => 'required|numeric',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;

    public function getByOrderId(int $orderId)
    {
        return $this->select('order_items.*, products.name, products.image, products.weight_grams')
            ->join('products', 'products.id = order_items.product_id')
            ->where('order_id', $orderId)
            ->findAll();
    }
}
