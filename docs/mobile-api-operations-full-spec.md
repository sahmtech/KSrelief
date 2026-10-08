# KSrelief — Mobile API Specification (Operations Domain)

**Audience:** Flutter / mobile developers  
**Backend:** Laravel 11 (`ksrelief-v0`)  
**Web reference:**

- [Attendance](https://phplaravel-1451551-6496195.cloudwaysapps.com/operations/attendance)
- [Transportation](https://phplaravel-1451551-6496195.cloudwaysapps.com/operations/transportation)
- [Activities](https://phplaravel-1451551-6496195.cloudwaysapps.com/operations/activities)

**Document version:** 1.0.0  
**Last aligned with codebase:** 2026-10-08  

**Implementation status:** REST API under **`/api/v1/operations/*`** is implemented (Laravel Sanctum). Mirror web behavior via the same Form Requests, Services, Policies, and JSON Resources.

---

## 1. Executive summary

Operations covers three modules:

| Module | Web path | API prefix |
|--------|----------|------------|
| Attendance | `/operations/attendance` | `/api/v1/operations/attendance` |
| Transportation | `/operations/transportation` | `/api/v1/operations/transportation` |
| Activities | `/operations/activities` | `/api/v1/operations/activities` |

**Auth:** Bearer token from `POST /api/v1/auth/login` (same as patients module).

**Campaign scoping:** When `MOBILE_API_CAMPAIGN_SCOPE=true` (default), list/show/mutate operations only allow campaigns the user is assigned to (same rules as patients — see `PatientAccessService`).

---

## 2. Base URL & headers

| Item | Value |
|------|--------|
| API base | `https://{host}/api/v1` |
| Auth header | `Authorization: Bearer {token}` |
| Accept | `application/json` |
| Content-Type | `application/json` (except file uploads elsewhere) |
| Locale (optional) | `Accept-Language: ar` or `en` |

---

## 3. Bootstrap (lookups + permissions)

### `GET /api/v1/bootstrap/operations-module`

**Requires:** at least one of `attendance.view`, `transportation.view`, `activity.view`.

**Response (summary):**

- `campaigns` — scoped list `{ id, name, code }`
- `campaign_visibility` — `{ mode: "all"|"assigned_campaigns", campaign_ids: [] }`
- `permissions` — `{ attendance: {...}, transportation: {...}, activities: {...} }`
- Lookups: `attendance_statuses`, `member_roles`, `specialties`, `transportation_locations`, `trip_types`, `trip_statuses`, `activity_types`, `patient_stages`, `activity_statuses`

Call once after login to drive filters, pickers, and UI affordances.

---

## 4. Attendance API

### Permissions

| Permission | Capability |
|------------|------------|
| `attendance.view` | List, show |
| `attendance.create` | Create, quick sheet, bulk |
| `attendance.update` | Update |
| `attendance.delete` | Delete |
| `attendance.export` | Web export (not exposed on mobile API yet) |

Policy: `AttendancePolicy`.

### Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/operations/attendance` | Paginated list + today stats |
| GET | `/operations/attendance/quick?campaign_id=&attendance_date=&shift_number=` | Quick sheet (members + existing rows) |
| POST | `/operations/attendance` | Single record |
| POST | `/operations/attendance/bulk` | Bulk upsert rows |
| GET | `/operations/attendance/{id}` | Detail |
| PUT | `/operations/attendance/{id}` | Update |
| DELETE | `/operations/attendance/{id}` | Delete |

### List query parameters

`search`, `campaign_id`, `date_from`, `date_to`, `shift_number`, `attendance_status_id`, `member_role_id`, `specialty_id`, `per_page` (1–100, default 25).

### Create / update body (JSON)

```json
{
  "campaign_id": 1,
  "member_id": 12,
  "attendance_date": "2026-10-08",
  "shift_number": 1,
  "attendance_status_id": 2,
  "check_in": "08:00",
  "check_out": "16:00",
  "notes": "optional"
}
```

Validation: `StoreAttendanceRequest` / `UpdateAttendanceRequest` (member must be on campaign; no duplicate member+date+shift).

### Bulk body

```json
{
  "campaign_id": 1,
  "attendance_date": "2026-10-08",
  "shift_number": 1,
  "rows": [
    {
      "member_id": 12,
      "attendance_status_id": 1,
      "check_in": null,
      "check_out": null,
      "notes": null
    }
  ]
}
```

### Quick sheet response

- `members` — assignable members for campaign (active assignment)
- `existing_by_member_id` — map of `member_id` → `AttendanceResource`
- `stats` — `getCampaignStats(campaignId, date)`

### Resource shape

`AttendanceResource`: id, campaign_id, member_id, dates/times, status object, member/campaign embeds, worked minutes/hours labels.

---

## 5. Transportation API

### Permissions

| Permission | Capability |
|------------|------------|
| `transportation.view` | Trips, location search |
| `transportation.create` | Create trip; quick-create location |
| `transportation.update` | Update trip |
| `transportation.delete` | Delete trip |
| `transportation.manage_passengers` | Add/remove passengers |
| `transportation.change_status` | Status transitions |

Policy: `TransportationTripPolicy`.

### Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/operations/transportation/trips` | Paginated list |
| POST | `/operations/transportation/trips` | Create |
| GET | `/operations/transportation/trips/{id}` | Detail + status_transitions |
| PUT | `/operations/transportation/trips/{id}` | Update |
| PATCH | `/operations/transportation/trips/{id}/status` | Change status |
| DELETE | `/operations/transportation/trips/{id}` | Delete |
| GET | `/operations/transportation/trips/{id}/passenger-options` | Members/patients not yet on trip |
| POST | `/operations/transportation/trips/{id}/passengers` | Add passenger |
| DELETE | `/operations/transportation/trips/{id}/passengers/{passengerId}` | Remove passenger |
| GET | `/operations/transportation/locations/search?q=&limit=` | Location autocomplete |
| POST | `/operations/transportation/locations` | Quick-create location |

### Create trip body

```json
{
  "campaign_id": 1,
  "trip_date": "2026-10-08",
  "departure_time": "09:00",
  "arrival_time": "10:30",
  "from_location_id": 3,
  "to_location_id": 5,
  "trip_type": "mixed_transport",
  "vehicle_number": "ABC-123",
  "driver_name": "Driver",
  "capacity": 20,
  "notes": null
}
```

`trip_type` enum: `patient_transport`, `member_transport`, `mixed_transport`.

`status` enum (via PATCH): `planned`, `in_progress`, `completed`, `cancelled` — only allowed transitions returned in `status_transitions` on show.

### Add passenger body

```json
{
  "passenger_type": "member",
  "member_id": 12,
  "notes": null
}
```

Or `passenger_type: "patient"` with `patient_id`. Rules in `AddPassengerRequest` (trip type compatibility enforced in service).

### Resource shape

`TransportationTripResource` + nested `TransportationPassengerResource`.

---

## 6. Activities API

### Permissions

| Permission | Capability |
|------------|------------|
| `activity.view` | List, calendar, show |
| `activity.create` | Create |
| `activity.update` | Update, reschedule |
| `activity.delete` | Delete |
| `activity.manage_participants` | Participants |
| `activity.change_status` | Status |

Policy: `ActivityPolicy`.

### Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/operations/activities` | Paginated list |
| GET | `/operations/activities/calendar/events?start=&end=&campaign_id=&activity_type_id=` | Calendar feed |
| POST | `/operations/activities` | Create |
| GET | `/operations/activities/{id}` | Detail |
| PUT | `/operations/activities/{id}` | Update |
| PATCH | `/operations/activities/{id}/reschedule` | Date/time only |
| PATCH | `/operations/activities/{id}/status` | Status change |
| DELETE | `/operations/activities/{id}` | Delete |
| GET | `/operations/activities/{id}/participant-options` | Available members/patients |
| POST | `/operations/activities/{id}/participants` | Add one |
| POST | `/operations/activities/{id}/participants/bulk` | Bulk add |
| DELETE | `/operations/activities/{id}/participants/{participantId}` | Remove |

### Create activity body

```json
{
  "campaign_id": 1,
  "activity_type_id": 2,
  "patient_stage_id": null,
  "title": "Team briefing",
  "description": null,
  "activity_date": "2026-10-08",
  "start_time": "14:00",
  "end_time": "15:00",
  "location": "Hotel lobby",
  "max_participants": 50
}
```

### Reschedule body

`activity_date`, `start_time`, `end_time` (required).

### Status body

`status`, optional `notes`.

### Bulk participants

Either `rows[]` with `participant_type` + `member_id`/`patient_id`, or shorthand `member_ids[]` / `patient_ids[]`.

### Calendar events

Same shape as web FullCalendar JSON: `id`, `title`, `start`, `end`, colors, `extendedProps` (no web URL — use app routes to `GET .../activities/{id}`).

### Resource shape

`ActivityResource` + `ActivityParticipantResource`.

---

## 7. Errors & HTTP codes

| Code | Meaning |
|------|---------|
| 401 | Missing/invalid token |
| 403 | Policy or campaign scope (`campaign_not_accessible`) |
| 404 | Model not found or nested resource mismatch |
| 422 | Validation (`message` + Laravel `errors` bag) or business rule from service |
| 201 | Created (store endpoints) |

---

## 8. Flutter checklist

1. Login → store token → `GET /bootstrap/operations-module`.
2. Respect `permissions.*` flags before showing FABs/actions.
3. Always pass `campaign_id` filter when user is campaign-scoped.
4. Attendance: use `quick` + `bulk` for shift entry UX.
5. Transportation: search locations before create; use `passenger-options` before add.
6. Activities: load `calendar/events` for month view; `reschedule` for drag-drop equivalent.
7. Handle 403 campaign errors by hiding cross-campaign data (never cache other campaigns’ IDs).

---

## 9. Laravel implementation map

| Layer | Paths |
|-------|--------|
| Routes | `routes/api.php` → `operations.*` |
| Controllers | `app/Http/Controllers/Api/V1/{Attendance,TransportationTrip,TransportationPassenger,TransportationLocation,Activity,ActivityParticipant,OperationsBootstrap}Controller.php` |
| Campaign scope | `PatientAccessService::scopeVisibleByCampaign`, `assertCanAccessCampaign` |
| Services | `AttendanceService`, `TransportationService`, `ActivityService` (unchanged) |
| Resources | `AttendanceResource`, `TransportationTripResource`, `ActivityResource`, … |

Web routes remain in `routes/web.php` under `/operations/*` (session auth).
