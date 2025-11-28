<?php
class M_Order extends CI_Model {

    public function insert_order($data) {
        $this->db->insert('orders', $data);
        return $this->db->insert_id();
    }

    public function insert_order_item($data) {
        return $this->db->insert('order_item', $data);
    }
}
