<?php

namespace AIConnection;

class AIConnectionHandler
{
    private string $url = 'http://aiserver:5000/';

    // Receive response from AI server as an array
    public function getResults($moveNumber, $hand, $board)
    {
        $content = [
            'move_number' => $moveNumber,
            'hand' => $hand,
            'board' => $board
        ];

        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($content),
            ],
        ];

        return json_decode(
            file_get_contents(
                $this->url,
                false,
                stream_context_create($options)
            )
        );
    }
}