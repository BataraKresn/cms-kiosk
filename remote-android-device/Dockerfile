FROM python:3.11-slim

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1

WORKDIR /app

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
      build-essential \
      libffi-dev \
      libssl-dev \
      libxml2 \
      libxslt1.1 \
      libgdk-pixbuf-2.0-0 \
      libcairo2 \
      libpango-1.0-0 \
      libpangoft2-1.0-0 \
      shared-mime-info \
      fonts-liberation \
      libnss3 \
      libxss1 \
      libasound2 \
      curl; \
    rm -rf /var/lib/apt/lists/*

COPY requirements.txt .

RUN pip install --no-cache-dir -r requirements.txt

COPY . .

EXPOSE 3001

CMD ["sh", "-c", "uvicorn app:app --host ${HOST:-0.0.0.0} --port ${PORT:-3001}"]
