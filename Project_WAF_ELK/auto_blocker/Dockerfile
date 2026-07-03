FROM python:3.11-slim

RUN apt-get update && apt-get install -y nginx docker.io && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY auto_blocker.py .
RUN pip install requests

CMD ["python", "-u", "auto_blocker.py"]