#!/bin/bash

# Build the szk image and push it to the home-lab registry.
# Then on the server: docker compose pull && docker compose up -d

# Configuration
IMAGE_NAME="registry.leon-home-lab.ddns.net/szk"
TAG="latest"

echo "Building Docker image..."
docker build -t $IMAGE_NAME:$TAG .

if [ $? -eq 0 ]; then
    echo "Build successful! Pushing to registry..."
    docker push $IMAGE_NAME:$TAG

    if [ $? -eq 0 ]; then
        echo "✅ Successfully pushed $IMAGE_NAME:$TAG"
        echo "On the server: docker compose pull && docker compose up -d"
    else
        echo "❌ Failed to push image"
        exit 1
    fi
else
    echo "❌ Build failed"
    exit 1
fi
