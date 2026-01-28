#!/usr/bin/env bash
set -euo pipefail

NETWORK_NAME=${NETWORK_NAME:-kiosk-net}
NETWORK_SUBNET=${NETWORK_SUBNET:-172.28.0.0/16}

repos=(
  "cosmic-media-streaming-dpr|docker-compose.prod.yml|.env"
  "generate-pdf|docker-compose.yml|.env"
  "remote-android-device|docker-compose.yml|.env"
)

ensure_network() {
  if ! docker network inspect "${NETWORK_NAME}" >/dev/null 2>&1; then
    echo "[network] creating ${NETWORK_NAME} (${NETWORK_SUBNET})"
    docker network create --driver bridge --subnet "${NETWORK_SUBNET}" "${NETWORK_NAME}" >/dev/null
  else
    echo "[network] ${NETWORK_NAME} already exists"
  fi
}

check_env() {
  local dir=$1
  local env_file=$2
  if [[ -f "${dir}/${env_file}" ]]; then
    echo "[env] ${dir}/${env_file} present"
  else
    echo "[env] WARNING: ${dir}/${env_file} missing" >&2
  fi
}

update_repo() {
  local dir=$1
  echo "[git] Updating ${dir}"
  git -C "${dir}" pull --ff-only
}

compose_up() {
  local dir=$1
  local compose_file=$2
  echo "[compose] ${dir}/${compose_file} up -d --build"
  docker compose -f "${dir}/${compose_file}" up -d --build
}

deploy_all() {
  ensure_network
  for entry in "${repos[@]}"; do
    IFS='|' read -r dir compose_file env_file <<<"${entry}"
    update_repo "${dir}"
    check_env "${dir}" "${env_file}"
    compose_up "${dir}" "${compose_file}"
  done
}

deploy_all

echo "Deployment finished"
