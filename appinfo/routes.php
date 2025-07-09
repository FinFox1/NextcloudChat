<?php
declare(strict_types=1);

return [
    'routes' => [
        // Page routes
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        
        // API routes
        ['name' => 'api#config', 'url' => '/api/config', 'verb' => 'GET'],
        ['name' => 'matrix#authenticate', 'url' => '/api/authenticate', 'verb' => 'POST'],
        ['name' => 'matrix#getConfig', 'url' => '/api/matrix/config', 'verb' => 'GET'],
        ['name' => 'element#getConfig', 'url' => '/api/element/config', 'verb' => 'GET'],
        ['name' => 'element#updateConfig', 'url' => '/api/element/config', 'verb' => 'PUT'],
        
        // Room management
        ['name' => 'api#getRooms', 'url' => '/api/rooms', 'verb' => 'GET'],
        ['name' => 'api#createRoom', 'url' => '/api/rooms', 'verb' => 'POST'],
        ['name' => 'api#joinRoom', 'url' => '/api/rooms/{roomId}/join', 'verb' => 'POST'],
        ['name' => 'api#leaveRoom', 'url' => '/api/rooms/{roomId}/leave', 'verb' => 'POST'],
        
        // Call management
        ['name' => 'api#startCall', 'url' => '/api/rooms/{roomId}/call', 'verb' => 'POST'],
        ['name' => 'api#endCall', 'url' => '/api/rooms/{roomId}/call', 'verb' => 'DELETE'],
        
        // User management
        ['name' => 'api#getUsers', 'url' => '/api/users', 'verb' => 'GET'],
        ['name' => 'api#inviteUser', 'url' => '/api/rooms/{roomId}/invite', 'verb' => 'POST'],
    ]
];
