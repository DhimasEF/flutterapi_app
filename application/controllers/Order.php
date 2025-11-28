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

        // 1. Insert orders
        $orderData = [
            "id_buyer"      => $id_buyer,
            "total_price"   => $art['price'],
            "payment_status"=> "pending",
            "order_status"  => "pending",
        ];

        $id_order = $this->M_Order->insert_order($orderData);

        // 2. Insert orders_item
        $itemData = [
            "id_order"   => $id_order,
            "id_artwork" => $id_artwork,
            "price"      => $art['price']
        ];
        $this->M_Order->insert_order_item($itemData);

        // 3. Update artwork -> sold
        $this->M_Artwork->set_sold($id_artwork);

        echo json_encode([
            "status" => true,
            "message" => "Order created successfully",
            "id_order" => $id_order
        ]);
    }
}
