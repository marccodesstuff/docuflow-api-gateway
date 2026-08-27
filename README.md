# DocuFlow API Gateway

Laravel 11 + Filament 3 + Reverb + Sanctum — The BFF/API Gateway for the DocuFlow platform. Provides REST API, admin panel, WebSocket real-time updates, and gRPC client connectivity to backend services.

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    DocuFlow API Gateway                     │
│  ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌─────────┐  │
│  │  REST API  │ │  Admin UI  │ │  WebSocket │ │  gRPC   │  │
│  │ (Sanctum)  │ │ (Filament) │ │  (Reverb)  │ │ Clients │  │
│  └─────┬──────┘ └─────┬──────┘ └─────┬──────┘ └────┬────┘  │
│        │             │             │             │        │
│        ▼             ▼             ▼             ▼        │
│  ┌────────────────────────────────────────────────────────┐ │
│  │              Laravel Application Core                   │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
         │                    │                    │
         ▼                    ▼                    ▼
   ┌──────────┐         ┌──────────┐         ┌──────────┐
   │PostgreSQL│         │  Redis   │         │  MinIO   │
   └──────────┘         └──────────┘         └──────────┘
         │                    │                    │
         ▼                    ▼                    ▼
   ┌──────────┐         ┌──────────┐         ┌──────────┐
   │ docuflow │         │ docuflow │         │ docuflow │
   │  -core   │         │   -ml    │         │   -ml    │
   │  (gRPC)  │         │  (gRPC)  │         │  (gRPC)  │
   └──────────┘         └──────────┘         └──────────┘
```

## Features

### REST API (Sanctum)
- **Documents** - Upload, list, download, process, delete
- **Document Types** - CRUD with field definitions, validation, routing
- **Processing Jobs** - Track pipeline execution, retry, cancel
- **Webhooks** - Register, test, view delivery history
- **Tenants** - Multi-tenant management

### Admin Panel (Filament 3)
- **Documents** - Table with status badges, bulk actions, relation managers
- **Document Types** - Visual field builder, validation rules, routing config
- **Processing Jobs** - Step-by-step execution view
- **Extraction Results** - Field/table viewer with confidence scores
- **Webhooks** - Delivery log with retry status
- **Tenants/Users** - RBAC with Spatie permissions

### Real-time (Reverb)
- Document processing status updates
- WebSocket notifications for job completion
- Live extraction result streaming

### gRPC Clients
- **docuflow-core** (port 9090) - Document processing orchestration
- **docuflow-ml** (port 50051) - ML inference (classify, extract, detect)
- **docuflow-integrations** (port 9091) - ERP/EDI/webhook triggers

## Tech Stack

- **PHP 8.2**, Laravel 11
- **Filament 3** - Admin panel
- **Laravel Reverb** - WebSocket server
- **Laravel Sanctum** - API authentication
- **Spiral RoadRunner** - gRPC worker support
- **PostgreSQL**, Redis, MinIO/S3
- **Pest** - Testing

## Quick Start

### Prerequisites

- PHP 8.2+ with extensions: pdo_pgsql, mbstring, zip, bcmath, pcntl, redis, grpc, protobuf
- Composer 2+
- Node.js 18+ (for Vite)
- Docker Compose (for dependencies)

### Local Development

```bash
# Start dependencies
cd ../docuflow-infra/docker
docker compose up -d postgres redis minio

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Generate gRPC classes
chmod +x scripts/generate_grpc.sh
./scripts/generate_grpc.sh

# Start all services (concurrently)
npm run dev
# Or individually:
# php artisan serve
# php artisan reverb:start
# php artisan queue:listen
# php artisan roadrunner:start
```

### Build for Production

```bash
# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build assets
npm run build

