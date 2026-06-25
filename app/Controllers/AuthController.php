<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = model('App\Models\UserModel');
    }

    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return $this->redirectByRole();
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        if (!$email || !$password) {
            return redirect()->back()->withInput()->with('error', 'Email and password are required.');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->userModel->verifyPassword($password, $user->password)) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        $sessionData = [
            'isLoggedIn' => true,
            'user_id'    => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->role,
            'avatar'     => $user->avatar,
        ];

        session()->set($sessionData);

        if ($remember) {
            helper('cookie');
            $token = bin2hex(random_bytes(32));
            set_cookie('remember_token', $token, 86400 * 30);
        }

        session()->setFlashdata('message', 'Welcome back, ' . $user->name . '!');

        return $this->redirectByRole();
    }

    public function register()
    {
        if (session()->get('isLoggedIn')) {
            return $this->redirectByRole();
        }

        return view('auth/register');
    }

    public function attemptRegister()
    {
        $rules = [
            'name'                  => 'required|min_length[3]|max_length[100]',
            'email'                 => 'required|valid_email|is_unique[users.email]',
            'password'              => 'required|min_length[6]',
            'password_confirm'      => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = $this->userModel->createUser([
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'role'     => 'buyer',
        ]);

        if (!$userId) {
            return redirect()->back()->withInput()->with('error', 'Registration failed. Please try again.');
        }

        $sessionData = [
            'isLoggedIn' => true,
            'user_id'    => $userId,
            'name'       => $this->request->getPost('name'),
            'email'      => $this->request->getPost('email'),
            'role'       => 'buyer',
            'avatar'     => null,
        ];

        session()->set($sessionData);
        session()->setFlashdata('message', 'Account created successfully! Welcome!');

        return redirect()->to('/');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('message', 'You have been logged out.');
    }

    private function redirectByRole()
    {
        $role = session()->get('role');

        if ($role === 'owner') {
            return redirect()->to('/admin/dashboard');
        }

        return redirect()->to('/');
    }
}
