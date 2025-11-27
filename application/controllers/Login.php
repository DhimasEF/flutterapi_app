<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Login extends CI_Controller {

    private $secret_key = "flystudio_secret_key"; // ubah sesuai kebutuhan

    public function __construct() {
        parent::__construct();
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json');
        $this->load->model('M_user');

        // load composer autoload (JWT)
        require_once APPPATH . '../vendor/autoload.php';
    }

    // Endpoint tes koneksi
    public function index() {
        $connected = $this->db->initialize();
        echo json_encode([
            "status" => $connected ? true : false,
            "message" => $connected ? "API Ready ✅ Database connected" : "API Ready ❌ Database not connected"
        ]);
    }

    // Endpoint utama: login dan generate token
    public function auth() {
        log_message('debug', 'Auth function started.');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || empty($data['username']) || empty($data['password'])) {
            log_message('error', 'Missing username or password.');
            echo json_encode(['status' => false, 'message' => 'Username dan password harus diisi']);
            return;
        }

        $username = $data['username'];
        $password = $data['password'];
        log_message('debug', 'Username received: ' . $username);

        $user = $this->M_user->get_by_username($username);
        if (!$user) {
            log_message('error', 'User not found: ' . $username);
            echo json_encode(['status' => false, 'message' => 'User tidak ditemukan']);
            return;
        }

        log_message('debug', 'User found: ID ' . $user['id_user']);

        if (!password_verify($password, $user['password'])) {
            log_message('error', 'Password salah untuk user ID ' . $user['id_user']);
            echo json_encode(['status' => false, 'message' => 'Password salah']);
            return;
        }

        log_message('debug', 'Password verified for user ID ' . $user['id_user']);

        $payload = [
            'iss' => 'flystudio_api',
            'aud' => 'flutter_client',
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24),
            'jti' => bin2hex(random_bytes(16)),
            'data' => [
                'id_user' => $user['id_user'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'] // ← tambahkan ini
                ]
        ];

        log_message('debug', 'Payload prepared.');

        try {
            $token = JWT::encode($payload, $this->secret_key, 'HS256');
            log_message('debug', 'Token generated successfully.');
        } catch (Exception $e) {
            log_message('error', 'JWT generation failed: ' . $e->getMessage());
            echo json_encode(['status' => false, 'message' => 'Gagal generate token']);
            return;
        }

        // Update token
        $this->db->where('id_user', $user['id_user']);
        $this->db->set('token_login', $token);
        $this->db->update('users');

        log_message('debug', 'Query: ' . $this->db->last_query());

        if ($this->db->affected_rows() > 0) {
            log_message('debug', 'Token updated for user ID: ' . $user['id_user']);
        } else {
            log_message('error', 'Token update failed or unchanged for user ID: ' . $user['id_user']);
        }

        // ✅ Respon ke Flutter
        echo json_encode([
            'status' => true,
            'message' => 'Login berhasil ✅',
            'token' => $token,
            'user' => [
                'id_user' => $user['id_user'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);

        log_message('debug', 'Auth function finished successfully.');
    }


    // ✅ Endpoint untuk verifikasi token (cek valid / expired)
    public function verify() {
        $headers = $this->input->request_headers();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;

        if (!$authHeader) {
            echo json_encode(['status' => false, 'message' => 'Token tidak ditemukan']);
            return;
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode($token, new Key($this->secret_key, 'HS256'));
            echo json_encode([
                'status' => true,
                'message' => 'Token valid ✅',
                'data' => $decoded->data
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => 'Token tidak valid ❌: ' . $e->getMessage()
            ]);
        }
    }

    // Helper agar Authorization terbaca di semua server
    private function getAuthorizationHeader() {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { // Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }


    public function dashboard() {
        $authHeader = $this->getAuthorizationHeader();
        if (!$authHeader) {
            echo json_encode(['status' => false, 'message' => 'Token tidak ditemukan']);
            return;
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode($token, new Key($this->secret_key, 'HS256'));
            $userData = $decoded->data;

            echo json_encode([
                'status' => true,
                'message' => 'Berhasil mengambil data dashboard',
                'data' => [
                    [
                        'title' => 'Selamat datang ' . $userData->username,
                        'description' => 'Email: ' . $userData->email
                    ],
                    [
                        'title' => 'Role',
                        'description' => 'Peranmu di sistem: ' . ($userData->role ?? 'user')
                    ]
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => 'Token tidak valid ❌: ' . $e->getMessage()
            ]);
        }
    }
}
