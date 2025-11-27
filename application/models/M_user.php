<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_user extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    // Fungsi untuk cek login
    public function check_login($username, $password) {
        // Ganti "users" sesuai nama tabel kamu di database
        $this->db->where('username', $username);
        $this->db->where('password', $password); // pastikan nanti di-hash (misalnya md5/sha1/bcrypt)
        $query = $this->db->get('users');

        if ($query->num_rows() > 0) {
            return $query->row(); // ambil data user pertama
        } else {
            return false;
        }
    }

    public function get_by_username($username) {
        return $this->db->get_where('users', ['username' => $username])->row_array();
    }

    public function get_by_token($token) {
        return $this->db->get_where('users', ['token_login' => $token])->row_array();
    }

    public function get_user_by_id($id_user) {
        return $this->db->get_where('users', ['id_user' => $id_user])->row_array();
    }

    public function update_user($id_user, $data) {
        $this->db->where('id_user', $id_user);
        return $this->db->update('users', $data);
    }
    
    public function get_all_users() {
        return $this->db->select('id_user, username, email, name, role, avatar, created_at')
                        ->from('users')
                        ->order_by('id_user', 'DESC')
                        ->get()
                        ->result_array();
    }

    public function reset_password($id_user, $new_hash) {
        return $this->db->where('id_user', $id_user)
                        ->update('users', ['password' => $new_hash]);
    }

}
