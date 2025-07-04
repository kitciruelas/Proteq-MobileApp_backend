<?php
class EvacuationCenters {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get all open evacuation centers
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM evacuation_centers WHERE status = 'open'");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get a single open evacuation center by ID
    public function getById($center_id) {
        $stmt = $this->db->prepare("SELECT * FROM evacuation_centers WHERE center_id = ? AND status = 'open'");
        $stmt->bind_param("i", $center_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
} 