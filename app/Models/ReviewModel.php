<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewModel extends Model
{
    protected $table            = 'product_reviews';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['product_id', 'user_id', 'rating', 'review', 'reply', 'replied_at', 'replied_by', 'status'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'product_id' => 'required|integer|is_natural_no_zero',
        'user_id'    => 'required|integer|is_natural_no_zero',
        'rating'     => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[5]',
        'review'     => 'permit_empty',
        'status'     => 'permit_empty|in_list[approved,pending]',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;

    public function getByProduct(int $productId, string $status = 'approved')
    {
        return $this->select('product_reviews.*, users.name as user_name, users.avatar as user_avatar')
            ->join('users', 'users.id = product_reviews.user_id')
            ->where('product_reviews.product_id', $productId)
            ->where('product_reviews.status', $status)
            ->orderBy('product_reviews.created_at', 'DESC')
            ->findAll();
    }

    public function getRatingSummary(int $productId): object
    {
        $result = $this->select('
                COUNT(*) as total,
                ROUND(AVG(rating), 1) as average,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one
            ')
            ->where('product_id', $productId)
            ->where('status', 'approved')
            ->get()
            ->getRow();

        if (!$result || !$result->total) {
            return (object) [
                'total'   => 0,
                'average' => 0,
                'five'    => 0,
                'four'    => 0,
                'three'   => 0,
                'two'     => 0,
                'one'     => 0,
            ];
        }

        return $result;
    }

    public function getAllWithProduct(int $perPage = 20, int $page = 1)
    {
        return $this->select('product_reviews.*, users.name as user_name, users.avatar as user_avatar, products.name as product_name, products.slug as product_slug')
            ->join('users', 'users.id = product_reviews.user_id')
            ->join('products', 'products.id = product_reviews.product_id')
            ->orderBy('product_reviews.created_at', 'DESC')
            ->paginate($perPage, 'default', $page);
    }

    public function hasUserReviewed(int $productId, int $userId): bool
    {
        return $this->where('product_id', $productId)
            ->where('user_id', $userId)
            ->countAllResults() > 0;
    }
}
