<?php
class Emergencies {
    private $conn;
    private $table = 'emergencies';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create emergency
    public function create($data) {
        $query = "INSERT INTO {$this->table} (emergency_type, description, triggered_by, triggered_at, is_active) VALUES (?, ?, ?, NOW(), 1)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssi', $data['emergency_type'], $data['description'], $data['triggered_by']);
        return $stmt->execute();
    }

    // Get all emergencies
    public function getAll() {
        $query = "SELECT * FROM {$this->table}";
        return $this->conn->query($query);
    }

    // Get emergencies by status
    public function getByStatus($is_active) {
        $query = "SELECT * FROM {$this->table} WHERE is_active = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $is_active);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Update emergency
    public function update($emergency_id, $data) {
        $query = "UPDATE {$this->table} SET emergency_type = ?, description = ? WHERE emergency_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssi', $data['emergency_type'], $data['description'], $emergency_id);
        return $stmt->execute();
    }

    // Resolve emergency
    public function resolve($emergency_id, $resolution_reason, $resolved_by) {
        $query = "UPDATE {$this->table} SET is_active = 0, resolution_reason = ?, resolved_by = ?, resolved_at = NOW() WHERE emergency_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sii', $resolution_reason, $resolved_by, $emergency_id);
        return $stmt->execute();
    }
} 