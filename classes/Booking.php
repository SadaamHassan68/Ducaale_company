<?php
class Booking {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createBooking($user_id, $flight_id, $seat_id, $base_price, $name = null, $phone = null, $email = null) {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT status FROM seats WHERE id = :seat_id AND flight_id = :flight_id FOR UPDATE");
            $stmt->execute(['seat_id' => $seat_id, 'flight_id' => $flight_id]);
            $seat = $stmt->fetch();

            if (!$seat || $seat['status'] !== 'Available') {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Seat is no longer available.'];
            }

            $final_price = $base_price; 

            // Reserve the seat temporarily
            $updateSeat = $this->pdo->prepare("UPDATE seats SET status = 'Reserved' WHERE id = :seat_id");
            $updateSeat->execute(['seat_id' => $seat_id]);

            $booking_ref = 'DUC-' . strtoupper(substr(uniqid(), -6));
            $insertBooking = $this->pdo->prepare("
                INSERT INTO bookings (user_id, flight_id, seat_id, booking_reference, final_price, status, passenger_name, passenger_phone, passenger_email)
                VALUES (:user_id, :flight_id, :seat_id, :booking_ref, :final_price, 'Pending', :p_name, :p_phone, :p_email)
            ");
            $insertBooking->execute([
                'user_id' => $user_id,
                'flight_id' => $flight_id,
                'seat_id' => $seat_id,
                'booking_ref' => $booking_ref,
                'final_price' => $final_price,
                'p_name' => $name,
                'p_phone' => $phone,
                'p_email' => $email
            ]);

            $this->pdo->commit();
            return ['success' => true, 'booking_reference' => $booking_ref];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Booking failed: ' . $e->getMessage()];
        }
    }

    public function confirmPayment($booking_ref) {
        try {
            $this->pdo->beginTransaction();

            // Find the pending booking
            $stmt = $this->pdo->prepare("SELECT id, seat_id, status FROM bookings WHERE booking_reference = ? FOR UPDATE");
            $stmt->execute([$booking_ref]);
            $booking = $stmt->fetch();

            if (!$booking) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Booking not found.'];
            }

            if ($booking['status'] !== 'Pending') {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Booking is already ' . $booking['status'] . '.'];
            }

            // Confirm the booking
            $stmt = $this->pdo->prepare("UPDATE bookings SET status = 'Confirmed' WHERE id = ?");
            $stmt->execute([$booking['id']]);

            // Finalize seat booking
            $stmt = $this->pdo->prepare("UPDATE seats SET status = 'Booked' WHERE id = ?");
            $stmt->execute([$booking['seat_id']]);

            $this->pdo->commit();
            return ['success' => true, 'message' => 'Payment verified. Booking confirmed.'];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Confirmation failed: ' . $e->getMessage()];
        }
    }
}
?>
