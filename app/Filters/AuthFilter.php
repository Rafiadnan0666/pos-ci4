<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        if (!empty($arguments)) {
            $requiredRole = $arguments[0];
            $userRole = $session->get('role');

            if ($requiredRole === 'owner' && $userRole !== 'owner') {
                return redirect()->to('/')->with('error', 'You do not have permission to access this page.');
            }

            if ($requiredRole === 'buyer' && !in_array($userRole, ['buyer', 'owner'], true)) {
                return redirect()->to('/pos')->with('error', 'Please log in as a buyer to continue.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
