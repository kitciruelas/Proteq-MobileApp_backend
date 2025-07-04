<?php
class SafetyProtocols {
    private $conn;
    private $table = 'safety_protocols';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create safety protocol
    public function create($data) {
        $query = "INSERT INTO {$this->table} (title, description, type, file_attachment, created_by, created_at, updated_at) 
                  VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssssi', 
            $data['title'], 
            $data['description'], 
            $data['type'], 
            $data['file_attachment'], 
            $data['created_by']
        );
        return $stmt->execute();
    }

    // Get all safety protocols
    public function getAll() {
        $query = "SELECT sp.*, a.name as created_by_name 
                  FROM {$this->table} sp 
                  LEFT JOIN admin a ON sp.created_by = a.admin_id 
                  ORDER BY sp.created_at DESC";
        return $this->conn->query($query);
    }

    // Get safety protocol by ID
    public function getById($protocol_id) {
        $query = "SELECT sp.*, a.name as created_by_name 
                  FROM {$this->table} sp 
                  LEFT JOIN admin a ON sp.created_by = a.admin_id 
                  WHERE sp.protocol_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $protocol_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Get safety protocols by type
    public function getByType($type) {
        $query = "SELECT sp.*, a.name as created_by_name 
                  FROM {$this->table} sp 
                  LEFT JOIN admin a ON sp.created_by = a.admin_id 
                  WHERE sp.type = ? 
                  ORDER BY sp.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $type);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Update safety protocol
    public function update($protocol_id, $data) {
        $set_clauses = [];
        $types = '';
        $values = [];
        
        if (isset($data['title'])) {
            $set_clauses[] = 'title = ?';
            $types .= 's';
            $values[] = $data['title'];
        }
        
        if (isset($data['description'])) {
            $set_clauses[] = 'description = ?';
            $types .= 's';
            $values[] = $data['description'];
        }
        
        if (isset($data['type'])) {
            $set_clauses[] = 'type = ?';
            $types .= 's';
            $values[] = $data['type'];
        }
        
        if (isset($data['file_attachment'])) {
            $set_clauses[] = 'file_attachment = ?';
            $types .= 's';
            $values[] = $data['file_attachment'];
        }
        
        $set_clauses[] = 'updated_at = NOW()';
        
        if (empty($set_clauses)) {
            return false;
        }
        
        $query = "UPDATE {$this->table} SET " . implode(', ', $set_clauses) . " WHERE protocol_id = ?";
        $types .= 'i';
        $values[] = $protocol_id;
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    // Delete safety protocol
    public function delete($protocol_id) {
        // First get the file attachment to delete the file
        $protocol = $this->getById($protocol_id);
        if ($protocol && $protocol['file_attachment']) {
            $file_path = '../uploads/safety_protocols/' . $protocol['file_attachment'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        $query = "DELETE FROM {$this->table} WHERE protocol_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $protocol_id);
        return $stmt->execute();
    }

    // Search safety protocols
    public function search($search_term) {
        $search_term = '%' . $search_term . '%';
        $query = "SELECT sp.*, a.name as created_by_name 
                  FROM {$this->table} sp 
                  LEFT JOIN admin a ON sp.created_by = a.admin_id 
                  WHERE sp.title LIKE ? OR sp.description LIKE ? OR sp.type LIKE ? 
                  ORDER BY sp.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sss', $search_term, $search_term, $search_term);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Get safety protocols by creator
    public function getByCreator($created_by) {
        $query = "SELECT sp.*, a.name as created_by_name 
                  FROM {$this->table} sp 
                  LEFT JOIN admin a ON sp.created_by = a.admin_id 
                  WHERE sp.created_by = ? 
                  ORDER BY sp.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $created_by);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Get recent safety protocols (last 10)
    public function getRecent($limit = 10) {
        $query = "SELECT sp.*, a.name as created_by_name 
                  FROM {$this->table} sp 
                  LEFT JOIN admin a ON sp.created_by = a.admin_id 
                  ORDER BY sp.created_at DESC 
                  LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
} 