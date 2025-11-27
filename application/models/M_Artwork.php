<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_Artwork extends CI_Model {

    // ======================================================
    // INSERT ARTWORK
    // ======================================================
    public function insert_artwork($data) {
        $this->db->insert('artworks', $data);
        return $this->db->insert_id();
    }

    // ======================================================
    // INSERT SINGLE IMAGE
    // ======================================================
    public function insert_image($data) {
        return $this->db->insert('artwork_images', $data);
    }

    // ======================================================
    // INSERT TAG MAP (artwork - tag)
    // ======================================================
    public function insert_tag_map($id_artwork, $id_tag) {
        $this->db->insert('artwork_tag_map', [
            "id_artwork" => $id_artwork,
            "id_tag"     => $id_tag
        ]);
    }

    // ======================================================
    // GET draft ARTWORK (draft Only)
    // ======================================================
    public function get_draft() {
        $this->db->select('
            a.*,
            u.username, 
            u.avatar,

            ai.id_image AS image_id,
            ai.image_url,

            atm.id_tag,
            at.tag_name
        ');

        $this->db->from('artworks a');
        $this->db->join('users u', 'u.id_user = a.id_user');

        // Join gambar
        $this->db->join('artwork_images ai', 'ai.id_artwork = a.id_artwork', 'left');

        // Join tag map → tag info
        $this->db->join('artwork_tag_map atm', 'atm.id_artwork = a.id_artwork', 'left');
        $this->db->join('artwork_tags at', 'at.id_tag = atm.id_tag', 'left');

        $this->db->where('a.status', 'draft');
        $this->db->order_by('a.created_at', 'DESC');

        $artworks = $this->db->get()->result_array();

        return $this->attach_images_and_tags($artworks);
    }
    
    // ======================================================
    // GET ALL ARTWORK (Approved Only)
    // ======================================================
    public function get_all() {
        $this->db->select('
            a.*,
            u.username, 
            u.avatar,

            ai.id_image AS image_id,
            ai.image_url,

            atm.id_tag,
            at.tag_name
        ');

        $this->db->from('artworks a');
        $this->db->join('users u', 'u.id_user = a.id_user');

        // Join gambar
        $this->db->join('artwork_images ai', 'ai.id_artwork = a.id_artwork', 'left');

        // Join tag map → tag info
        $this->db->join('artwork_tag_map atm', 'atm.id_artwork = a.id_artwork', 'left');
        $this->db->join('artwork_tags at', 'at.id_tag = atm.id_tag', 'left');

        $this->db->where('a.status', 'published');
        $this->db->order_by('a.created_at', 'DESC');

        $artworks = $this->db->get()->result_array();

        return $this->attach_images_and_tags($artworks);
    }

    // ======================================================
    // GET MY ARTWORK
    // ======================================================
    public function get_by_user($id_user) {
        $this->db->select('
            a.*,
            u.username, 
            u.avatar,

            ai.id_image AS image_id,
            ai.image_url,

            atm.id_tag,
            at.tag_name
        ');

        $this->db->from('artworks a');
        $this->db->join('users u', 'u.id_user = a.id_user');

        // Join gambar
        $this->db->join('artwork_images ai', 'ai.id_artwork = a.id_artwork', 'left');

        // Join tag map → tag info
        $this->db->join('artwork_tag_map atm', 'atm.id_artwork = a.id_artwork', 'left');
        $this->db->join('artwork_tags at', 'at.id_tag = atm.id_tag', 'left');
  
        $this->db->where('a.id_user', $id_user);
        $this->db->order_by('a.created_at', 'DESC');
        $artworks = $this->db->get()->result_array();

        return $this->attach_images_and_tags($artworks);
    }

    // ======================================================
    // GET PENDING ARTWORK (ADMIN)
    // ======================================================
    public function get_pending() {
        $this->db->select('a.*, u.username, u.avatar');
        $this->db->from('artworks a');
        $this->db->join('users u', 'u.id_user = a.id_user');
        $this->db->where('a.status', 'pending');
        $this->db->order_by('a.created_at', 'DESC');
        $artworks = $this->db->get()->result_array();

        return $this->attach_images_and_tags($artworks);
    }

    // ======================================================
    // GET DETAIL ARTWORK
    // ======================================================
    public function get_detail($id_artwork) {
        $this->db->select('a.*, u.username, u.avatar');
        $this->db->from('artworks a');
        $this->db->join('users u', 'u.id_user = a.id_user');
        $this->db->where('a.id_artwork', $id_artwork);
        $artwork = $this->db->get()->row_array();

        if (!$artwork) return null;

        // attach images
        $artwork['images'] = $this->db
            ->get_where('artwork_images', ['id_artwork' => $id_artwork])
            ->result_array();

        // attach tags
        $this->db->select('t.id_tag, t.tag_name');
        $this->db->from('artwork_tag_map m');
        $this->db->join('artwork_tags t', 't.id_tag = m.id_tag');
        $this->db->where('m.id_artwork', $id_artwork);
        $artwork['tags'] = $this->db->get()->result_array();

        return $artwork;
    }

    // ======================================================
    // UPDATE STATUS (Admin Approve/Reject)
    // ======================================================
    public function update_status($id, $status) {
        return $this->db->update(
            'artworks',
            ["status" => $status],
            ["id_artwork" => $id]
        );
    }

    // ======================================================
    // Helper: attach images and tags to artwork list
    // ======================================================
    private function attach_images_and_tags($rows)
    {
        $result = [];
        $temp = [];

        foreach ($rows as $row) {
            $id = $row['id_artwork'];

            // Jika belum pernah dimasukkan
            if (!isset($temp[$id])) {
                $temp[$id] = [
                    'id_artwork' => $row['id_artwork'],
                    'id_user' => $row['id_user'],
                    'username' => $row['username'],
                    'avatar' => $row['avatar'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'price' => $row['price'],
                    'status' => $row['status'],
                    'created_at' => $row['created_at'],
                    'images' => [],
                    'tags' => []
                ];
            }

            // -----------------------
            // ADD IMAGE (unique)
            // -----------------------
            if (!empty($row['image_id'])) {
                $imgKey = $row['image_id'];

                if (!isset($temp[$id]['images'][$imgKey])) {
                    $temp[$id]['images'][$imgKey] = [
                        'image_id' => $row['image_id'],
                        'image_url' => $row['image_url']
                    ];
                }
            }

            // -----------------------
            // ADD TAG (unique)
            // -----------------------
            if (!empty($row['id_tag'])) {
                $tagKey = $row['id_tag'];

                if (!isset($temp[$id]['tags'][$tagKey])) {
                    $temp[$id]['tags'][$tagKey] = [
                        'id_tag' => $row['id_tag'],
                        'tag_name' => $row['tag_name']
                    ];
                }
            }
        }

        // Convert associative keys → numeric index
        foreach ($temp as $item) {
            $item['images'] = array_values($item['images']);
            $item['tags'] = array_values($item['tags']);

            $result[] = $item;
        }

        return $result;
    }
}
