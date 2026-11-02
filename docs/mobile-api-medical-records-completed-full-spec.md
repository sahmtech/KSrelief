# KSrelief — Completed Medical Record (Web ↔ Mobile 100%)

**Scope:** Stage code **`completed`** only.  
**Web UI:** No dedicated partial — **no clinical stage fields** are configured or rendered.  
**Parent:** [mobile-api-medical-records-full-spec.md](./mobile-api-medical-records-full-spec.md)  
**Version:** 1.0.0 — 2026-11-02

---

## 1. Summary

**Completed** is a **terminal workflow stage** used to mark closure. On the web medical record form it behaves differently from Pre-Op, Operation, Follow Up, etc.:

| Aspect | Completed stage |
|--------|-----------------|
| `config/patient_clinical.php` → `stage_fields.completed` | **Not defined** → `{}` |
| Stage fields panel on create/edit | **Hidden** (`@if(!empty($stageFields))` is false) |
| Stored `fields_json` | **Empty object** `{}` in normal use |
| User-visible inputs | **`stage_id`**, **`record_date`**, **`notes`** only |
| Templates / defaults API | **None** |

Mobile must **not** invent completion checklists or extra keys. Parity = **header-only form** + optional general notes.

**Related (not this form):** Moving the **patient’s current stage** to Completed uses **`POST …/workflow/stage`** — see [mobile-api-patients-full-spec.md](./mobile-api-patients-full-spec.md). That is separate from creating a **medical record** row for stage `completed`.

---

## 2. Web layout (exact)

**Files:**

- `resources/views/pages/patients/records/_form.blade.php` — always shows stage, date, general notes
- `resources/views/pages/patients/records/_stage_fields.blade.php` — **skipped** when `$stageFields` is empty

```
┌─ Create / Edit Medical Record
│  [col-md-6] Stage *          — includes "Completed" option (not in hidden list)
│  [col-md-6] Record date *
│  (no clinical card — stageFields empty for completed)
│  General notes               — textarea, 3 rows, optional
└─
```

**Show page** (`show.blade.php`):

- Sidebar: date, stage badge (green `#22C55E` typical), submitter, created_at
- **No** clinical fields card (`@if(!empty($stageFields))` is false)
- General **notes** card only if `notes` is filled

---

## 3. API — load form metadata

### 3.1 Schema

```http
GET /api/v1/patients/{patientId}/records/schema?stage_id={completedStageId}
Authorization: Bearer …
```

When `stage_code === "completed"`:

```json
{
  "stage_code": "completed",
  "fields": {},
  "completed_form": { … }
}
```

Resolve `completedStageId` from `schema.stages` where `code === "completed"`.

### 3.2 Dedicated meta

```http
GET /api/v1/patients/{patientId}/records/completed-form-meta
```

```json
{ "data": { … } }
```

**Builder:** `App\Support\CompletedFormMetaBuilder`

### 3.3 `completed_form` keys (reference)

| Key | Purpose |
|-----|---------|
| `stage_code` | `"completed"` |
| `display_name` | e.g. `"Completed"` |
| `title` | Workflow + stage name |
| `field_order` | `[]` |
| `fields` | `{}` |
| `form_ui.stage_fields_panel` | **`false`** |
| `form_ui.header_fields` | stage, record_date, notes |
| `stage_presentation.badge_color_default` | `#22C55E` (use `stages[].color` from schema when present) |
| `defaults_empty` | `{}` |
| `payload_empty` | `{ "fields": [] }` — send `{}` or omit `fields` on POST |
| `templates.supported` | `false` |
| `show_page` | Which cards appear on web show |
| `workflow_context` | Terminal stage + KPI + workflow API hint |

---

## 4. Field specification

There are **no stage-specific field keys** for `completed`.

| Payload key | Required | Notes |
|-------------|----------|--------|
| `stage_id` | yes | Must reference `patient_stages` row with `code === "completed"` |
| `record_date` | yes | ISO date |
| `notes` | no | Record-level general notes (max 2000) |
| `fields` | no | **Empty object** or omitted; do not send clinical keys |

Config: **`stage_fields.completed` is absent** — intentional.

---

## 5. Submit payload (create / update)

```http
POST /api/v1/patients/{patientId}/records
Content-Type: application/json
```

```json
{
  "stage_id": 9,
  "record_date": "2026-11-02",
  "notes": "All follow-up objectives met; case closed.",
  "fields": {}
}
```

Equivalent valid bodies:

```json
{
  "stage_id": 9,
  "record_date": "2026-11-02",
  "notes": "Case closed."
}
```

(no `fields` key — `fields` is nullable in validation)

**Update:** `PUT …/records/{recordId}` — same shape; preserve empty `fields_json`.

**Anti-pattern (do not do on mobile):**

```json
{
  "fields": {
    "completion_summary": "…",
    "switch_on": "…"
  }
}
```

Those keys are **not** in web config for this stage and will not render on web show.

---

## 6. Show / list

- **List / show:** `MedicalRecordResource` → `stage.code === "completed"`, `fields` typically `{}`.
- **Display:** Stage badge + dates + general notes; no composite widgets.
- **Brief / profile:** No dedicated completed field highlights in `PatientBriefService` (no configured keys).

---

## 7. Permissions & visibility

- **Completed** appears in the medical record stage dropdown (not in `record_form_hidden_stage_codes`).
- Schema / meta: `medical_record.view`; create/update: standard record permissions.

---

## 8. Mobile implementation checklist

- [ ] When `completed_form.form_ui.stage_fields_panel === false`, **hide** all clinical field widgets.
- [ ] Show only stage picker, record date, general notes (match web `_form.blade.php`).
- [ ] POST with **`fields: {}`** or omit `fields`.
- [ ] Show screen: metadata sidebar + notes; no empty “clinical form” placeholder unless you mirror web `no_stage_fields_saved` (web omits the card entirely).
- [ ] Use stage **color** from `schema.stages` for badge (default green `#22C55E`).
- [ ] Do **not** call template/default endpoints — none exist.
- [ ] Distinguish **record create** vs **workflow stage change** in product copy.

---

## 9. Common mobile mistakes

| Mistake | Correct |
|---------|---------|
| Building a multi-field “completion” form | **Header-only** — zero `stage_fields` |
| Copying post-op or activation fields | Not on completed web form |
| Expecting `completed_form.select_options` | **None** |
| Sending invented `fields.*` keys | Keep **`fields_json` empty** |
| Confusing with import/operation “completed” status | This spec is **`patient_stages.code = completed`** only |

---

## 10. Source files

| Piece | Path |
|-------|------|
| Stage seed | `database/seeders/PatientStagesSeeder.php` (`code` => `completed`) |
| Web form | `resources/views/pages/patients/records/_form.blade.php` |
| Stage fields (empty) | `resources/views/pages/patients/records/_stage_fields.blade.php` |
| Show | `resources/views/pages/patients/records/show.blade.php` |
| API meta | `app/Support/CompletedFormMetaBuilder.php` |
| Fields registry | `PatientClinicalFieldRegistry::getStageFields('completed')` → `[]` |
| Save | `MedicalRecordService::buildFieldsJson` (no keys to normalize) |

---

**End of Completed medical record specification.**