# Docker
docker build -t docuflow-api:latest .
docker build -f Dockerfile.reverb -t docuflow-reverb:latest .
```

## Configuration

Key environment variables (`.env`):

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_URL` | `http://localhost` | Application URL |
| `DB_CONNECTION` | `pgsql` | Database driver |
| `DB_HOST` | `127.0.0.1` | Database host |
| `REDIS_HOST` | `127.0.0.1` | Redis host |
| `QUEUE_CONNECTION` | `redis` | Queue driver |
| `BROADCAST_DRIVER` | `reverb` | Broadcasting driver |
| `REVERB_APP_ID` | `docuflow` | Reverb app ID |
| `REVERB_APP_KEY` | `docuflow-key` | Reverb app key |
| `REVERB_APP_SECRET` | `docuflow-secret` | Reverb secret |
| `REVERB_HOST` | `0.0.0.0` | Reverb host |
| `REVERB_PORT` | `8080` | Reverb port |
| `GRPC_CORE_HOST` | `localhost` | Core service host |
| `GRPC_CORE_PORT` | `9090` | Core service port |
| `GRPC_ML_HOST` | `localhost` | ML service host |
| `GRPC_ML_PORT` | `50051` | ML service port |
| `AWS_ENDPOINT` | `http://localhost:9000` | MinIO endpoint |
| `AWS_BUCKET` | `docuflow` | S3 bucket |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `true` | MinIO path style |

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/documents` | List documents (paginated, filterable) |
| `POST` | `/api/documents` | Upload document |
| `GET` | `/api/documents/{id}` | Get document with relations |
| `POST` | `/api/documents/{id}/process` | Start processing pipeline |
| `GET` | `/api/documents/{id}/download` | Download original file |
| `DELETE` | `/api/documents/{id}` | Delete document |
| `GET` | `/api/document-types` | List document types |
| `POST` | `/api/document-types` | Create document type |
| `GET` | `/api/processing-jobs` | List jobs |
| `POST` | `/api/processing-jobs/{id}/retry` | Retry failed job |
| `POST` | `/api/processing-jobs/{id}/cancel` | Cancel running job |
| `GET` | `/api/webhooks` | List webhooks |
| `POST` | `/api/webhooks` | Register webhook |
| `POST` | `/api/webhooks/{id}/test` | Test webhook delivery |

## Admin Panel

Access at `/admin` (default: `http://localhost:8000/admin`)

Resources:
- **Documents** - Full CRUD with processing actions
- **Document Types** - Visual schema builder
- **Processing Jobs** - Execution timeline
- **Extraction Results** - Field/table inspector
- **Webhooks** - Delivery history
- **Tenants** - Multi-tenant management
- **Users** - RBAC with roles/permissions

## WebSocket Events

Subscribe to channels via Reverb:

```javascript
import Echo from 'laravel-echo';

const echo = new Echo({
    broadcaster: 'reverb',
    key: 'docuflow-key',
    wsHost: 'localhost',
    wsPort: 8080,
    forceTLS: false,
});

// Document processing updates
echo.private(`documents.${documentId}`)
    .listen('DocumentProcessingUpdated', (e) => {
        console.log(e.status, e.progress);
    });

// Job completion
echo.private(`tenant.${tenantId}`)
    .listen('ProcessingJobCompleted', (e) => {
        console.log('Job done:', e.jobId);
    });
```

## Testing

```bash
# Run tests with Pest
./vendor/bin/pest

# With coverage
./vendor/bin/pest --coverage

# Static analysis
./vendor/bin/pint --test
```

## Docker

```bash
# API
docker build -t docuflow-api:latest .

# Reverb WebSocket server
docker build -f Dockerfile.reverb -t docuflow-reverb:latest .

# Run with docker-compose (see docuflow-infra)
```

## Monitoring

- **Health**: `GET /health`
- **Metrics**: Prometheus exporter (if enabled)
- **Horizon**: Queue dashboard at `/horizon` (if installed)
- **Telescope**: Debug assistant at `/telescope` (if installed)

## License

MIT