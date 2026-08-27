#!/usr/bin/env bash
# Generate gRPC PHP code from proto files for Laravel

set -e

PROTO_DIR="$(dirname "$0")/../proto"
OUT_DIR="$(dirname "$0")/../app/Grpc/Generated"

echo "Generating gRPC PHP code from proto files..."
echo "Proto dir: $PROTO_DIR"
echo "Output dir: $OUT_DIR"

mkdir -p "$OUT_DIR"

# Check if protoc-gen-grpc is available
if ! command -v protoc-gen-grpc &> /dev/null; then
    echo "Installing protoc-gen-grpc..."
    composer require --dev spiral/roadrunner-cli
    # Or install via pecl: pecl install grpc
fi

# Generate PHP gRPC code
protoc \
    -I"$PROTO_DIR" \
    --php_out="$OUT_DIR" \
    --grpc_out="$OUT_DIR" \
    --plugin=protoc-gen-grpc="$(which protoc-gen-grpc)" \
    "$PROTO_DIR"/docuflow/v1/document.proto

# Fix namespace imports in generated files
echo "Fixing namespace imports..."
find "$OUT_DIR" -name "*.php" -exec sed -i 's/^namespace DocuFlow/namespace App\\Grpc\\Generated/' {} \;

# Generate GPBMetadata for performance
echo "Generating GPBMetadata..."
protoc \
    -I"$PROTO_DIR" \
    --php_out="$OUT_DIR" \
    --php_metadata_out="$OUT_DIR/GPBMetadata" \
    "$PROTO_DIR"/docuflow/v1/document.proto

echo "Generation complete!"
echo "Generated classes in: $OUT_DIR"