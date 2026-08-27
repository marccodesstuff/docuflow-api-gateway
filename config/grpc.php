<?php

return [
    /*
    |--------------------------------------------------------------------------
    | gRPC Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to DocuFlow gRPC services.
    |
    */

    'core_host' => env('GRPC_CORE_HOST', 'localhost'),
    'core_port' => env('GRPC_CORE_PORT', 9090),
    'ml_host' => env('GRPC_ML_HOST', 'localhost'),
    'ml_port' => env('GRPC_ML_PORT', 50051),
    'integrations_host' => env('GRPC_INTEGRATIONS_HOST', 'localhost'),
    'integrations_port' => env('GRPC_INTEGRATIONS_PORT', 9091),
];