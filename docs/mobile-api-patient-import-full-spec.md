# KSrelief — Mobile API: Patient Excel Import

**Audience:** Flutter / mobile developers  
**Backend module:** Patient import ([web reference](https://phplaravel-1451551-6496195.cloudwaysapps.com/patients/import))  
**API base:** `{APP_URL}/api/v1`  
**Auth:** Laravel Sanctum Bearer token  
**Document version:** 1.0.0  
**Aligned with codebase:** 2026-08-05  

---

## Table of contents

1. [Overview](#1-overview)
2. [Permissions & roles](#2-permissions--roles)
3. [Campaign access (API)](#3-campaign-access-api)
4. [End-to-end flow (Flutter)](#4-end-to-end-flow-flutter)
5. [Excel template contract](#5-excel-template-contract)
6. [Upload rules & multipart](#6-upload-rules--multipart)
7. [Batch lifecycle & statuses](#7-batch-lifecycle--statuses)
8. [API endpoints reference](#8-api-endpoints-reference)
9. [Row validation (per patient row)](#9-row-validation-per-patient-row)
10. [File-level validation (whole file)](#10-file-level-validation-whole-file)
11. [Duplicates](#11-duplicates)
12. [Approve & patient creation defaults](#12-approve--patient-creation-defaults)
13. [Error export](#13-error-export)
14. [JSON shapes (resources)](#14-json-shapes-resources)
15. [Polling & async processing](#15-polling--async-processing)
16. [Flutter checklist](#16-flutter-checklist)
17. [Backend source map](#17-backend-source-map)
18. [Appendix — API `meta` payload](#appendix--api-meta-payload)

---

## 1. Overview

Patient import lets staff upload **one Excel/CSV file** to register **many patients** for a **single selected campaign**. The server:

1. Validates the **file template** (headers, sheet type)  
2. Validates **each row**  
3. Marks **duplicates**  
4. Waits for **human approval**  
5. Creates **Patient** records for valid, non-duplicate rows  

**Not supported:** multi-sheet campaign workbooks (Day 1 / Day 2). Only the **official single-sheet template**.

**Config source of truth:** `config/patient_import.php`

---

## 2. Permissions & roles

| Permission | Mobile capability |
|------------|-------------------|
| `patient.import_excel` | Download template, read `meta`, **upload** file |
| `patient.import_history` | List batches, view batch + rows, download errors |
| `patient.import_approve` | **Approve** import (creates patients) |

Typical roles (Spatie):

| Role | Import |
|------|--------|
| `super_admin` / `campaign_manager` | Full import + approve |
| `campaign_coordinator` | Full import group (upload, history, approve) |
| `doctor` | **No** import permissions by default |

Always read live permissions from `GET /api/v1/auth/me` → `user.permissions`.

---

## 3. Campaign access (API)

When `MOBILE_API_CAMPAIGN_SCOPE=true` (default):

- Upload requires `campaign_id` the user may access (member / campaign assignment).  
- Batch list/show/errors are filtered to those campaigns.  
- `403` + message `patients.api.messages.patient_not_in_campaign` if not allowed.

`super_admin` and `campaign_manager` see all campaigns.

---

## 4. End-to-end flow (Flutter)

```
┌─────────────────┐
│ GET import/meta │  ← instructions, columns, limits (once / cache)
└────────┬────────┘
         ▼
┌─────────────────────┐
│ GET import/template │  ← optional: save .xlsx for user to fill
└────────┬────────────┘
         ▼
   User fills Excel locally (row 1 = headers, data from row 2)
         ▼
┌──────────────────────────┐
│ POST import/batches      │  multipart: file + campaign_id + notes?
└────────┬─────────────────┘
         ▼
   status = failed?  → show failure_reason (file rejected)
   status = processing/uploaded? → poll GET batches/{id}
   status = review?  → show row review UI
         ▼
┌──────────────────────────┐
│ POST batches/{id}/approve│  (if can_approve)
└────────┬─────────────────┘
         ▼
   status = completed, imported_count set
```

Optional: `GET batches/{id}/errors` → `.xlsx` with invalid/duplicate rows.

---

## 5. Excel template contract

### 5.1 Structure

| Rule | Value |
|------|--------|
| Sheets | **One** sheet only (first sheet used) |
| Sheet title (export) | `Patients` |
| Row 1 | **Header** — exact column names, exact order |
| Row 2+ | Patient data |
| Empty rows | Skipped (all template columns empty) |

### 5.2 Columns (fixed order)

| # | Column key | Required on row? | Notes |
|---|------------|------------------|-------|
| 1 | `patient_name` | **Yes** | Max 255 chars |
| 2 | `date_of_birth` | No | `YYYY-MM-DD` or Excel date; validated if present |
| 3 | `gender` | No | `male` or `female` if present |
| 4 | `height_cm` | No | Number 20–250 if present |
| 5 | `weight_kg` | No | Number 0.5–500 if present |
| 6 | `contact_number` | No | Max 30 chars if present |

**Row 1 must be exactly:**

```
patient_name | date_of_birth | gender | height_cm | weight_kg | contact_number
```

No extra columns. No missing columns. No reordering.

### 5.3 Sample rows (from official template)

| patient_name | date_of_birth | gender | height_cm | weight_kg | contact_number |
|--------------|---------------|--------|-----------|-----------|----------------|
| Ahmed Al-Zahrani | 2015-06-20 | male | 120 | 32.5 | +966501234567 |
| Sara Al-Otaibi | | | | | |

Second row is valid: **only name required**; other fields empty → defaults on approve.

### 5.4 Header aliases (normalized server-side)

If users slightly rename headers, server maps common aliases before validation, e.g. `name` → `patient_name`, `dob` → `date_of_birth`, `phone` → `contact_number`. **Mobile should still generate the official headers** to avoid rejection.

---

## 6. Upload rules & multipart

### 6.1 HTTP request

```http
POST /api/v1/patients/import/batches
Authorization: Bearer {token}
Content-Type: multipart/form-data
Accept: application/json
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `file` | file | Yes | `xlsx`, `xls`, `csv`; max **20480 KB** (20 MB) |
| `campaign_id` | integer | Yes | Must exist in `campaigns` |
| `notes` | string | No | Max 1000 chars |

### 6.2 Flutter / Dio example

```dart
final formData = FormData.fromMap({
  'campaign_id': campaignId,
  'notes': notes,
  'file': await MultipartFile.fromFile(
    filePath,
    filename: 'patients.xlsx',
  ),
});

final response = await dio.post(
  '/api/v1/patients/import/batches',
  data: formData,
);
```

### 6.3 User-facing upload instructions (show in app)

Use strings from `GET /api/v1/patients/import/meta` → `spec.instructions` (localized via `Accept-Language`).

Summary for UI copy:

1. Select **campaign** before upload.  
2. Download official template; **do not change row 1**.  
3. One sheet only; six columns in fixed order.  
4. **Every data row must have `patient_name`.**  
5. Other columns optional; if filled, they are validated.  
6. Gender: `male` / `female`.  
7. Date: `YYYY-MM-DD` or Excel date cell.  
8. Rows with errors appear in review; only valid non-duplicate rows can be imported after approve.

---

## 7. Batch lifecycle & statuses

| Status | Meaning | Flutter action |
|--------|---------|----------------|
| `uploaded` | File stored; job may not have started | Poll `GET batches/{id}` |
| `processing` | Parsing & validating | Poll every ~5s |
| `review` | Ready for human review | Show rows; enable Approve if permitted |
| `approved` | Brief transient during commit | Poll until `completed` |
| `completed` | Patients created | Show `imported_count` |
| `failed` | File rejected or processing error | Show `failure_reason`; offer re-upload |

**Approvable:** only `review` (`PatientImportBatchStatus::isApprovable()`).

**Sync processing:** default `PATIENT_IMPORT_SYNC=true` → after POST, batch often jumps straight to `review` or `failed`.

---

## 8. API endpoints reference

All require `Authorization: Bearer {token}` unless noted.

### 8.1 `GET /api/v1/patients/import/meta`

**Permission:** `patient.import_excel` **or** `patient.import_history`

Returns full machine-readable spec + template download URL. See [Appendix](#appendix--api-meta-payload).

---

### 8.2 `GET /api/v1/patients/import/template`

**Permission:** `patient.import_excel`

**Response:** binary `.xlsx` file (`patient-import-template.xlsx`).

Headers: use `responseType: ResponseType.bytes` in Dio.

---

### 8.3 `GET /api/v1/patients/import/batches`

**Permission:** `patient.import_history`

**Query:**

| Param | Description |
|-------|-------------|
| `page`, `per_page` | Pagination (default 25, max 100) |
| `campaign_id` | Filter |
| `status` | Filter by batch status code |

**Response:**

```json
{
  "data": [ /* PatientImportBatchResource[] */ ],
  "meta": { "current_page": 1, "last_page": 3, "per_page": 25, "total": 52 },
  "stats": {
    "total": 52,
    "pending_review": 2,
    "processing": 0,
    "completed": 45,
    "failed": 5,
    "patients_imported": 1200
  }
}
```

---

### 8.4 `POST /api/v1/patients/import/batches`

**Permission:** `patient.import_excel`

**Success (201)** — status `review`:

```json
{
  "message": "File uploaded…",
  "batch": { "id": 7, "status": "review", "valid_rows": 10, "invalid_rows": 2, … }
}
```

**Accepted (202)** — still `uploaded` / `processing` (async queue):

Same body; poll until `review` or `failed`.

**File rejected (422):**

```json
{
  "message": "File could not be imported",
  "error": "The file does not match the import template…",
  "batch": { "id": 8, "status": "failed", "failure_reason": "…" }
}
```

---

### 8.5 `GET /api/v1/patients/import/batches/{id}`

**Permission:** `patient.import_history`

**Response:**

```json
{
  "batch": { /* PatientImportBatchResource */ },
  "logs": [ /* PatientImportLogResource[] */ ],
  "logs_meta": { "current_page": 1, "last_page": 1, "per_page": 50, "total": 12 },
  "polling": {
    "recommended_interval_seconds": 5,
    "message": "Processing the file…"
  }
}
```

`polling` only when status is `uploaded` or `processing`.

---

### 8.6 `POST /api/v1/patients/import/batches/{id}/approve`

**Permission:** `patient.import_approve`

**Body:** empty JSON `{}`

**Success (200):**

```json
{
  "message": "8 patient(s) imported successfully.",
  "imported_count": 8,
  "batch": { "status": "completed", "imported_count": 8, … }
}
```

**422:** batch not in `review`, or business rule error (`not_reviewable`).

**Important:** Only rows where `is_valid === true`, `is_duplicate === false`, and not yet imported are created.

---

### 8.7 `GET /api/v1/patients/import/batches/{id}/errors`

**Permission:** `patient.import_history`

**Response:** Excel file `import-errors-batch-{id}.xlsx`

Columns: `row_number`, `patient_name`, `date_of_birth`, `gender`, `height_cm`, `weight_kg`, `contact_number`, `errors`, `duplicate_flag`.

---

## 9. Row validation (per patient row)

Errors are stored in `validation_errors[]` on each log (localized strings).

| Condition | Error |
|-----------|--------|
| Missing `patient_name` | `required` for field patient_name |
| `patient_name` > 255 chars | `too_long` |
| `gender` filled but not male/female | `invalid_gender` |
| `date_of_birth` filled but unparseable | `invalid_date` |
| `date_of_birth` in future | `future_date` |
| `height_cm` not numeric | `invalid_number` |
| `height_cm` out of 20–250 | `out_of_range` |
| `weight_kg` not numeric / out of range | same pattern |
| `contact_number` > 30 chars | `too_long` |

Legacy columns in file (not in template) are **not** accepted — file fails at header level.

---

## 10. File-level validation (whole file)

Batch → `status: failed`, `failure_reason` set:

| Case | User message key |
|------|------------------|
| No data rows | `empty_file` |
| Missing/wrong headers | `header_missing`, `invalid_template`, `invalid_template_order` |
| Campaign workbook (multi-day sheets) | `workbook_not_supported` |
| Missing stored file | `file_missing` |
| No campaign on batch | `campaign_required` |
| Uncaught exception | `processing_failed` |

Show `failure_reason` text directly to the user (already translated).

---

## 11. Duplicates

After row validation, server runs duplicate detection on **valid** rows:

| Type | Behavior |
|------|----------|
| Duplicate `patient_name` in same campaign (file) | `is_duplicate`, reason `duplicate_in_file` |
| Same name already in DB for campaign | `duplicate_name_in_database` |
| Duplicate `file_number` (if column existed — not in current template) | N/A for standard template |

Duplicate rows **cannot** be imported on approve.

---

## 12. Approve & patient creation defaults

For each importable row, server calls `PatientService::createPatient` with:

| Field | Source |
|-------|--------|
| `campaign_id` | Batch campaign |
| `patient_name` | Row (required at validation) |
| `date_of_birth` | Row or **`2000-01-01`** |
| `gender` | Row or **`male`** |
| `height_cm`, `weight_kg`, `contact_number` | Row or null |
| `eligibility_status_id` | **`accepted`** (active) if not resolved |
| `admission_status` | **`not_admitted`** |
| `file_number` | Auto-generated if empty |
| `screening_data` | Empty array |

After success, log gets `patient_id` → open patient via `GET /api/v1/patients/{id}`.

---

## 13. Error export

Use when many invalid rows — same data as review table, plus combined error text column.

Requires permission `patient.import_history`. Available when batch has finished processing (`review`, `completed`, or `failed` with row logs).

---

## 14. JSON shapes (resources)

### Batch (`PatientImportBatchResource`)

Key fields: `id`, `campaign`, `original_file_name`, `status`, `status_label`, counts (`total_rows`, `valid_rows`, `invalid_rows`, `duplicate_rows`, `imported_count`), `failure_reason`, `notes`, `imported_by`, `approved_by`, `approved_at`, `permissions.can_approve`, `permissions.can_download_errors`.

### Log (`PatientImportLogResource`)

Key fields: `row_number`, row columns, `is_valid`, `is_duplicate`, `is_importable`, `validation_errors`, `duplicate_reason`, `patient_id`, `status`, `status_code` (`valid` | `invalid` | `duplicate` | `imported`).

---

## 15. Polling & async processing

| `PATIENT_IMPORT_SYNC` | Behavior |
|-----------------------|----------|
| `true` (default) | POST returns final state immediately (`review` or `failed`) |
| `false` | Queue worker required; POST may return `202`; poll until `review` |

If poll exceeds ~60s, show admin hint (`queue_stuck` message in web lang).

---

## 16. Flutter checklist

- [ ] On import screen load: `GET import/meta` + cache  
- [ ] Campaign picker limited to `auth/me` → `patient_visibility.campaign_ids` when scoped  
- [ ] Template download → open/share file  
- [ ] Upload via **multipart** with correct field name `file`  
- [ ] Handle **422** file rejection vs **201/202** success  
- [ ] Poll batch show until `review` or `failed`  
- [ ] Row list: color by `status_code`  
- [ ] Approve button only if `batch.permissions.can_approve` and `valid_rows > 0`  
- [ ] Confirm dialog: `confirm_approve` with count  
- [ ] Errors export: save bytes to file  
- [ ] Pass `Accept-Language: ar` or `en` for messages  

---

## 17. Backend source map

| Concern | Path |
|---------|------|
| API routes | `routes/api.php` → `patients/import/*` |
| API controller | `app/Http/Controllers/Api/V1/PatientImportController.php` |
| Web UI (reference) | `app/Http/Controllers/PatientImportController.php` |
| Core logic | `app/Services/PatientImportService.php` |
| Job | `app/Jobs/ProcessPatientImportJob.php` |
| Config | `config/patient_import.php` |
| Template export | `app/Exports/PatientTemplateExport.php` |
| Spec builder | `app/Support/PatientImportSpec.php` |
| Upload validation | `app/Http/Requests/Patient/UploadPatientImportRequest.php` |

---

## Appendix — API `meta` payload

Call:

```http
GET /api/v1/patients/import/meta
Authorization: Bearer {token}
Accept: application/json
Accept-Language: ar
```

Response structure:

```json
{
  "spec": {
    "template": {
      "header_row": 1,
      "data_starts_at_row": 2,
      "single_sheet_only": true,
      "column_order_fixed": true,
      "column_keys_in_order": ["patient_name", "date_of_birth", "gender", "height_cm", "weight_kg", "contact_number"],
      "required_row_fields": ["patient_name"],
      "columns": [ { "key": "patient_name", "label": "…", "required_on_row": true, "validation_hint": "…" } ]
    },
    "upload": {
      "allowed_extensions": ["xlsx", "xls", "csv"],
      "max_file_size_kb": 20480,
      "multipart_field": "file",
      "required_fields": ["file", "campaign_id"],
      "optional_fields": ["notes"]
    },
    "defaults_on_approve": {
      "date_of_birth": "2000-01-01",
      "gender": "male",
      "eligibility_status_code": "accepted",
      "admission_status": "not_admitted"
    },
    "processing": { "sync_by_default": true, "env_flag": "PATIENT_IMPORT_SYNC" },
    "batch_statuses": [ { "code": "review", "label": "…" } ],
    "instructions": [ "… localized steps …" ],
    "default_notes": [ "…" ],
    "permissions": {
      "upload_and_template": "patient.import_excel",
      "history_and_review": "patient.import_history",
      "approve": "patient.import_approve"
    },
    "gender_values": [ { "value": "male", "label": "…" } ]
  },
  "template_download_url": "https://host/api/v1/patients/import/template"
}
```

**Flutter:** Prefer building the entire import UI from this single endpoint so you stay in sync with backend config changes.

---

**Related:** [mobile-api-patients-full-spec.md](./mobile-api-patients-full-spec.md) (patients CRUD & clinical API).
