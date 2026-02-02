# Backend Fixes - Architecture Diagrams

## BEFORE (Problems)

```
┌─────────────────────────────────────────────────────────────────┐
│                     DEVICE CONNECTIVITY ISSUES                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────┐                                                    │
│  │  Device  │                                                    │
│  │ (Android)│                                                    │
│  └────┬─────┘                                                    │
│       │                                                          │
│       │ Heartbeat every 30s                                      │
│       ↓                                                          │
│  ┌────────────────┐                                              │
│  │   Laravel CMS  │                                              │
│  │  Controller    │                                              │
│  └────┬───────────┘                                              │
│       │                                                          │
│       │ Raw SQL (no transaction) ❌                              │
│       ↓                                                          │
│  ┌────────────────┐                                              │
│  │    MySQL DB    │◄─────────────┐ Direct write ❌              │
│  │  remotes table │              │ (race condition)              │
│  └────────────────┘              │                               │
│       │                          │                               │
│       │                    ┌─────┴──────┐                        │
│       │                    │  Python    │                        │
│       │                    │  Ping      │                        │
│       │                    │  Service   │                        │
│       │                    └─────┬──────┘                        │
│       │                          │                               │
│       │ Global cache flush ❌    │ HTTP ping                     │
│       │ (every heartbeat)        │                               │
│       ↓                          ↓                               │
│  ┌────────────────┐         ┌──────────┐                        │
│  │     Cache      │         │  Device  │                        │
│  │   (Flushed)    │         │  HTTP    │                        │
│  └────────────────┘         └──────────┘                        │
│                                                                   │
│  PROBLEMS:                                                       │
│  • Race conditions between heartbeat and external service        │
│  • Status flapping (Connected ↔ Disconnected)                   │
│  • Cache thrashing (100 devices = 3.3 flushes/sec)              │
│  • No rate limiting                                              │
│  • No grace periods                                              │
│  • External service is authority (not CMS)                       │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## AFTER (Fixed)

```
┌─────────────────────────────────────────────────────────────────┐
│                  FIXED DEVICE CONNECTIVITY                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────┐                                                    │
│  │  Device  │                                                    │
│  │ (Android)│                                                    │
│  └────┬─────┘                                                    │
│       │                                                          │
│       │ Heartbeat every 30s                                      │
│       ↓                                                          │
│  ┌─────────────────────┐                                         │
│  │ Rate Limit          │ ✅ NEW                                  │
│  │ Middleware          │                                         │
│  │ • Min 10s interval  │                                         │
│  │ • Max 10/min        │                                         │
│  └────┬────────────────┘                                         │
│       │                                                          │
│       │ Pass / 429 Too Many Requests                             │
│       ↓                                                          │
│  ┌─────────────────────┐                                         │
│  │   Controller        │                                         │
│  │  (Refactored)       │                                         │
│  └────┬────────────────┘                                         │
│       │                                                          │
│       │ Calls service                                            │
│       ↓                                                          │
│  ┌─────────────────────┐ ✅ NEW                                  │
│  │ HeartbeatService    │                                         │
│  │                     │                                         │
│  │ • processHeartbeat()│                                         │
│  │ • Atomic updates    │                                         │
│  │ • Grace periods     │                                         │
│  │ • Logging           │                                         │
│  └────┬────────────────┘                                         │
│       │                                                          │
│       │ DB::transaction                                          │
│       ↓                                                          │
│  ┌─────────────────────┐                                         │
│  │    MySQL DB         │                                         │
│  │  lockForUpdate()    │ ✅ Row-level lock                       │
│  │                     │                                         │
│  │  Status levels:     │                                         │
│  │  • Connected        │                                         │
│  │  • Temp Offline     │ ✅ NEW                                  │
│  │  • Disconnected     │                                         │
│  └─────────────────────┘                                         │
│       │            ▲                                             │
│       │            │                                             │
│       │            │ API call (respects heartbeat) ✅            │
│       │            │                                             │
│       │       ┌────┴─────────┐                                   │
│       │       │  External    │                                   │
│       │       │  Service     │                                   │
│       │       │  Controller  │ ✅ NEW                            │
│       │       └────┬─────────┘                                   │
│       │            ▲                                             │
│       │            │ HTTP API                                    │
│       │            │                                             │
│       │       ┌────┴─────────┐                                   │
│       │       │   Python     │                                   │
│       │       │   Ping       │                                   │
│       │       │   Service    │ (updated)                         │
│       │       └──────────────┘                                   │
│       │                                                          │
│       │ Scoped cache invalidate ✅                               │
│       │ (only this device)                                       │
│       ↓                                                          │
│  ┌─────────────────────┐                                         │
│  │     Cache           │                                         │
│  │  (Per-device only)  │                                         │
│  └─────────────────────┘                                         │
│                                                                   │
│  ┌─────────────────────┐                                         │
│  │   Scheduler         │ ✅ NEW                                  │
│  │  (Every minute)     │                                         │
│  └────┬────────────────┘                                         │
│       │                                                          │
│       │ Cron trigger                                             │
│       ↓                                                          │
│  ┌─────────────────────┐                                         │
│  │ StatusMonitor       │ ✅ NEW                                  │
│  │ Command             │                                         │
│  │                     │                                         │
│  │ Enforces timeouts:  │                                         │
│  │ • 30s → Connected   │                                         │
│  │ • 60s → Temp Off    │                                         │
│  │ • 300s → Disc       │                                         │
│  └─────────────────────┘                                         │
│                                                                   │
│  IMPROVEMENTS:                                                   │
│  ✅ No race conditions (row-level locking)                       │
│  ✅ No status flapping (grace periods)                           │
│  ✅ No cache thrashing (scoped invalidation)                     │
│  ✅ Rate limiting (prevents abuse)                               │
│  ✅ CMS is authority (external service is secondary)             │
│  ✅ Server-initiated reconnection works                          │
│  ✅ Comprehensive logging for debugging                          │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## STATUS TRANSITION FLOW (New Three-Tier System)

