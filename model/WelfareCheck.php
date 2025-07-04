<?php
class WelfareCheck {
    private $conn;
    private $table = 'welfare_checks';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create welfare check
    public function create($data) {
        $query = "INSERT INTO {$this->table} (user_id, emergency_id, status, remarks) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iiss', $data['user_id'], $data['emergency_id'], $data['status'], $data['remarks']);
        return $stmt->execute();
    }

    // Get all welfare checks
    public function getAll() {
        $query = "SELECT * FROM {$this->table}";
        return $this->conn->query($query);
    }

    // Get welfare checks by user
    public function getByUser($user_id) {
        $query = "SELECT * FROM {$this->table} WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Get welfare checks by emergency
    public function getByEmergency($emergency_id) {
        $query = "SELECT * FROM {$this->table} WHERE emergency_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $emergency_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Update welfare check status/remarks
    public function update($welfare_id, $data) {
        $query = "UPDATE {$this->table} SET status = ?, remarks = ? WHERE welfare_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssi', $data['status'], $data['remarks'], $welfare_id);
        return $stmt->execute();
    }

    // Delete welfare check
    public function delete($welfare_id) {
        $query = "DELETE FROM {$this->table} WHERE welfare_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $welfare_id);
        return $stmt->execute();
    }
} 