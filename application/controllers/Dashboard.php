<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {
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

    public function data() {
        // ✅ Pastikan header bisa dibaca di semua server
        $headers = [];
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } else {
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) == 'HTTP_') {
                    $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                    $headers[$header] = $value;
                }
            }
        }

        // 🔹 Cari header Authorization
        $authHeader = '';
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                $authHeader = trim($value);
                break;
            }
        }

        log_message('debug', '🔹 Authorization header hasil normalisasi: ' . json_encode($authHeader));

        // 🔹 Bersihkan prefix Bearer
        $token = '';
        if (!empty($authHeader) && stripos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
        }

        if (empty($token)) {
            log_message('error', '🚫 Token kosong atau tidak dikirim');
            echo json_encode([
                'status' => false,
                'message' => 'Token tidak ditemukan di header',
                'data' => []
            ]);
            return;
        }

        // 🔹 Cek user dari database
        $user = $this->db->get_where('users', ['token_login' => $token])->row_array();

        if ($user) {
            echo json_encode([
                'status' => true,
                'message' => 'Data ditemukan ✅',
                'data' => [
                    'id_user' => $user['id_user'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Token tidak valid atau user tidak ditemukan ❌',
                'data' => []
            ]);
        }
    }

}
