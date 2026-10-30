<?php
/**
 * Event Manager Class
 * Handles all event-related operations for cc_events table
 */

class EventManager {
    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    /**
     * Get event_type enum values from database
     */
    public function getEventTypeEnums() {
        try {
            $query = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA = 'sms' AND TABLE_NAME = 'cc_events' 
                      AND COLUMN_NAME = 'event_type'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                // Fallback if INFORMATION_SCHEMA is not available
               return [
'Academic',
'Meeting',
'Seminar',
'Institutional Event',
'Cultural Event',
'Sports Event',
'Orientation',
'Other'
];
            }
            
            $columnType = $result['COLUMN_TYPE'];
            
            // Extract enum values from type string like "enum('val1','val2')"
            if (strpos($columnType, 'enum') === 0) {
                preg_match_all("/'([^']*)'/", $columnType, $matches);
                return $matches[1];
            }
            
            return ['Academic', 'Meeting', 'Seminar', 'Institutional Event', 'Cultural Event', 'Sports Event', 'Orientation', 'Other'];
        } catch (Exception $e) {
            // Fallback to default values
            return ['Academic', 'Meeting', 'Seminar', 'Institutional Event', 'Cultural Event', 'Sports Event', 'Orientation', 'Other'];
        }
    }

    /**
     * Get status enum values from database
     */
    public function getStatusEnums() {
        try {
            $query = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA = 'sms' AND TABLE_NAME = 'cc_events' 
                      AND COLUMN_NAME = 'status'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                // Fallback if INFORMATION_SCHEMA is not available
                return ['upcoming', 'ongoing', 'completed', 'cancelled'];
            }
            
            $columnType = $result['COLUMN_TYPE'];
            
            // Extract enum values from type string like "enum('val1','val2')"
            if (strpos($columnType, 'enum') === 0) {
                preg_match_all("/'([^']*)'/", $columnType, $matches);
                return $matches[1];
            }
            
            return ['upcoming', 'ongoing', 'completed', 'cancelled'];
        } catch (Exception $e) {
            // Fallback to default values
            return ['upcoming', 'ongoing', 'completed', 'cancelled'];
        }
    }

    /**
     * Get all events
     */
    public function getAllEvents() {
        try {
            // include optional columns if they exist
            $extra = '';
            if ($this->hasColumn('cc_events', 'template_id')) $extra .= ', template_id';
            if ($this->hasColumn('cc_events', 'priority')) $extra .= ', priority';

            $query = "SELECT 
                        event_id,
                        event_title,
                        event_type,
                        event_date,
                        start_time,
                        end_time,
                        description,
                        location,
                        target_audience,
                        status" . $extra . ",
                        created_at
                    FROM cc_events
                    ORDER BY event_date DESC, start_time DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error fetching events: " . $e->getMessage());
        }
    }

    /**
     * Get event by ID
     */
    public function getEventById($event_id) {
        try {
            $query = "SELECT * FROM cc_events WHERE event_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $event_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error fetching event: " . $e->getMessage());
        }
    }

    /**
     * Check whether a table column exists
     */
    private function hasColumn($table, $column) {
        try {
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM {$table} LIKE :column");
            $stmt->bindParam(':column', $column);
            $stmt->execute();
            return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Return list of event templates (if table exists)
     */
    public function getEventTemplates() {
        try {
            $stmt = $this->conn->prepare("SHOW TABLES LIKE 'cc_event_templates'");
            $stmt->execute();
            $exists = (bool) $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$exists) return [];

            $q = "SELECT * FROM cc_event_templates ORDER BY template_name ASC";
            $s = $this->conn->prepare($q);
            $s->execute();
            return $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Create new event
     */
    public function createEvent($data) {
        try {
            // Validate required fields
            $required = ['event_title', 'event_type', 'event_date', 'start_time', 'end_time', 'location', 'status'];
            
            foreach ($required as $field) {
                if (!isset($data[$field]) || trim($data[$field]) === '') {
                    throw new Exception("Missing required field: $field");
                }
            }

            // Prepare data
            $event_title = trim($data['event_title']);
            $event_type = trim($data['event_type']);
            $event_date = trim($data['event_date']);
            $start_time = trim($data['start_time']);
            $end_time = trim($data['end_time']);
            $description = isset($data['description']) ? trim($data['description']) : '';
            $location = trim($data['location']);
            $target_audience = isset($data['target_audience']) ? trim($data['target_audience']) : '';
            $status = trim($data['status']);
            // optional fields
            $priority = isset($data['priority']) ? trim($data['priority']) : null;
            $template_id = isset($data['template_id']) ? $data['template_id'] : null;
            if ($template_id === '') $template_id = null;
            if ($priority === '') $priority = null;

            // Insert event, include optional template_id and priority if columns exist
            $columns = ['event_title','event_type','event_date','start_time','end_time','description','location','target_audience','status'];
            $placeholders = [':event_title',':event_type',':event_date',':start_time',':end_time',':description',':location',':target_audience',':status'];

            if ($this->hasColumn('cc_events','template_id')) {
                $columns[] = 'template_id';
                $placeholders[] = ':template_id';
            }
            if ($this->hasColumn('cc_events','priority')) {
                $columns[] = 'priority';
                $placeholders[] = ':priority';
            }

            $query = "INSERT INTO cc_events (" . implode(',', $columns) . ", created_at) VALUES (" . implode(',', $placeholders) . ", NOW())";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':event_title', $event_title);
            $stmt->bindParam(':event_type', $event_type);
            $stmt->bindParam(':event_date', $event_date);
            $stmt->bindParam(':start_time', $start_time);
            $stmt->bindParam(':end_time', $end_time);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':target_audience', $target_audience);
            $stmt->bindParam(':status', $status);

            if ($this->hasColumn('cc_events','template_id')) {
                $stmt->bindValue(':template_id', $template_id === null ? null : $template_id, PDO::PARAM_INT);
            }
            if ($this->hasColumn('cc_events','priority')) {
                $stmt->bindValue(':priority', $priority);
            }

            $stmt->execute();
            
            return [
                'success' => true,
                'event_id' => $this->conn->lastInsertId(),
                'message' => 'Event created successfully'
            ];
        } catch (Exception $e) {
            throw new Exception("Error creating event: " . $e->getMessage());
        }
    }

    /**
     * Update event
     */
    public function updateEvent($event_id, $data) {
        try {
            if (!isset($event_id) || !intval($event_id)) {
                throw new Exception("Invalid event_id");
            }

            // Prepare data
            $event_title = isset($data['event_title']) ? trim($data['event_title']) : null;
            $event_type = isset($data['event_type']) ? trim($data['event_type']) : null;
            $event_date = isset($data['event_date']) ? trim($data['event_date']) : null;
            $start_time = isset($data['start_time']) ? trim($data['start_time']) : null;
            $end_time = isset($data['end_time']) ? trim($data['end_time']) : null;
            $description = isset($data['description']) ? trim($data['description']) : null;
            $location = isset($data['location']) ? trim($data['location']) : null;
            $target_audience = isset($data['target_audience']) ? trim($data['target_audience']) : null;
            $status = isset($data['status']) ? trim($data['status']) : null;
            $priority = array_key_exists('priority', $data) ? (trim($data['priority']) === '' ? null : trim($data['priority'])) : null;
            $template_id = array_key_exists('template_id', $data) ? ($data['template_id'] === '' ? null : $data['template_id']) : null;

            // Build update query
            $updates = [];
            $params = [':event_id' => $event_id];
            
            if ($event_title !== null) {
                $updates[] = "event_title = :event_title";
                $params[':event_title'] = $event_title;
            }
            if ($event_type !== null) {
                $updates[] = "event_type = :event_type";
                $params[':event_type'] = $event_type;
            }
            if ($event_date !== null) {
                $updates[] = "event_date = :event_date";
                $params[':event_date'] = $event_date;
            }
            if ($start_time !== null) {
                $updates[] = "start_time = :start_time";
                $params[':start_time'] = $start_time;
            }
            if ($end_time !== null) {
                $updates[] = "end_time = :end_time";
                $params[':end_time'] = $end_time;
            }
            if ($description !== null) {
                $updates[] = "description = :description";
                $params[':description'] = $description;
            }
            if ($location !== null) {
                $updates[] = "location = :location";
                $params[':location'] = $location;
            }
            if ($target_audience !== null) {
                $updates[] = "target_audience = :target_audience";
                $params[':target_audience'] = $target_audience;
            }
            if ($status !== null) {
                $updates[] = "status = :status";
                $params[':status'] = $status;
            }

            // include optional columns if present in DB and in payload
            if ($this->hasColumn('cc_events','template_id') && array_key_exists('template_id', $data)) {
                $updates[] = "template_id = :template_id";
                $params[':template_id'] = $template_id;
            }
            if ($this->hasColumn('cc_events','priority') && array_key_exists('priority', $data)) {
                $updates[] = "priority = :priority";
                $params[':priority'] = $priority;
            }

            if (empty($updates)) {
                throw new Exception("No fields to update");
            }

            $query = "UPDATE cc_events SET " . implode(", ", $updates) . " WHERE event_id = :event_id";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Event updated successfully'
            ];
        } catch (Exception $e) {
            throw new Exception("Error updating event: " . $e->getMessage());
        }
    }

    /**
     * Delete event
     */
    public function deleteEvent($event_id) {
        try {
            if (!isset($event_id) || !intval($event_id)) {
                throw new Exception("Invalid event_id");
            }

            $query = "DELETE FROM cc_events WHERE event_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $event_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Event deleted successfully'
            ];
        } catch (Exception $e) {
            throw new Exception("Error deleting event: " . $e->getMessage());
        }
    }

    /**
     * Get event statistics
     */
    public function getEventStats() {
        try {
            $total = $this->conn->query("SELECT COUNT(*) as count FROM cc_events")->fetch(PDO::FETCH_ASSOC)['count'];
            $upcoming = $this->conn->query("SELECT COUNT(*) as count FROM cc_events WHERE event_date > CURDATE() AND status != 'Completed'")->fetch(PDO::FETCH_ASSOC)['count'];
            $ongoing = $this->conn->query("SELECT COUNT(*) as count FROM cc_events WHERE status = 'Ongoing'")->fetch(PDO::FETCH_ASSOC)['count'];
            $completed = $this->conn->query("SELECT COUNT(*) as count FROM cc_events WHERE status = 'Completed'")->fetch(PDO::FETCH_ASSOC)['count'];
            
            return [
                'total' => $total,
                'upcoming' => $upcoming,
                'ongoing' => $ongoing,
                'completed' => $completed
            ];
        } catch (Exception $e) {
            throw new Exception("Error fetching statistics: " . $e->getMessage());
        }
    }
}
?>
