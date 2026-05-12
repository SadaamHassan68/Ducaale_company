<?php
class Flight {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function searchFlights($origin, $destination, $date, $minPrice = null, $maxPrice = null, $seatClass = null) {
        $sql = "
            SELECT DISTINCT f.* 
            FROM flights f
            LEFT JOIN seats s ON f.id = s.flight_id
            WHERE f.origin = :origin 
              AND f.destination = :destination 
              AND DATE(f.departure_time) = :date
              AND f.departure_time > NOW()
        ";
        
        $params = ['origin' => $origin, 'destination' => $destination, 'date' => $date];

        if ($minPrice !== null && $minPrice !== '') {
            $sql .= " AND f.base_price >= :minPrice";
            $params['minPrice'] = $minPrice;
        }

        if ($maxPrice !== null && $maxPrice !== '') {
            $sql .= " AND f.base_price <= :maxPrice";
            $params['maxPrice'] = $maxPrice;
        }

        if ($seatClass !== null && $seatClass !== '') {
            $sql .= " AND s.seat_class = :seatClass AND s.status = 'Available'";
            $params['seatClass'] = $seatClass;
        }

        $sql .= " ORDER BY f.departure_time ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getUpcomingFlights($limit = 6, $includePast = false) {
        $where = $includePast ? "1=1" : "departure_time > NOW()";
        $stmt = $this->pdo->prepare("
            SELECT * FROM flights 
            WHERE $where
            ORDER BY departure_time DESC 
            LIMIT :limit
        ");
        // Bind limit as integer
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function checkAvailability($flight_id, $seat_id) {
        $stmt = $this->pdo->prepare("SELECT status FROM seats WHERE id = :seat_id AND flight_id = :flight_id");
        $stmt->execute(['seat_id' => $seat_id, 'flight_id' => $flight_id]);
        $seat = $stmt->fetch();
        return $seat && $seat['status'] === 'Available';
    }
}
?>
