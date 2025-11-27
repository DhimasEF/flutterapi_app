<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('M_user');

        // CORS
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        
        // Biar OPTIONS tidak masuk ke controller (fix preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
    }

    // ------------------------
    // GET /user/list
    // ------------------------
    public function list() {
        $users = $this->M_user->get_all_users();

        echo json_encode([
            "status" => true,
            "message" => "List user berhasil diambil",
            "data" => $users
        ]);
    }

    // ------------------------
    // GET /user/detail/{id}
    // ------------------------
    public function detail($id) {
        $user = $this->M_user->get_user_by_id($id);

        if (!$user) {
            echo json_encode([
                "status" => false,
                "message" => "User tidak ditemukan"
            ]);
            return;
        }

        echo json_encode([
            "status" => true,
            "data" => $user
        ]);
    }

    // ------------------------
    // POST /user/reset_password/{id}
    // ------------------------
    public function reset_password($id) {
        $new_password = "user".rand(10000,99999); // contoh auto generate
        $hash = password_hash($new_password, PASSWORD_DEFAULT);

        $update = $this->M_user->reset_password($id, $hash);

        if ($update) {
            echo json_encode([
                "status" => true,
                "message" => "Password berhasil direset",
                "new_password" => $new_password
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "message" => "Gagal reset password"
            ]);
        }
    }
}