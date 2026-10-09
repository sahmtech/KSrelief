# KSrelief — Mobile Push Notifications (FCM)

**Audience:** Flutter developer  
**Backend:** Laravel 11 + FCM HTTP v1 (Firebase Admin SDK JSON on server)  
**Version:** 1.0.0 — 2026-10-09  

---

## 1. Overview

- Doctors (and optionally coordinators) receive **automatic pushes** when clinical/operations events occur in their **assigned campaigns**.
- Admins/coordinators with `push.broadcast` can send **manual** notifications to all doctors, a campaign, or selected user IDs.
- Flutter must **register the FCM device token** after login.

---

## 2. Server setup (DevOps)

1. Upload Firebase Admin JSON to server (from Flutter team), e.g.  
   `storage/app/firebase/credentials.json`  
   **Never commit this file to git.**

2. `.env`:

```env
PUSH_NOTIFICATIONS_ENABLED=true
PUSH_NOTIFICATIONS_QUEUE=true
FIREBASE_CREDENTIALS=/full/path/to/storage/app/firebase/credentials.json
PUSH_RECIPIENT_ROLES=doctor,campaign_coordinator
QUEUE_CONNECTION=database
```

3. Run migrations + queue worker on production:

```bash
php artisan migrate --force
php artisan queue:work --tries=3
```

4. Sync new permission (once):

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder --force
```

---

## 3. Register device token (required)

### `POST /api/v1/device-tokens`

**Auth:** Bearer Sanctum  

**Body:**

```json
{
  "fcm_token": "<token from FirebaseMessaging>",
  "platform": "android",
  "device_name": "Pixel 8"
}
```

`platform`: `android` | `ios`

**Response:** `201` + token metadata.

Call after every login and when FCM token refreshes (`onTokenRefresh`).

### `DELETE /api/v1/device-tokens`

**Body (optional):**

```json
{ "fcm_token": "..." }
```

If omitted, removes **all** tokens for the user. Call on logout.

---

## 4. Notification payload (Flutter)

All pushes include **notification** (title/body) and **data** (string map).

Common data keys:

| Key | Description |
|-----|-------------|
| `type` | Event type (see §5) |
| `patient_id` | Navigate to patient |
| `campaign_id` | Campaign scope |
| `record_id` | Medical record |
| `activity_id` | Activity |
| `trip_id` | Transportation trip |

Handle taps by reading `data['type']` and routing in the app.

---

## 5. Automatic event types

| `type` | Trigger |
|--------|---------|
| `patient_created` | Patient registered (not per-row import) |
| `patient_stage_changed` | Workflow stage change |
| `medical_record_created` | New medical record |
| `activity_created` | New activity |
| `activity_status_changed` | Activity status |
| `activity_participant_added` | Participant added |
| `transportation_passenger_added` | Patient added to trip |
| `transportation_status_changed` | Trip status |
| `patient_import_approved` | Bulk import completed |
| `admin_broadcast` | Manual admin message |

Toggle events in `config/push_notifications.php` on server if needed.

---

## 6. Admin broadcast API

**Permission:** `push.broadcast` (super_admin, campaign_manager, campaign_coordinator)

### `GET /api/v1/push-notifications/recipients?campaign_id=`

List doctors/coordinators with `device_tokens_count`.

### `POST /api/v1/push-notifications/broadcast`

**Body examples:**

All clinical users:

```json
{
  "title": "Team meeting",
  "body": "19:00 hotel lobby",
  "target": "all_doctors"
}
```

One campaign:

```json
{
  "title": "Campaign update",
  "body": "Please review schedules",
  "target": "campaign_doctors",
  "campaign_id": 3
}
```

Selected users:

```json
{
  "title": "Reminder",
  "body": "Check patient 102",
  "target": "user_ids",
  "user_ids": [12, 15],
  "data": { "patient_id": "102" }
}
```

**Response:** `202` + `dispatch` stats (tokens attempted; success/fail updated async if queued).

### `GET /api/v1/push-notifications/history`

Paginated broadcast/dispatch log.

---

## 7. Flutter checklist

1. Firebase project `esghaa-55794` — use same app as mobile `google-services.json` / `GoogleService-Info.plist`.
2. Request notification permission (iOS).
3. After login → `POST /device-tokens`.
4. Foreground: `FirebaseMessaging.onMessage`.
5. Background/opened: `onMessageOpenedApp` / `getInitialMessage` — deep link from `data`.
6. Logout → `DELETE /device-tokens`.

---

## 8. Web admin

Web panel can use the same JSON APIs with Sanctum tokens, or a future Blade UI. Automatic events fire from **web and API** actions equally (shared Services layer).
