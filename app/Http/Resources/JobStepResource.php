<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobStepResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_id' => $this->job_id,
            'step_order' => $this->step_order,
            'name' => $this->name,
            'status' => $this->status,
            'input' => $this->input,
            'output' => $this->output,
            'error_message' => $this->error_message,
            'retry_count' => $this->retry_count,
            'started_at' => $this->started_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
        ];
    }
}