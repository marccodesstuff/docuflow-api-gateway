<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spiral\RoadRunner\Worker;
use Spiral\Goridge\StreamRelay;
use Spiral\RoadRunner\GRPC\Dispatcher;
use App\Services\GrpcClientManager;
use Docuflow\V1\DocumentServiceInterface;
use Docuflow\V1\ProcessingJobServiceInterface;
use Docuflow\V1\ExtractionServiceInterface;
use Docuflow\V1\WebhookServiceInterface;

class RoadRunnerWorkerCommand extends Command
{
    protected $signature = 'roadrunner:worker';
    protected $description = 'Start RoadRunner gRPC worker';

    public function handle(): int
    {
        $worker = new Worker(new StreamRelay(STDIN, STDOUT));
        
        $dispatcher = new Dispatcher();
        
        // Register gRPC services
        // These would be implemented as proper gRPC service handlers
        // For now, we'll register the client manager for outbound calls
        
        $this->info('RoadRunner gRPC worker started');
        
        while (true) {
            $payload = $worker->waitPayload();
            if ($payload === null) {
                break;
            }
            
            try {
                $response = $dispatcher->dispatch($payload->body);
                $worker->respond($response);
            } catch (\Throwable $e) {
                $this->error('Worker error: ' . $e->getMessage());
                $worker->error((string)$e);
            }
        }
        
        return 0;
    }
}