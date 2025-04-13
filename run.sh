#!/bin/bash
echo "Starting web interface using $(pwd)/database.db on port 80..."
docker run -d \
  -p 80:80 \
  -v $(pwd)/database.db:/nebulaftp/database.db \
  docker.io/library/latest:latest
echo "Starting the FTP Server..."
python3 main.py