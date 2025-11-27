<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Register extends CI_Controller {
    public function __construct() {
        parent::__construct();

        // IZINKAN AKSES DARI SEMUA ORIGIN (sementara)
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Authorization, Content-Type");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
        header("Access-Control-Expose-Headers: Content-Length, Content-Range");
        header("Content-Type: application/json");

        // ⚠️ WAJIB: tangani permintaan preflight dari browser
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit(); // hentikan eksekusi lebih lanjut agar gak lanjut ke controller
        }

        $this->load->database();
    }

    public function auth()   {
        // Baca input JSON dari Flutter
        $raw_input = file_get_contents('php://input');
        log_message('debug', 'Raw input: ' . $raw_input);

        $data = json_decode($raw_input, true);

        $data = json_decode(file_get_contents('php://input'), true);

        // Validasi input
        if (!$data || empty($data['username']) || empty($data['password'])) {
            echo json_encode([
                'status' => false,
                'message' => 'Username dan password wajib diisi'
            ]);
            return;
        }

        $username = trim($data['username']);
        $password = password_hash($data['password'], PASSWORD_BCRYPT);
        $email = !empty($data['email']) ? trim($data['email']) : '';

        // Cek apakah username sudah dipakai
        $check = $this->db->get_where('users', ['username' => $username])->row_array();
        if ($check) {
            echo json_encode([
                'status' => false,
                'message' => 'Username sudah digunakan'
            ]);
            return;
        }

        // Simpan ke database
        $insert = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => 'creator', // default role
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('users', $insert);

        if ($this->db->affected_rows() > 0) {
            echo json_encode([
                'status' => true,
                'message' => 'Registrasi berhasil ✅',
                'data' => [
                    'username' => $username,
                    'email' => $email
                ]
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Registrasi gagal ❌'
            ]);
        }
    }
}
?>