```
┌────────────────────────────────────────────────────────────────┐
│                    DEVICE STATUS STATE MACHINE                  │
└────────────────────────────────────────────────────────────────┘

                    ┌──────────────┐
                    │   INITIAL    │
                    │ REGISTRATION │
                    └──────┬───────┘
                           │
                           ↓
                  ┌────────────────┐
            ┌────►│   CONNECTED    │◄────┐
            │     │                │     │
            │     │ • Heartbeat    │     │
            │     │   received     │     │
            │     │   within 40s   │     │
            │     └───┬────────┬───┘     │
            │         │        │         │
            │         │        │         │
Heartbeat   │         │ 40-60s │         │ Heartbeat
received    │         │ no HB  │         │ received
            │         │        │         │
            │         ↓        │         │
            │   ┌──────────────┴──┐      │
            │   │  TEMPORARILY    │      │
            └───┤    OFFLINE      │──────┘
                │                 │
                │ • Grace period  │
                │   active        │
                │ • Will recover  │
                │   on heartbeat  │
                └────┬────────────┘
                     │
                     │ 300s+ no heartbeat
                     │
                     ↓
              ┌──────────────┐
              │ DISCONNECTED │
              │              │
              │ • Permanent  │
              │   offline    │
              │ • Needs      │
              │   attention  │
              └──────────────┘

RULES:
• Heartbeat within 40s        → CONNECTED
• No heartbeat 40-60s          → TEMPORARILY OFFLINE  
• No heartbeat 60-300s         → TEMPORARILY OFFLINE
• No heartbeat 300s+           → DISCONNECTED
• External ping only acts if heartbeat > 30s old
• All transitions are logged with reason
```

---

## RACE CONDITION PREVENTION

### Before (Problem)
```
Time    Laravel Heartbeat        Python Ping Service
─────────────────────────────────────────────────────
T0      Device sends heartbeat
T0+1    UPDATE status=Connected  
        (no lock)                
T0+2                             HTTP ping timeout
T0+3                             UPDATE status=Disconnected
                                 (overwrites!)
T0+4    ❌ Status = Disconnected (wrong!)
```

### After (Fixed)
```
Time    Laravel Heartbeat        Python Ping Service
─────────────────────────────────────────────────────
T0      Device sends heartbeat
T0+1    BEGIN TRANSACTION
        SELECT ... FOR UPDATE
        (row locked)
T0+2    UPDATE status=Connected                             
        COMMIT
        (lock released)
T0+3                             API call to CMS
T0+4                             Service checks:
                                 last_heartbeat_age = 3s
                                 interval = 30s
                                 ✅ IGNORE (too recent)
T0+5    ✅ Status = Connected (correct!)
```

---

## DATA FLOW DIAGRAM

