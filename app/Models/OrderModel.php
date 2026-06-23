<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table            = 'orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'order_number', 'buyer_id',
        'shipping_address', 'city_id', 'courier_name', 'courier_service',
        'shipping_cost', 'gross_amount', 'payment_status', 'midtrans_snap_token',
        'biteship_order_id', 'tracking_number', 'tracking_url',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $validationRules = [
        'order_number' => 'required|is_unique[orders.order_number]|max_length[50]',
        'buyer_id'     => 'required|integer',
        'gross_amount' => 'required|numeric',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;

    public function generateOrderNumber(): string
    {
        $prefix = 'INV-' . date('Ymd');
        $last = $this->select('order_number')
            ->like('order_number', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last->order_number);
            $seq = (int) end($parts) + 1;
        }

        return sprintf('%s-%04d', $prefix, $seq);
    }

    public function getWithItems(string $orderNumber)
    {
        return $this->select('orders.*, users.name as buyer_name, users.email as buyer_email, users.phone as buyer_phone')
            ->join('users', 'users.id = orders.buyer_id')
            ->where('order_number', $orderNumber)
            ->first();
    }

    public function updateBiteship(int $orderId, array $biteshipData): bool
    {
        $update = [];
        if (!empty($biteshipData['biteship_order_id'])) {
            $update['biteship_order_id'] = $biteshipData['biteship_order_id'];
        }
        if (!empty($biteshipData['tracking_number'])) {
            $update['tracking_number'] = $biteshipData['tracking_number'];
        }
        if (!empty($biteshipData['tracking_url'])) {
            $update['tracking_url'] = $biteshipData['tracking_url'];
        }
        if (empty($update)) return false;

        return $this->update($orderId, $update);
    }
}
