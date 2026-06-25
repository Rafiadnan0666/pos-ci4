<?php

namespace App\Controllers;

use App\Models\UserModel;

class Profile extends BaseController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = model('App\Models\UserModel');
    }

    public function index()
    {
        $user = $this->userModel->find(session()->get('user_id'));

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Please login first');
        }

        return view('profile/index', [
            'user' => $user,
        ]);
    }

    public function update()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Please login first');
        }

        $rules = [
            'name'    => 'required|max_length[100]',
            'email'   => 'required|valid_email',
            'phone'   => 'permit_empty|max_length[20]',
            'address' => 'permit_empty',
        ];

        if ($user->email !== $this->request->getPost('email')) {
            $rules['email'] .= "|is_unique[users.email,id,{$userId}]";
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $avatar = $user->avatar;
        $file   = $this->request->getFile('avatar');

        if ($file && $file->isValid() && $file->getSize() > 0) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (in_array($file->getMimeType(), $allowed, true)) {
                if ($file->getSize() <= 2 * 1024 * 1024) {
                    $uploadDir = ROOTPATH . 'public/uploads/avatars';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0755, true);
                    }
                    $name = $file->getRandomName();
                    $file->move($uploadDir, $name);
                    $avatar = 'uploads/avatars/' . $name;
                    if ($user->avatar && file_exists(ROOTPATH . 'public/' . $user->avatar)) {
                        @unlink(ROOTPATH . 'public/' . $user->avatar);
                    }
                } else {
                    return redirect()->back()->withInput()->with('error', 'Avatar image must be under 2MB');
                }
            } else {
                return redirect()->back()->withInput()->with('error', 'Avatar must be JPG, PNG, WebP, or GIF');
            }
        }

        if ($this->request->getPost('remove_avatar')) {
            if ($user->avatar && file_exists(ROOTPATH . 'public/' . $user->avatar)) {
                @unlink(ROOTPATH . 'public/' . $user->avatar);
            }
            $avatar = null;
        }

        $this->userModel->update($userId, [
            'name'    => $this->request->getPost('name'),
            'email'   => $this->request->getPost('email'),
            'phone'   => $this->request->getPost('phone') ?: null,
            'address' => $this->request->getPost('address') ?: null,
            'avatar'  => $avatar,
        ]);

        session()->set('name', $this->request->getPost('name'));
        session()->set('avatar', $avatar);

        return redirect()->to('/profile')->with('message', 'Profile updated successfully');
    }
}
