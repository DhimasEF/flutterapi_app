<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profil extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('M_user');
        header('Content-Type: application/json');
    }

    // 🔹 Ambil data profil user
    public function get($id_user) {
        $user = $this->M_user->get_user_by_id($id_user);
        if ($user) {
            echo json_encode(['status' => true, 'data' => $user]);
        } else {
            echo json_encode(['status' => false, 'message' => 'User tidak ditemukan']);
        }
    }

    public function update($id_user)    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            echo json_encode(['status' => false, 'message' => 'Data tidak valid']);
            return;
        }

        $updateData = [
            'username' => $data['username'] ?? null,
            'email' => $data['email'] ?? null,
            'name' => $data['name'] ?? null,
            'bio' => $data['bio'] ?? null,
            'avatar' => $data['avatar'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $update = $this->M_user->update_user($id_user, $updateData);

        echo json_encode([
            'status' => $update ? true : false,
            'message' => $update ? 'Profil berhasil diperbarui' : 'Gagal memperbarui profil'
        ]);
    }

    public function upload_avatar_web() {
        // --- Tambahkan ini di baris paling atas ---
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
        // ------------------------------------------

        $json = json_decode(file_get_contents('php://input'), true);
        $id_user = $json['id_user'];
        $avatar_base64 = $json['avatar_base64'];

        if (!$id_user || !$avatar_base64) {
            echo json_encode(['status' => false, 'message' => 'Data tidak lengkap']);
            return;
        }

        // Hapus prefix data URI
        $img = preg_replace('#^data:image/\w+;base64,#i', '', $avatar_base64);
        $data = base64_decode($img);

        // Simpan ke folder uploads/avatar/
        $filename = 'avatar_' . $id_user . '_' . time() . '.png';
        $path = FCPATH . 'uploads/avatar/' . $filename;

        if (!is_dir(FCPATH . 'uploads/avatar/')) {
            mkdir(FCPATH . 'uploads/avatar/', 0777, true);
        }

        file_put_contents($path, $data);

        // Simpan URL ke database
        $url = base_url('uploads/avatar/' . $filename);
        $this->db->where('id_user', $id_user)->update('users', ['avatar' => $url]);

        echo json_encode([
            'status' => true,
            'message' => 'Avatar berhasil diunggah',
            'avatar' => $url
        ]);
    }

}
