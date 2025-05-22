<?php

namespace App\Message;

class NotificationMessage
{
    private string $userId;
    private string $type;
    private string $title;
    private string $message;
    private ?array $data;

    public function __construct(
        string $userId,
        string $type,
        string $title,
        string $message,
        ?array $data = null
    ) {
        $this->userId = $userId;
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getData(): ?array
    {
        return $this->data;
    }
}
