# KSrelief — Mobile API Specification (Patients Domain)

**Audience:** Flutter / mobile developers  
**Backend:** Laravel 11 (`ksrelief-v0`)  
**Production web (reference):** [https://phplaravel-1451551-6496195.cloudwaysapps.com/patients](https://phplaravel-1451551-6496195.cloudwaysapps.com/patients)  
**Document version:** 1.1.0  
**Last aligned with codebase:** 2026-08-05  

**Implementation status:** REST API **`/api/v1/*`** is implemented in this repo (Laravel Sanctum). Use this document as the Flutter contract; base URL `{APP_URL}/api/v1`.

---

## Table of contents

1. [Executive summary](#1-executive-summary)
2. [Base URL, versioning, headers](#2-base-url-versioning-headers)
3. [Authentication & session](#3-authentication--session)
4. [Authorization (permissions, policies, roles)](#4-authorization-permissions-policies-roles)
5. [Who sees which patients (campaign scoping)](#5-who-sees-which-patients-campaign-scoping)
6. [API conventions (errors, pagination, i18n)](#6-api-conventions-errors-pagination-i18n)
7. [Endpoint catalog (patients module)](#7-endpoint-catalog-patients-module)
8. [Patient CRUD & list filters](#8-patient-crud--list-filters)
9. [Quick search (patient access)](#9-quick-search-patient-access)
10. [Patient overview (`show`) vs brief (quick access)](#10-patient-overview-show-vs-brief-quick-access)
11. [Medical workflow (stages / timeline / history)](#11-medical-workflow-stages--timeline--history)
12. [Medical records (full)](#12-medical-records-full)
13. [Screening data (on patient, not a separate record)](#13-screening-data-on-patient-not-a-separate-record)
14. [Attachments](#14-attachments)
15. [Activities (patient-linked)](#15-activities-patient-linked)
16. [Transportation (patient tab)](#16-transportation-patient-tab)
17. [Reports & export](#17-reports--export)
18. [Import (Excel — admin/coordinator)](#18-import-excel--admincoordinator)
19. [Lookup & bootstrap payloads](#19-lookup--bootstrap-payloads)
20. [Flutter client checklist](#20-flutter-client-checklist)
21. [Backend implementation checklist (Laravel)](#21-backend-implementation-checklist-laravel)
22. [Appendix A — System permissions list](#appendix-a--system-permissions-list)
23. [Appendix B — Role → permission matrix](#appendix-b--role--permission-matrix)
24. [Appendix C — Patient & record enums](#appendix-c--patient--record-enums)
25. [Appendix D — Medical record stage codes & field keys](#appendix-d--medical-record-stage-codes--field-keys)
26. [Appendix E — Composite / special field types (mobile UI)](#appendix-e--composite--special-field-types-mobile-ui)
27. [Appendix F — Existing JSON resources (Laravel)](#appendix-f--existing-json-resources-laravel)
28. [Appendix G — Web route map (today)](#appendix-g--web-route-map-today)

---

## 1. Executive summary

The **web admin panel** already implements the full patients domain (CRUD, workflow, medical records, attachments, activities, etc.) under **session cookie auth** in `routes/web.php`.

For the **mobile dashboard**, this document defines a **REST JSON API** (`/api/v1/...`) that must mirror web behavior **1:1** in:

- Permissions (Spatie) and Laravel policies  
- Validation rules (Form Requests)  
- Services (`PatientService`, `MedicalRecordService`, `PatientWorkflowService`, …)  
- Clinical field definitions in `config/patient_clinical.php`

**Today:**

| Area | Status |
|------|--------|
| Dedicated `routes/api.php` | **Yes** — prefix `/api/v1` |
| Laravel Sanctum / API tokens | **Installed** — `POST /api/v1/auth/login` |
| Patient JSON Resources | **Wired** to `App\Http\Controllers\Api\V1\*` |
| Web JSON (legacy) | `GET /patients/search` still available for session auth |
| Campaign-scoped patient lists (API) | **`MOBILE_API_CAMPAIGN_SCOPE=true`** (default) — see §5 |

**Flutter:** call **`/api/v1`** with Bearer token. Web panel unchanged (session cookies).

---

## 2. Base URL, versioning, headers

| Item | Value |
|------|--------|
| **Proposed API base** | `https://{host}/api/v1` |
| **Web (current)** | `https://{host}/` (session) |
| **Version** | Prefix `v1`; breaking changes → `v2` |

**Required headers (mobile):**

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}   # after Sanctum login
Accept-Language: ar|en          # optional; matches web locale
```

**Multipart uploads:**

```http
Content-Type: multipart/form-data
Authorization: Bearer {token}
```

---

## 3. Authentication & session

### 3.1 Web behavior today (reference)

| Action | Method | Path | Body |
|--------|--------|------|------|
| Login page | GET | `/login` | — |
| Login | POST | `/login` | `email`, `password`, `remember` (optional) |
| Logout | POST | `/logout` | CSRF + session |

**Login validation:** `email` required email, `password` required.

**After success:**

- Session regenerated  
- Inactive users (`UserStatus` ≠ active) are logged out with error  
- Redirect: `DashboardAccessResolver::defaultRoute($user)`  

**Source:** `app/Http/Controllers/Auth/LoginController.php`

### 3.2 Proposed mobile auth (implement on backend)

| Endpoint | Method | Permission | Description |
|----------|--------|------------|-------------|
| `/api/v1/auth/login` | POST | public | Issue API token |
| `/api/v1/auth/logout` | POST | auth | Revoke current token |
| `/api/v1/auth/me` | GET | auth | User + roles + permissions + member/campaign hints |
| `/api/v1/auth/refresh` | POST | auth | Optional token rotation |

#### POST `/api/v1/auth/login`

**Request:**

```json
{
  "email": "doctor@example.com",
  "password": "secret",
  "device_name": "Flutter Android"
}
```

**Response 200:**

```json
{
  "token": "1|plainTextTokenOnlyShownOnce",
  "token_type": "Bearer",
  "user": {
    "id": 12,
    "name": "Dr. Ahmed",
    "email": "doctor@example.com",
    "mobile": "+966…",
    "gender": "male",
    "avatar_url": "https://…/storage/…",
    "status": "active",
    "roles": ["doctor"],
    "role_labels": ["Doctor"],
    "permissions": [
      "patient.view",
      "medical_record.view",
      "stage.view",
      "dashboard.view"
    ],
    "member": {
      "id": 5,
      "member_role_code": "doctor",
      "campaign_ids": [1, 2]
    },
    "campaign_assignment_ids": [],
    "last_login_at": "2026-08-05T10:00:00+03:00"
  }
}
```

**Response 401:** invalid credentials (same semantics as `auth.failed`).

**Response 403:** account inactive (`users.messages.account_inactive`).

**Notes for Flutter:**

- Store `permissions` locally to hide UI actions (server must still enforce).  
- `member.campaign_ids` = campaigns where this user’s **Member** profile is on `campaign_member` pivot (staff assignment).  
- `campaign_assignment_ids` = `campaign_user` rows (future app-level scoping).  
- **Super admin** role name: `super_admin` with permission `*`.

---

## 4. Authorization (permissions, policies, roles)

### 4.1 Permission guard

- Spatie guard: **`web`** (`PermissionRegistry::GUARD`)  
- API tokens should use the same guard / user model so `$user->can('patient.view')` works identically.

### 4.2 Patient-related permissions

| Permission | Used for |
|------------|----------|
| `patient.view` | List, show, brief, search, attachment read |
| `patient.create` | Create patient |
| `patient.update` | Edit patient, upload attachment (policy), delete attachment |
| `patient.delete` | Soft-delete patient |
| `patient.export` | Excel export |
| `patient.import_excel` | Upload import file |
| `patient.import_approve` | Approve import batch |
| `patient.import_history` | View import batches |
| `medical_record.view` | List/show records |
| `medical_record.create` | Create record + defaults helpers |
| `medical_record.update` | Edit record |
| `medical_record.delete` | Delete record |
| `stage.view` | Workflow timeline |
| `stage.change` | Change current stage |
| `stage.history.view` | Stage history list |
| `activity.view` | Patient activities tab |
| `transportation.view` | Patient trips tab |
| `report.view` | Reports module (patients report placeholder) |

Full list: [Appendix A](#appendix-a--system-permissions-list).

### 4.3 Policy matrix (patient domain)

**`PatientPolicy`** (`app/Policies/PatientPolicy.php`):

| Policy method | Rule |
|---------------|------|
| `viewAny` | `patient.view` |
| `view` | `patient.view` (no per-campaign check) |
| `create` | `patient.create` |
| `update` | `patient.update` |
| `delete` | `patient.delete` |
| `export` | `patient.export` |
| `importExcel` | `patient.import_excel` |
| `importApprove` | `patient.import_approve` |
| `importHistory` | `patient.import_history` |
| `viewWorkflow` | `stage.view` **AND** `view` |
| `changeStage` | `stage.change` **AND** `view` |
| `viewStageHistory` | `stage.history.view` **AND** `view` |
| `uploadAttachment` | `update` **OR** (`view` **AND** `medical_record.view`) |
| `deleteAttachment` | `update` only |

**`MedicalRecordPolicy`** (`app/Policies/MedicalRecordPolicy.php`):

| Policy method | Rule |
|---------------|------|
| `viewAny($user, $patient)` | `medical_record.view` + `view($patient)` |
| `view($record)` | `medical_record.view` + `view($record->patient)` |
| `create($patient)` | `medical_record.create` + `view($patient)` |
| `update($record)` | `medical_record.update` + `view($patient)` |
| `delete($record)` | `medical_record.delete` + `view($patient)` |

**`ActivityPolicy`:** CRUD → `activity.view/create/update/delete`; participants → `activity.manage_participants`; status → `activity.change_status`.

### 4.4 System roles (Spatie role names)

From `App\Enums\SystemRole`:

| Role code | Typical mobile use |
|-----------|-------------------|
| `super_admin` | Full access |
| `campaign_manager` | Full patients + records + settings groups |
| `campaign_coordinator` | Patients CRUD (group), records, stages, activities, attendance, transport |
| `doctor` | **Read-only clinical:** `patient.view`, `medical_record.view`, `stage.view` |
| `attendance_officer` | Attendance only |
| `reports_officer` | Reports + dashboard view |

Default **`doctor`** permissions (from `PermissionRegistry::ROLE_PERMISSIONS`): no create/update/delete on patients or records.

**There is no Spatie role named `nurse`.** Clinical staff on a campaign use **`Member`** with **`member_roles.code`** e.g. `doctor`, `coordinator`, `specialist`, `technician` (`MemberRolesSeeder`). That affects **member_select** fields in medical records, not Spatie permissions unless the user account has a system role.

### 4.5 Action visibility matrix (Flutter)

Use this to show/hide buttons (always re-check HTTP 403):

| Action | super_admin / campaign_manager | campaign_coordinator | doctor |
|--------|-------------------------------|----------------------|--------|
| List patients | ✓ | ✓ | ✓ |
| Search | ✓ | ✓ | ✓ |
| View patient / brief | ✓ | ✓ | ✓ |
| Create patient | ✓ | ✓ | ✗ |
| Edit patient | ✓ | ✓ | ✗ |
| Delete patient | ✓ | ✓ | ✗ |
| Change stage | ✓ | ✓* | ✗ |
| View timeline | ✓ | ✓ | ✓ |
| View stage history | ✓ | ✓* | ✗* |
| List medical records | ✓ | ✓ | ✓ |
| Create/edit/delete record | ✓ | ✓ | ✗ |
| Upload attachment | ✓ | ✓ | ✓† |
| Delete attachment | ✓ | ✓ | ✗ |
| View activities tab | ✓ | ✓ | ✗‡ |
| Export Excel | ✓ | ✓ | ✗ |

\* If role includes `stage.change` / `stage.history.view` (coordinator group includes `medical_stages`).  
† Doctor: `uploadAttachment` allowed with `patient.view` + `medical_record.view`.  
‡ Doctor default role has no `activity.view`.

---

## 5. Who sees which patients (campaign scoping)

### 5.1 Current backend behavior (important)

| Mechanism | Enforced on list/search? |
|-----------|---------------------------|
| `PatientPolicy::view` | **No** `campaign_id` check |
| `PatientController@index` | Optional filter `campaign_id` from query only |
| `PatientSearchController` | **No** campaign filter |
| `User::campaignAssignments()` (`campaign_user`) | Documented **NOT YET ENFORCED** |
| `User::member()` → `campaign_member` | Used for **team pickers**, not patient list |

**Result today:** Any user with `patient.view` can open **any patient ID** and search **global** patients (up to 10 hits).

### 5.2 Recommended API behavior for mobile (v1)

Implement in API layer (middleware or `PatientQueryScope`) **without changing web** until product confirms:

| User type | Patient list scope |
|-----------|-------------------|
| `super_admin` | All campaigns |
| `campaign_manager` | All campaigns (or assigned — product choice) |
| `campaign_coordinator` | Campaigns in `member.campaign_ids` **OR** `campaign_user.campaign_id` |
| `doctor` / clinical staff | Same as coordinator **recommended** |
| User with explicit `campaign_id` query | Intersect with allowed campaigns |

**Proposed helper response on `/auth/me`:**

```json
{
  "patient_visibility": {
    "mode": "all|assigned_campaigns",
    "campaign_ids": [1, 2]
  }
}
```

**403 when:** `GET /patients/{id}` for patient outside allowed campaigns (once enforced).

### 5.3 How web assigns doctors to campaigns

- **Member** record linked to **User** (`users` ↔ `members`)  
- **CampaignMember** pivot: which campaigns the member works on  
- Medical record **`member_select`** fields resolve team via `LookupService::getCampaignTeamMembers($patient->campaign_id)` filtered by `member_role` (`doctor`, `coordinator`, `specialist`).

Flutter should load **campaign team** when rendering operation/anesthesia/admission forms.

---

## 6. API conventions (errors, pagination, i18n)

### 6.1 Error shape (proposed standard)

```json
{
  "message": "Human readable summary",
  "errors": {
    "patient_name": ["The patient name field is required."]
  }
}
```

| HTTP | Meaning |
|------|---------|
| 401 | Missing/invalid token |
| 403 | Authenticated but policy/permission denied |
| 404 | Model not found |
| 422 | Validation failed |
| 429 | Throttle (login) |

### 6.2 Pagination (proposed — web currently loads all)

Web `PatientController@index` uses `->get()` without pagination. **Mobile must use pagination:**

```
GET /api/v1/patients?page=1&per_page=25
```

**Response:**

```json
{
  "data": [ /* PatientResource[] */ ],
  "meta": { "current_page": 1, "last_page": 10, "per_page": 25, "total": 240 },
  "stats": { "total": 240, "by_stage": { } }
}
```

`stats` from `PatientStatisticsService::getPatientCounts()` (scoped same as list).

### 6.3 Dates & numbers

- Dates: ISO 8601 strings in JSON; input `YYYY-MM-DD` for date fields  
- Decimals: `height_cm`, `weight_kg` as numbers  
- Gender: `male` | `female`  
- `admission_status`: `admitted` | `not_admitted`  
- Patient `status`: see `PatientRecordStatus` enum  

---

## 7. Endpoint catalog (patients module)

**Legend:** 🔴 not implemented as API yet · 🟢 exists as JSON on web · 🔵 proposed only

### 7.1 Auth

| Method | Path | Status |
|--------|------|--------|
| POST | `/api/v1/auth/login` | 🔵 |
| POST | `/api/v1/auth/logout` | 🔵 |
| GET | `/api/v1/auth/me` | 🔵 |

### 7.2 Patients core

| Method | Path | Permission | Status |
|--------|------|------------|--------|
| GET | `/api/v1/patients` | `patient.view` | 🔵 |
| POST | `/api/v1/patients` | `patient.create` | 🔵 |
| GET | `/api/v1/patients/{id}` | `patient.view` | 🔵 |
| PUT/PATCH | `/api/v1/patients/{id}` | `patient.update` | 🔵 |
| DELETE | `/api/v1/patients/{id}` | `patient.delete` | 🔵 |
| GET | `/api/v1/patients/search` | `patient.view` | 🟢 web: `GET /patients/search?q=` |

### 7.3 Brief / quick access

| Method | Path | Permission | Status |
|--------|------|------------|--------|
| GET | `/api/v1/patients/{id}/brief` | `patient.view` | 🔵 |

### 7.4 Workflow

| Method | Path | Permission | Status |
|--------|------|------------|--------|
| GET | `/api/v1/patients/{id}/workflow/timeline` | `stage.view` + policy | 🔵 |
| GET | `/api/v1/patients/{id}/workflow/history` | `stage.history.view` | 🔵 |
| POST | `/api/v1/patients/{id}/workflow/stage` | `stage.change` | 🟢 partial JSON on web POST |

### 7.5 Medical records

| Method | Path | Permission | Status |
|--------|------|------------|--------|
| GET | `/api/v1/patients/{id}/records` | `medical_record.view` | 🔵 |
| POST | `/api/v1/patients/{id}/records` | `medical_record.create` | 🔵 |
| GET | `/api/v1/patients/{id}/records/{recordId}` | `medical_record.view` | 🔵 |
| PUT | `/api/v1/patients/{id}/records/{recordId}` | `medical_record.update` | 🔵 |
| DELETE | `/api/v1/patients/{id}/records/{recordId}` | `medical_record.delete` | 🔵 |
| GET | `/api/v1/patients/{id}/records/schema?stage_id=` | `medical_record.create` or `view` | 🔵 replaces HTML `stage-fields` |
| GET | `/api/v1/patients/{id}/records/electrode-types?implant_company_id=` | `medical_record.view` | 🟢 web JSON |
| GET/POST | `…/records/*-defaults` | `medical_record.create` | 🟢 web JSON (defaults) |
| GET | `…/records/{recordId}/operation-pdf` | `medical_record.view` | 🔵 binary PDF |

### 7.6 Attachments

| Method | Path | Policy | Status |
|--------|------|--------|--------|
| POST | `/api/v1/patients/{id}/attachments` | `uploadAttachment` | 🔵 |
| GET | `/api/v1/patients/{id}/attachments/{aid}/download` | `view` | 🔵 |
| GET | `/api/v1/patients/{id}/attachments/{aid}/preview` | `view` | 🔵 |
| DELETE | `/api/v1/patients/{id}/attachments/{aid}` | `deleteAttachment` | 🔵 |

### 7.7 Activities (patient-scoped)

| Method | Path | Permission | Status |
|--------|------|------------|--------|
| GET | `/api/v1/patients/{id}/activities` | `activity.view` | 🔵 |
| GET | `/api/v1/patients/{id}/activities/stats` | `activity.view` | 🔵 |

Global activities CRUD remains under `/api/v1/operations/activities` (separate spec).

### 7.8 Transportation (patient tab)

| Method | Path | Permission | Status |
|--------|------|------------|--------|
| GET | `/api/v1/patients/{id}/transportation/trips` | `transportation.view` | 🔵 |
| GET | `/api/v1/patients/{id}/transportation/stats` | `transportation.view` | 🔵 |

### 7.9 Reports & export

| Method | Path | Permission | Status |
|--------|------|------------|--------|
| GET | `/api/v1/reports/patients` | `report.view` | 🔵 placeholder (web empty) |
| POST | `/api/v1/patients/export` | `patient.export` | 🔵 file download |

---

## 8. Patient CRUD & list filters

### 8.1 List filters (mirror web)

Query parameters on `GET /api/v1/patients`:

| Param | Type | Maps to |
|-------|------|---------|
| `search` | string | `Patient::scopeSearch` — name, file_number, contact_number |
| `campaign_id` | int | exact |
| `eligibility_status_id` | int | exact |
| `current_stage_id` | int | exact |
| `admission_status` | string | `admitted` / `not_admitted` |
| `gender` | string | `male` / `female` |
| `created_from` | date | `created_at >=` |
| `created_to` | date | `created_at <=` |
| `page`, `per_page` | int | **API only** |

**Eager loads for list item:** `campaign`, `eligibilityStatus`, `currentStage` (same as web).

**File number colors:** web applies `PatientFileNumberStyleSupport::applyAccentColors` — expose `file_number_color` in list resource for badges.

### 8.2 Create / update body

Validation source: `StorePatientRequest` / `UpdatePatientRequest`.

**Required on create (web form):**

| Field | Rules |
|-------|--------|
| `campaign_id` | exists campaigns |
| `patient_name` | required, max 255 |
| `date_of_birth` | required, date, not future |
| `gender` | required, `male`/`female` |
| `eligibility_status_id` | required, active status |

**Optional:** `file_number` (unique), `height_cm`, `weight_kg`, `contact_number`, `current_stage_id`, `admission_status`, `surgery_day_number`, `rank`, `approval_reason`, `surgical_side`, `notes`, `status`, `photo`, `attachments[]`, all `screening_*` keys from config, all `field_*` for pre-op auto-record.

**Create side effect:** `MedicalRecordService::createPreOperationRecordIfFilled` may create a pre-operation record if pre-op fields sent on patient create.

**Response:** `PatientResource` + `201`.

### 8.3 Delete

Soft delete (`Patient` uses `SoftDeletes`). Permission `patient.delete`.

---

## 9. Quick search (patient access)

### 9.1 Existing endpoint 🟢

```http
GET /patients/search?q={term}
Cookie: session
```

**Rules:**

- Min length **2** else `{ "results": [] }`  
- Max **10** results  
- Search: `patient_name`, `file_number` LIKE  
- Order: exact file_number match first, then prefix matches  

**Response item fields:**

| Field | Description |
|-------|-------------|
| `id` | Patient PK |
| `name` | `patient_name` |
| `file_number` | Campaign patient code |
| `file_number_color` | UI accent |
| `campaign` | Campaign name string |
| `age` | Human label from `ageLabel()` |
| `gender` | Localized label |
| `stage` | Current stage name |
| `eligibility` | Status name |
| `eligibility_color` | Hex |
| `surgery_day` | Label |
| `url` | **Web** route to brief — mobile should use `id` → `GET /api/v1/patients/{id}/brief` |

**Proposed API alias:** `GET /api/v1/patients/search?q=` identical JSON + add `campaign_id` filter when scoping enabled.

---

## 10. Patient overview (`show`) vs brief (quick access)

### 10.1 Full show payload (aggregate)

Mirror `PatientController@show` conditional blocks:

```json
{
  "patient": { /* PatientResource + relations */ },
  "workflow": {
    "timeline": [ /* if stage.view */ ],
    "available_stages": [ /* active ordered PatientStage */ ],
    "history": [ /* PatientStageHistoryResource[] if stage.history.view */ ]
  },
  "medical_records": [ /* MedicalRecordResource[] if medical_record.view */ ],
  "clinical_profile": { /* if medical_record.view — PatientClinicalProfileService */ },
  "screening_field_definitions": { /* metadata for screening_data keys */ },
  "clinical_phases": { /* phase colors/labels */ },
  "transportation": {
    "stats": { "total", "upcoming", "completed" },
    "trips": [ /* if transportation.view */ ]
  },
  "activities": {
    "stats": { "total", "upcoming", "completed", "attended" },
    "items": [ /* ActivityResource[], limit 10 default */ ]
  },
  "permissions": {
    "can_update": true,
    "can_delete": false,
    "can_change_stage": false,
    "can_view_records": true,
    "can_create_record": false
  }
}
```

Compute `permissions` server-side from policies for Flutter.

### 10.2 Brief payload (quick access screen)

Built by `PatientBriefService::build($patient, $clinicalProfile)`.

**Top-level keys:**

| Key | Purpose |
|-----|---------|
| `surgery_context` | Highlight chips (day, rank, side, etc.) |
| `demographics` | Label/value rows |
| `priority_clinical` | Top screening/clinical highlights |
| `phases` | Grouped clinical items by phase (`pre_op`, `intra_op`, `screening`, `post_op`, `follow_up`) |
| `stage_summaries` | Per-stage latest record summary |
| `record_overview` | `{ total, stages_with_data, has_history }` |

**When clinical profile is null** (no `medical_record.view`): brief still returns demographics + surgery context; clinical sections empty/redacted.

**Route (proposed):** `GET /api/v1/patients/{id}/brief`

---

## 11. Medical workflow (stages / timeline / history)

### 11.1 Timeline

**Service:** `PatientWorkflowService::getTimeline`

Each item:

```json
{
  "stage": {
    "id": 3,
    "name": "Operation",
    "code": "operation",
    "color": "#…",
    "sort_order": 5
  },
  "completed": false,
  "current": true,
  "pending": false,
  "history": {
    "id": 88,
    "changed_at": "…",
    "changed_by": { "id": 1, "name": "…" },
    "notes": "…"
  }
}
```

`history` is the **first time** patient entered that stage (keyed by `to_stage_id`), not full audit.

### 11.2 Change stage

**Web:** `POST /patients/{patient}/workflow/stage`  
**Body:** `to_stage_id` (required), `notes` (optional, max 1000)  
**Authorize:** `ChangePatientStageRequest` + `changeStage` policy  

**JSON response (when Accept: application/json):**

```json
{
  "message": "…translated…",
  "stage": "Operation"
}
```

**422:** same stage (`workflow.errors.same_stage`).

**Side effect:** Updates `patients.current_stage_id`, inserts `patient_stage_histories` row.

### 11.3 History list

**Service:** `getHistory` — descending `changed_at`, with `fromStage`, `toStage`, `changedBy`.

Use **`PatientStageHistoryResource`** for JSON.

---

## 12. Medical records (full)

### 12.1 Record model

Table `medical_records`: `patient_id`, `stage_id`, `specialty_id`, `record_date`, `fields_json` (object), `notes`, `submitted_by`, timestamps.

**List:** `MedicalRecordService::getPatientRecords` — typically by patient, includes stage, submitter.

**Show:** Single record + same `fields_json` as stored.

### 12.2 Create / update

**Request base fields:**

| Field | Rules |
|-------|--------|
| `stage_id` | required, exists `patient_stages` |
| `record_date` | required date |
| `specialty_id` | optional |
| `notes` | optional, max 2000 |
| `fields` | optional array **or** dynamic keys |

**Dynamic fields:** Web forms post `field_{key}` for each key in stage schema (see `MergesMedicalRecordFieldInputs`). Mobile should send either:

```json
{
  "stage_id": 4,
  "record_date": "2026-08-05",
  "fields": {
    "surgeon": 12,
    "operation_date": "2026-08-05",
    "implant_company_id": 1,
    "electrode_type_id": 3
  }
}
```

Backend should normalize to same storage as web before save.

**Admission attachments:** optional `admission_attachments[]` files on create (multipart).

**Hidden stages for *new record* dropdown:** `admission`, `activation`, `rehab_education` (`record_form_hidden_stage_codes`) — still may exist as patient workflow stages.

### 12.3 Stage field schema endpoint (critical for Flutter)

**Web today:** `GET /patients/{patient}/records/stage-fields?stage_id=` returns `{ "html": "…" }` — **not suitable for mobile**.

**Proposed:**

```http
GET /api/v1/patients/{id}/records/schema?stage_code=operation
```

**Response:**

```json
{
  "stage_code": "operation",
  "fields": {
    "operation_date": {
      "type": "date",
      "label": "Operation Date",
      "phase": "intra_op",
      "required": true
    },
    "surgeon": {
      "type": "member_select",
      "member_role": "doctor",
      "label": "Surgeon",
      "required": true,
      "options": [ { "id": 5, "name": "Dr. …" } ]
    }
  },
  "options_catalog": {
    "operation_side_of_surgery_options": [ { "value": "left", "label": "Left" } ]
  }
}
```

Build from `PatientClinicalFieldRegistry::getStageFields($stageCode)` + `LookupService` (team members, companies, electrodes, etc.).

### 12.4 Defaults & quick-fill (operation / pre-op / post-op / follow-up)

Web JSON routes (use same paths under `/api/v1`):

| GET | Purpose |
|-----|---------|
| `follow-up-defaults` | Saved user defaults for follow-up form |
| `operation-defaults` | User operation template |
| `pre-operation-defaults` | Pre-op template |
| `post-operation-defaults` | Post-op template |
| `campaign-operation-defaults?implant_company_id=` | Campaign-level operation defaults |
| `operation-quick-fill?implant_company_id=` | Presets list |

Response pattern: `{ "has_defaults": true, "data": { …field map… } }`

POST counterparts save defaults (coordinator+).

### 12.5 Dynamic option creation (during data entry)

POST endpoints (return created option for immediate UI insert):

| POST path | Creates |
|-----------|---------|
| `clinical-select-options` | `{ code, label }` |
| `imaging-options/ct` | `{ id, label }` |
| `imaging-options/mri` | `{ id, label }` |
| `expectation-options` | `{ id, label }` |

Permission: `medical_record.create`.

### 12.6 Operation PDF

`GET …/records/{recordId}/export-operation-pdf` — binary PDF (operative note). Same permission as view record.

---

## 13. Screening data (on patient, not a separate record)

Stored in `patients.screening_data` JSON.

**Field definitions:** `config/patient_clinical.php` → `screening_fields` (see [Appendix D](#appendix-d--medical-record-stage-codes--field-keys)).

**On patient create/update:** keys prefixed `screening_{key}` in form map to `screening_data[key]`.

**Clinical profile for overview tabs:** `PatientClinicalProfileService::buildProfile($patient)` merges screening + latest records by phase — used on show/brief when user can view records.

---

## 14. Attachments

**Model:** `PatientAttachment` — `original_name`, `file_name`, `file_type`, `file_size`, `storage_path`, `notes`, `uploaded_by`.

| Action | Detail |
|--------|--------|
| Upload | `UploadPatientAttachmentRequest`: single `file` or `files[]` max **10**, max **51200 KB** each, mime includes images/video/pdf/office |
| Preview | Inline stream if image/video |
| Download | Attachment download |
| Delete | Policy `deleteAttachment` → soft delete + remove file from disk |

**Proposed JSON list item:**

```json
{
  "id": 9,
  "original_name": "scan.pdf",
  "file_type": "application/pdf",
  "file_size": 102400,
  "human_file_size": "100.0 KB",
  "is_image": false,
  "is_video": false,
  "is_previewable": false,
  "icon": "ti-file-type-pdf",
  "notes": null,
  "uploaded_by": { "id": 2, "name": "…" },
  "created_at": "…",
  "download_url": "/api/v1/patients/1/attachments/9/download"
}
```

---

## 15. Activities (patient-linked)

Patients link to activities via **`activity_participants.patient_id`**.

**On patient show (web):**

- `ActivityStatisticsService::getParticipantStats(patientId: $id)`  
- `getPatientActivities($patientId, $limit = 10)`

**Proposed:**

```http
GET /api/v1/patients/{id}/activities?limit=20
GET /api/v1/patients/{id}/activities/stats
```

Use **`ActivityResource`** + **`ActivityParticipantResource`**.

Requires permission **`activity.view`** — not included in default doctor role.

---

## 16. Transportation (patient tab)

If `transportation.view`:

- `TransportationStatisticsService::getPatientTransportStats($patientId)`  
- `getPatientTrips($patientId)`

Expose under patient show aggregate or dedicated sub-routes.

---

## 17. Reports & export

| Feature | Web status |
|---------|------------|
| `/reports/patients` | **Placeholder** empty view |
| Patient Excel export | `POST patients/export` — `PatientExportService`, permission `patient.export` |

Mobile: treat reports as **future**; implement export as file download endpoint.

---

## 18. Import (Excel — admin/coordinator)

**Full Flutter spec:** [mobile-api-patient-import-full-spec.md](./mobile-api-patient-import-full-spec.md)  
**REST API:** `/api/v1/patients/import/*` (implemented).

Web module under `/patients/import/*`:

- Template columns: `patient_name`, `date_of_birth`, `gender`, `height_cm`, `weight_kg`, `contact_number`  
- **Required row field:** `patient_name` only (`config/patient_import.php`)  
- Permissions: `patient.import_excel`, `patient.import_history`, `patient.import_approve`  

Usually **out of scope** for doctor mobile app; include in admin/coordinator app if needed.

---

## 19. Lookup & bootstrap payloads

Mobile should call once after login (or cache):

| Data | Source |
|------|--------|
| Active campaigns | `Campaign` list (respect visibility) |
| Patient stages | `LookupService::getPatientStages()` |
| Eligibility statuses | `getPatientEligibilityStatuses()` |
| Genders | enum `male`, `female` |
| Admission statuses | enum |
| Implant companies / insertion approaches | settings APIs |
| Activity types | settings |
| Clinical option lists | slices of `config/patient_clinical.php` or dedicated metadata endpoint |

**Proposed:** `GET /api/v1/bootstrap/patients-module`

---

## 20. Flutter client checklist

1. Implement token storage; attach `Authorization` on all calls.  
2. On login, cache `permissions` array; gate every action button.  
3. Implement **search** first (existing semantics); navigate to **brief** then **full show**.  
4. Load **record schema** before render forms; never hardcode field keys.  
5. Support **composite field types** (Appendix E) with nested JSON in `fields_json`.  
6. Handle **403** globally (“No permission”).  
7. Use **pagination** on list even if web does not.  
8. Respect **campaign filter** when API exposes `patient_visibility`.  
9. File uploads: multipart, show progress, respect 50MB limit.  
10. Locale: pass `Accept-Language` for translated validation messages.

---

## 21. Backend implementation checklist (Laravel)

1. Install **`laravel/sanctum`**, add `routes/api.php`, register in `bootstrap/app.php`.  
2. Create `Api/V1/AuthController`, `PatientController`, etc. — delegate to **existing services**.  
3. Wire **`PatientResource`**, **`MedicalRecordResource`**, **`PatientStageHistoryResource`**, **`ActivityResource`**.  
4. Add **`GET records/schema`** JSON (replace HTML stage-fields for mobile).  
5. Implement **`PatientVisibilityScope`** (optional feature flag) for assigned campaigns.  
6. Add pagination to API patient index.  
7. Add **`permissions` object** on patient show/brief responses.  
8. Feature tests per role: doctor read-only, coordinator create record, 403 cases.  
9. Document OpenAPI optional export from this file.

---

## Appendix A — System permissions list

Source: `app/Support/PermissionRegistry.php` — groups include:

`campaign.*`, `patient.*`, `medical_record.*`, `stage.*`, `member.*`, `attendance.*`, `transportation.*`, `activity.*`, `report.*`, `settings.*`, plus settings entities (countries, cities, specialties, implant companies, CT/MRI options, etc.), `user.*`, `role.*`, `dashboard.*`, `operative_note_pdf.*`.

Use `PermissionRegistry::all()` for exhaustive list in backend.

---

## Appendix B — Role → permission matrix

Source: `PermissionRegistry::ROLE_PERMISSIONS`

| Role | Patients / clinical |
|------|---------------------|
| `super_admin` | `*` (all) |
| `campaign_manager` | Full groups: patients, medical_records, medical_stages, … |
| `campaign_coordinator` | Full patient group + medical_records + medical_stages + activities + attendance + transportation + dashboard + campaign.view |
| `doctor` | `patient.view`, `medical_record.view`, `stage.view` only |
| `attendance_officer` | attendance group |
| `reports_officer` | dashboard.view + reports group |

Custom Spatie roles may be created in admin UI — always use **`/auth/me` permissions** at runtime, not role name alone.

---

## Appendix C — Patient & record enums

| Enum | Values (API) |
|------|----------------|
| `Gender` | `male`, `female` |
| `AdmissionStatus` | `admitted`, `not_admitted` |
| `PatientRecordStatus` | see `App\Enums\PatientRecordStatus` |
| `ActivityStatus` | used in activities module |
| `UserStatus` | `active` required for login |

---

## Appendix D — Medical record stage codes & field keys

Source: `config/patient_clinical.php`

### D.1 Stage codes in `stage_fields`

| `stage_code` | Record form keys (summary) |
|--------------|----------------------------|
| `pre_operation` | `physician_assessment`, `imaging_findings`, `audiology_decision`, `speech_assessment` |
| `admission` | `coordinator`, `admission_notes`, `initial_assessment` |
| `anesthesia` | `attending_doctor`, `anesthesia_type`, `npo_time`, `asa_score`, `weight`, `anesthesia_notes` |
| `operation` | `operation_date`, `surgeon`, `implant_company_id`, `electrode_type_id`, `insertion_approach_id`, `side_of_surgery`, `insertion_depth`, `time_in_surgery`, `time_out_surgery`, `audio_test`, `intra_op_findings`, `operation_notes` |
| `follow_up` | `clinical_assessment`, `audiology_assessment`, `speech_assessment`, `follow_up_notes` |
| `post_operation` | `physician_assessment`, `clinical_aud`, `counselling`, `post_op_notes` |
| `activation` | `coordinator`, `activation_date`, `switch_on`, `switch_on_note`, `activation_result`, `comments` |
| `rehab_education` | `specialist`, `session_date`, `post_op_audio_education`, `post_op_speech_education`, `education_notes`, `rehab_plan`, `outcome` |

### D.2 Screening keys (`screening_fields`)

`clinical_aud`, `clinical_speech`, `deafness_age`, `speech_ci_candidate`, `expectations_post_ci`, `imaging_findings`, `cochlear_diameter`, `surgical_consideration`, `medical_history`, `aud_result`, `speech_result`, `consent`, `audiology_link`, `video_link`

### D.3 Phases

`pre_op`, `intra_op`, `post_op`, `screening`, `follow_up` — colors/labels in config `phases`.

---

## Appendix E — Composite / special field types (mobile UI)

When `type` is one of these, `fields_json[key]` is structured JSON — render nested UI, not a single text field:

| type | Usage |
|------|--------|
| `clinical_aud` | Hearing metrics + status |
| `clinical_speech` | Speech screening |
| `imaging_findings` | CT/MRI expandable findings |
| `medical_history_screening` | Structured history |
| `pre_op_physician_assessment` | Pre-op physician block |
| `pre_op_audiology_decision` | Pre-op audiology |
| `pre_op_speech_assessment` | Pre-op speech |
| `follow_up_clinical_assessment` | Follow-up clinical |
| `follow_up_audiology_assessment` | Follow-up audiology |
| `follow_up_speech_assessment` | Follow-up speech |
| `follow_up_notes` | Follow-up notes block |
| `post_op_physician_assessment` | Post-op physician |
| `post_op_clinical_aud` | Post-op audiology |
| `post_op_notes` | Post-op notes |
| `operation_insertion_depth` | Structured depth |
| `operation_audio_test` | Intra-op audio test keys |
| `operation_intra_op_findings` | Findings checklist |
| `member_select` | Stores **member id**; options from campaign team |
| `company_select` / `electrode_select` | Implant catalog |
| `expandable_checklist` | e.g. expectations post CI |

Presentation strings for brief/overview: `ClinicalCompositeFields::present($key, $value, $definition)`.

---

## Appendix F — Existing JSON resources (Laravel)

| Resource | File |
|----------|------|
| `PatientResource` | `app/Http/Resources/PatientResource.php` |
| `PatientCollection` | `app/Http/Resources/PatientCollection.php` |
| `MedicalRecordResource` | `app/Http/Resources/MedicalRecordResource.php` |
| `PatientStageHistoryResource` | `app/Http/Resources/PatientStageHistoryResource.php` |
| `ActivityResource` | `app/Http/Resources/ActivityResource.php` |
| `ActivityParticipantResource` | `app/Http/Resources/ActivityParticipantResource.php` |

Extend resources as needed (`height_cm`, `weight_kg`, `screening_data`, `surgical_side`, `surgery_day_number`, `rank`, `campaign` embed, etc.) before mobile launch.

---

## Appendix G — Web route map (today)

All under `auth` middleware, prefix `/patients`:

| Name | Method | Path |
|------|--------|------|
| `patients.search` | GET | `/patients/search` |
| `patients.index` | GET | `/patients` |
| `patients.store` | POST | `/patients` |
| `patients.brief` | GET | `/patients/{patient}/brief` |
| `patients.show` | GET | `/patients/{patient}` |
| `patients.update` | PUT | `/patients/{patient}` |
| `patients.destroy` | DELETE | `/patients/{patient}` |
| `patients.workflow.timeline` | GET | `/patients/{patient}/workflow` |
| `patients.workflow.change-stage` | POST | `/patients/{patient}/workflow/stage` |
| `patients.workflow.history` | GET | `/patients/{patient}/workflow/history` |
| `patients.records.*` | * | `/patients/{patient}/records/…` |
| `patients.attachments.*` | * | `/patients/{patient}/attachments/…` |

Full route list: `routes/web.php` lines 163–330.

---

**End of specification.** For questions about clinical field JSON shapes, inspect stored `medical_records.fields_json` samples per stage in staging DB or export from web forms.
