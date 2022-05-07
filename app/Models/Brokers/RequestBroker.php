<?php

namespace Models\Brokers;

class RequestBroker extends Broker
{
    public function insert($description, $clientName, $clientEmail)
    {
        $sql = "INSERT INTO request(description, client_name, client_email) VALUES (?, ?, ?)";
        $this->query($sql, [
            $description,
            $clientName,
            $clientEmail
        ]);
    }

    public function getLatestId(): array
    {
        $sql = "SELECT id FROM request ORDER BY id DESC LIMIT 1";
        return $this->select($sql);
    }

    public function updateStatus(bool $status, int $id)
    {
        $sql = "UPDATE request SET send_status = ? WHERE id = ?";
        $this->query($sql, [
            $status,
            $id
        ]);
    }
}