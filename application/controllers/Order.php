<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('M_Order');
        $this->load->model('M_Artwork');
    }

    public function create()
    {
        header("Content-Type: application/json");

        $json = json_decode(file_get_contents("php://input"), true);

        if (!$json || !isset($json['id_buyer']) || !isset($json['id_artwork'])) {
            echo json_encode(["status" => false, "message" => "Invalid request"]);
            return;
        }

        $id_buyer   = $json['id_buyer'];
        $id_artwork = $json['id_artwork'];

        // Ambil harga artwork
        $art = $this->M_Artwork->get_detail($id_artwork);

        if (!$art) {
            echo json_encode(["status" => false, "message" => "Artwork not found"]);
            return;
        }

        if ($art['status'] == 'sold') {
            echo json_encode(["status" => false, "message" => "Artwork already sold"]);
            return;
        }

        // Cek apakah buyer sudah pernah order artwork ini
        $existing = $this->M_Order->check_existing_order($id_buyer, $id_artwork);

        if ($existing) {
            echo json_encode([
                "status" => false,
                "message" => "You already ordered this artwork",
                "id_order" => $existing['id_order']
            ]);
            return;
        }


        // 1. Insert orders
        $orderData = [
            "id_buyer"      => $id_buyer,
            "total_price"   => $art['price'],
            "payment_status"=> "pending",
            "order_status"  => "waiting",
        ];

        $id_order = $this->M_Order->insert_order($orderData);

        // 2. Insert orders_item
        $itemData = [
            "id_order"   => $id_order,
            "id_artwork" => $id_artwork,
            "price"      => $art['price']
        ];
        $this->M_Order->insert_order_item($itemData);

        echo json_encode([
            "status" => true,
            "message" => "Order created successfully",
            "id_order" => $id_order
        ]);
    }

    public function my_as_buyer()
    {
        $id_buyer = $this->input->get('id_buyer');

        if (!$id_buyer) {
            echo json_encode([
                'status' => false,
                'message' => 'id_buyer tidak boleh kosong',
                'data' => []
            ]);
            return;
        }

        $orders = $this->M_Order->get_my_orders($id_buyer);

        foreach ($orders as &$order) {
            if (!empty($order['images'])) {
                $order['images'] = explode(",", $order['images']); // parse manual, NO JSON ERROR
            } else {
                $order['images'] = [];
            }
        }

        echo json_encode([
            'status' => true,
            'data' => $orders
        ]);
    }

    public function my_as_creator()
    {
        $id_creator = $this->input->get('id_creator');

        if (!$id_creator) {
            echo json_encode([
                'status' => false,
                'message' => 'id_creator tidak boleh kosong',
                'data' => []
            ]);
            return;
        }

        $orders = $this->M_Order->get_orders_by_creator($id_creator);

        // Parse images
        foreach ($orders as &$order) {
            if (!empty($order['images'])) {
                $order['images'] = explode(",", $order['images']);
            } else {
                $order['images'] = [];
            }
        }

        echo json_encode([
            'status' => true,
            'data' => $orders
        ]);
    }

    public function detail()
    {
        $id_order = $this->input->get('id_order');

        if (!$id_order) {
            echo json_encode([
                'status' => false,
                'message' => 'id_order tidak boleh kosong',
                'data' => []
            ]);
            return;
        }

        $orders = $this->M_Order->get_order_detail($id_order);

        // Parse images
        foreach ($orders as &$order) {
            if (!empty($order['images'])) {
                $order['images'] = explode(",", $order['images']);
            } else {
                $order['images'] = [];
            }
        }

        echo json_encode([
            'status' => true,
            'data' => $orders
        ]);
    }


    public function upload_payment() {
        header("Content-Type: application/json");

        $id_order   = $this->input->post('id_order');
        $amount     = $this->input->post('amount');

        if (!$id_order || !$amount) {
            echo json_encode([
                'status' => false,
                'message' => 'Data tidak lengkap'
            ]);
            return;
        }

        // ============================
        //  CEK ORDER ADA ATAU TIDAK
        // ============================
        $order = $this->M_Order->getOrder($id_order);
        if (!$order) {
            echo json_encode([
                'status' => false,
                'message' => 'Order tidak ditemukan'
            ]);
            return;
        }

        // ============================
        //  CONFIG FILE UPLOAD
        // ============================
        $config['upload_path']      = './uploads/payment/';
        $config['allowed_types']    = 'jpg|jpeg|png|webp';
        $config['max_size']         = 2048; // 2MB
        $config['file_name']        = "bukti_" . time();

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('bukti')) {
            echo json_encode([
                'status' => false,
                'message' => $this->upload->display_errors('', '')
            ]);
            return;
        }

        $fileData = $this->upload->data();
        $filename = $fileData['file_name'];

        // ============================
        //  UPDATE DATABASE
        // ============================
        $update = $this->M_Order->updatePaymentProof(
            $id_order,
            $amount,
            $filename
        );

        echo json_encode([
            'status' => $update,
            'message' => $update ? 'Bukti pembayaran berhasil diupload' : 'Gagal menyimpan data'
        ]);
    }

}
