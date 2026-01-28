# Redis Capacity for 25 Kiosk Android

## TL;DR
- **Safe:** 1 Redis instance is more than enough for 25 kiosks.
- **Load:** ~4 ops/sec (0.004% of 100,000 ops/sec capacity).
- **Memory:** ~16 MB estimated (0.78% of 2 GB).
- **Latency:** ~0.07 microseconds (measured).
- **Headroom:** Can scale to 1,000+ kiosks before reconsidering.

## Assumptions
- Each kiosk per minute: 1 video playlist + 2 images + 2 news requests.
- Each request ~2 Redis operations (cache/session + occasional write).
- Burst: all kiosks refresh simultaneously.

## Calculations
- Requests/min/kiosk: 5
- Requests/sec (25 kiosks): 25 * 5 / 60 ≈ 2.08
- Redis ops/sec: 2.08 * 2 ≈ 4 ops/sec
- Burst worst-case: ~125 ops/sec (still 0.125% of capacity)

## Resource Usage
- Redis maxmemory: 2 GB
- Estimated usage for 25 kiosks: ~16 MB
- Network: negligible (<0.01 MB/sec)

## Benchmarks (Measured)
- Latency (intrinsic): ~0.0677 microseconds
- Current ops/sec: ~0 (idle)
- Used memory: ~1.37 MB (runtime reading)

## Scaling Guidance
- 1 Redis is fine up to ~1,000 kiosks (expected utilization <30%).
- Add replica/HA only if:
  - >5,000 kiosks, or
  - Zero-downtime requirement, or
  - Measured ops/sec consistently >50,000.

## Action
No changes needed. Keep single Redis for now; monitor ops/sec and memory. Scale only when traffic grows 10–100x.
