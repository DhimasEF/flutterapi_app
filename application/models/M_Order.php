<?php
class M_Order extends CI_Model {

    public function insert_order($data) {
        $this->db->insert('orders', $data);
        return $this->db->insert_id();
    }

    public function insert_order_item($data) {
        return $this->db->insert('order_items', $data);
    }

    public function check_existing_order($id_buyer, $id_artwork)
    {
        return $this->db
            ->select('orders.id_order')
            ->from('orders')
            ->join('order_items', 'order_items.id_order = orders.id_order')
            ->where('orders.id_buyer', $id_buyer)
            ->where('order_items.id_artwork', $id_artwork)
            ->get()
            ->row_array();
    }
    
    public function get_my_orders($id_buyer)
    {
        return $this->db
            ->select("
                orders.*,
                artworks.*,
                artworks.status AS artwork_status,
                GROUP_CONCAT(artwork_images.preview_url SEPARATOR ',') AS images
            ")
            ->from('orders')
            ->join('order_items', 'order_items.id_order = orders.id_order')
            ->join('artworks', 'artworks.id_artwork = order_items.id_artwork')
            ->join('artwork_images', 'artwork_images.id_artwork = artworks.id_artwork', 'left')
            ->where('orders.id_buyer', $id_buyer)
            ->group_by('orders.id_order, artworks.id_artwork')
            ->order_by('orders.id_order', 'DESC')
            ->get()
            ->result_array();
    }

    public function get_order_detail($id_order)
    {
        return $this->db
            ->select("
                orders.*,
                artworks.*,
                artworks.status AS artwork_status,
                GROUP_CONCAT(artwork_images.preview_url SEPARATOR ',') AS images
            ")
            ->from('orders')
            ->join('order_items', 'order_items.id_order = orders.id_order')
            ->join('artworks', 'artworks.id_artwork = order_items.id_artwork')
            ->join('artwork_images', 'artwork_images.id_artwork = artworks.id_artwork', 'left')
            ->where('orders.id_order', $id_order)
            ->group_by('orders.id_order, artworks.id_artwork')
            ->order_by('orders.id_order', 'DESC')
            ->get()
            ->result_array();
    }

    public function get_orders_by_creator($id_creator)
    {
        return $this->db
            ->select("
                orders.*,
                artworks.*,
                artworks.status AS artwork_status,
                GROUP_CONCAT(artwork_images.preview_url SEPARATOR ',') AS images
            ")
            ->from('orders')
            ->join('order_items', 'order_items.id_order = orders.id_order')
            ->join('artworks', 'artworks.id_artwork = order_items.id_artwork')
            ->join('artwork_images', 'artwork_images.id_artwork = artworks.id_artwork', 'left')
            ->where('artworks.id_user', $id_creator)
            ->group_by('orders.id_order, artworks.id_artwork')
            ->order_by('orders.id_order', 'DESC')
            ->get()
            ->result_array();
    }

    public function getOrder($id_order) {
        return $this->db->where('id_order', $id_order)
                        ->get('orders')
                        ->row_array();
    }

    public function updatePaymentProof($id_order, $amount, $file) {
        $data = [
            'payment_status' => 'pending',
            'total_paid'     => $amount,
            'note'  => $file
        ];

        return $this->db->where('id_order', $id_order)
                        ->update('orders', $data);
    }

}
