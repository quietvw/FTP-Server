# Use the latest official Ubuntu image
FROM ubuntu:latest

# Set environment variables to avoid some interactive prompts
ENV DEBIAN_FRONTEND=noninteractive

# Update package lists and install any required software (optional step)
RUN apt-get update && apt-get install -y \
    curl php python3-pip \
    && apt-get clean && pip3 install pyftpdlib --break-system-package && mkdir -p /nebulaftp && mkdir -p /nebulaftp/root

COPY php_webroot/ /nebulaftp/webroot
COPY main.py/ /nebulaftp/main.py


# Expose ports 80 (HTTP) and 21 (FTP)
EXPOSE 80
EXPOSE 21

# Define the default command (optional, modify based on your use case)
CMD ["bash"]