```
┌─────────────────────────────────────────────────────────────┐
│                     HEARTBEAT DATA FLOW                      │
└─────────────────────────────────────────────────────────────┘

  Device                Middleware            Service
    │                      │                     │
    │  POST /heartbeat     │                     │
    ├─────────────────────►│                     │
    │                      │                     │
    │                      │ Check rate limit    │
    │                      │ (10s min, 10/min)   │
    │                      │                     │
    │                      ├─ IF EXCEEDED        │
    │  429 Too Many        │                     │
    │◄─────────────────────┤                     │
    │                      │                     │
    │                      ├─ IF OK              │
    │                      │  Extract metrics    │
    │                      ├────────────────────►│
    │                      │                     │
    │                      │                     │ processHeartbeat()
    │                      │                     ├─ BEGIN TX
    │                      │                     ├─ LOCK ROW
    │                      │                     ├─ UPDATE
    │                      │                     ├─ LOG
    │                      │                     ├─ COMMIT
    │                      │                     │
    │                      │  Result             │
    │  200 OK              │◄────────────────────┤
    │◄─────────────────────┤                     │
    │                      │                     │
    │ {                    │                     │
    │  "remote_control_    │                     │
    │   enabled": true,    │                     │
    │  "should_reconnect": │                     │
    │   false              │                     │
    │ }                    │                     │
    │                      │                     │

  Cache                Database              Logs
    │                      │                     │
    │                      │                     │
    │  Invalidate          │  UPDATE remotes     │
    │  device_token_X      │  SET status=...     │
    │◄─────────────────────┤  last_seen_at=...   │
    │                      │  WHERE id=X         │  Status transition
    │  Invalidate          │  FOR UPDATE         │  logged
    │  device_status_X     │                     ├─────────────►
    │◄─────────────────────┤                     │
    │                      │                     │
    │  (No global flush)   │  (Row lock only)    │  (Structured)
    │                      │                     │
```

---

## SCHEDULED MONITORING FLOW

```
┌─────────────────────────────────────────────────────────────┐
│              SCHEDULED STATUS MONITORING                     │
└─────────────────────────────────────────────────────────────┘

  Linux Cron          Laravel Scheduler      Command
     │                      │                   │
     │  Every minute        │                   │
     ├─────────────────────►│                   │
     │                      │                   │
     │                      │ Trigger           │
     │                      ├──────────────────►│
     │                      │                   │
     │                      │                   │ For each device:
     │                      │                   ├─ Check last_seen_at
     │                      │                   ├─ Calculate age
     │                      │                   ├─ Apply rules:
     │                      │                   │  • <40s → Connected
     │                      │                   │  • 40-300s → Temp Off
     │                      │                   │  • 300s+ → Disc
     │                      │                   │
     │                      │                   ├─ IF status changed:
     │                      │                   │  BEGIN TX
     │                      │                   │  LOCK ROW
     │                      │                   │  UPDATE
     │                      │                   │  LOG TRANSITION
     │                      │                   │  COMMIT
     │                      │                   │
     │                      │  Stats            │
     │                      │◄──────────────────┤
     │                      │                   │
     │  (Next minute)       │                   │
     ├─────────────────────►│                   │
     │                      │                   │

  Database              Logs                Cache
     │                   │                    │
     │  Multiple         │                    │
     │  transactions     │  Log each          │
     │  (row locks)      │  transition        │
     │                   │  with reason       │  Invalidate
     │◄──────────────────┼───────────────────►│  changed devices
     │                   │                    │
```

---

## COMPARISON TABLE

| Aspect | Before ❌ | After ✅ |
|--------|----------|---------|
| **State Updates** | Raw SQL, no transaction | Transaction + row lock |
| **Race Conditions** | Frequent (heartbeat vs ping) | Prevented (atomic) |
| **Status Levels** | 2 (Connected/Disconnected) | 3 (+ Temporarily Offline) |
| **Grace Periods** | None (instant offline) | 60s grace period |
| **Cache Strategy** | Global flush every HB | Scoped per device |
| **Rate Limiting** | None | 10s min, 10/min max |
| **Authority** | External service | CMS (heartbeat primary) |
| **Reconnection** | Hardcoded false | Functional signaling |
| **Logging** | Minimal | Comprehensive structured |
| **Timeout Enforcement** | Manual DB query | Scheduled command |
| **Coordinator** | None | External service API |

---

**All diagrams reflect the implemented architecture as of February 2, 2026**
