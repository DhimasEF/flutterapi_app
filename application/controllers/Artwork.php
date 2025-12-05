<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Artwork extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('M_Artwork');
    }

    public function all() {
        $data = $this->M_Artwork->get_all();
        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
    }

    public function all_admin() {
        $data = $this->M_Artwork->get_all_admin();
        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
    }

    public function draft() {
        $data = $this->M_Artwork->get_draft();
        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
    }

    // -------------------------
    // 1. Creator Upload Artwork
    // -------------------------
    public function upload()
    {
        // MATIKAN OUTPUT HTML
        @ob_end_clean();
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        // Ambil input
        $id_user     = $this->input->post('id_user');
        $title       = $this->input->post('title');
        $description = $this->input->post('description');
        $price       = $this->input->post('price');
        $tags        = json_decode($this->input->post('tags'), true);

        if (!$id_user || !$title || !$description || !$price) {
            echo json_encode(["status" => false, "message" => "Missing required fields"]);
            exit;
        }

        // Path penyimpanan
        $originalPath = "./uploads/artworks/original/";
        $previewPath  = "./uploads/artworks/preview/";

        if (!is_dir($originalPath)) mkdir($originalPath, 0777, true);
        if (!is_dir($previewPath)) mkdir($previewPath, 0777, true);

        $uploadedFiles = [];

        // --- UPLOAD GAMBAR ---
        if (!empty($_FILES['images'])) {

            $files = $_FILES['images'];

            // SINGLE UPLOAD
            if (!is_array($files['name'])) {
                $_FILES['temp'] = $files;
                $fileName = $this->processSingleUpload('temp', $originalPath, $previewPath);
                if ($fileName) $uploadedFiles[] = $fileName;

            } else { 

                // MULTIPLE UPLOAD
                $count = count($files['name']);

                for ($i = 0; $i < $count; $i++) {

                    $_FILES['temp'] = [
                        'name'     => $files['name'][$i],
                        'type'     => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error'    => $files['error'][$i],
                        'size'     => $files['size'][$i]
                    ];

                    $fileName = $this->processSingleUpload('temp', $originalPath, $previewPath);
                    if ($fileName) $uploadedFiles[] = $fileName;
                }
            }
        }

        if (empty($uploadedFiles)) {
            echo json_encode([
                "status" => false,
                "message" => "Image upload failed",
            ]);
            exit;
        }

        // INSERT ARTWORK
        $dataArtwork = [
            "id_user"     => $id_user,
            "title"       => $title,
            "description" => $description,
            "price"       => $price,
            "status"      => 1,
            "created_at"  => date("Y-m-d H:i:s")
        ];

        $this->db->insert("artworks", $dataArtwork);
        $id_artwork = $this->db->insert_id();

        // INSERT IMAGES (dengan preview)
        foreach ($uploadedFiles as $file) {
            $this->db->insert("artwork_images", [
                "id_artwork"  => $id_artwork,
                "image_url"   => $file["original"],
                "preview_url" => $file["preview"]
            ]);
        }

        // INSERT TAGS
        if (is_array($tags)) {
            foreach ($tags as $tagName) {

                $tag = $this->db->get_where("artwork_tags", ["tag_name" => $tagName])->row();

                if (!$tag) {
                    $this->db->insert("artwork_tags", ["tag_name" => $tagName]);
                    $id_tag = $this->db->insert_id();
                } else {
                    $id_tag = $tag->id_tag;
                }

                $this->db->insert("artwork_tag_map", [
                    "id_artwork" => $id_artwork,
                    "id_tag"     => $id_tag
                ]);
            }
        }

        echo json_encode([
            "status"     => true,
            "message"    => "Artwork uploaded successfully",
            "id_artwork" => $id_artwork,
            "images"     => $uploadedFiles
        ]);
        exit;
    }

    private function processSingleUpload($fileInput, $originalPath, $previewPath)
    {
        $config = [
            'upload_path'   => $originalPath,
            'allowed_types' => 'jpg|jpeg|png|webp',
            'encrypt_name'  => TRUE
        ];

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload($fileInput)) {
            return false;
        }

        $data = $this->upload->data();
        $originalFile = $data['file_name'];

        // PREVIEW GENERATION
        $previewName = pathinfo($originalFile, PATHINFO_FILENAME) . "_preview.jpg";
        $previewFile = $previewPath . $previewName;

        $this->generatePreview($originalPath . $originalFile, $previewFile);

        return [
            "original" => $originalFile,
            "preview"  => $previewName
        ];
    }

    private function generatePreview($source, $destination)
    {
        list($width, $height) = getimagesize($source);

        $newWidth = 350; // ukuran preview
        $newHeight = intval(($newWidth / $width) * $height);

        $src = imagecreatefromstring(file_get_contents($source));
        $tmp = imagecreatetruecolor($newWidth, $newHeight);

        imagecopyresampled($tmp, $src, 0, 0, 0, 0,
            $newWidth, $newHeight, $width, $height
        );

        imagejpeg($tmp, $destination, 70); // kualitas 70% → kecil tapi jelas

        imagedestroy($tmp);
        imagedestroy($src);
    }

    public function my($id_user) {
        $data = $this->M_Artwork->get_by_user($id_user);
        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
    }

    // -------------------------
    // 2. Admin: List Pending
    // -------------------------
    public function pending() {
        $data = $this->M_Artwork->get_pending();
        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
    }


    // -------------------------
    // 3. Admin: Detail
    // -------------------------
    public function detail($id) {
        $data = $this->M_Artwork->get_detail($id);

        echo json_encode([
            "success" => $data ? true : false,
            "data" => $data
        ]);
    }

    public function updateStatus() {
        $id = $this->input->post('id_artwork');
        $status = $this->input->post('status');

        if (!$id || !$status) {
            echo json_encode([
                "status" => false, 
                "message" => "ID atau status tidak boleh kosong"
            ]);
            return;
        }

        // Validasi agar tidak sembarang status
        $allowed = ["approved", "rejected", "published", "draft"];
        if (!in_array($status, $allowed)) {
            echo json_encode([
                "status" => false,
                "message" => "Status tidak valid"
            ]);
            return;
        }

        $this->M_Artwork->update_status($id, $status);

        echo json_encode([
            "status" => true,
            "message" => "Status updated to $status"
        ]);
    }
}
