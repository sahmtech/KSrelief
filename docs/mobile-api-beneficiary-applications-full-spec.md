# KSrelief — Beneficiary Registration Applications (Web Public + Staff Review)

**Scope:** Public beneficiary registration form + staff review queue (web + mobile API).  
**Version:** 1.0.0 — 2026-11-17  
**Related:** [mobile-api-patients-full-spec.md](./mobile-api-patients-full-spec.md)

---

## 1. Summary

Beneficiaries submit a **public web** registration request (`GET/POST /register`). Staff (doctors/coordinators) review pending applications on **admin web** or **mobile API**. On **accept**, a `Patient` is created in a **campaign chosen by the reviewer**. On **reject**, a reason is stored and no patient is created.

Applications live in `beneficiary_applications` until accepted — they are **not** patients.

---

## 2. Form types (visible steps)

| `extra_type` | Label | Visible steps |
|--------------|-------|---------------|
| `8` | Cochlear implant | basic, extra, health, attachments |
| `9` | Other hearing issues | basic, health, attachments |

Config: `config/beneficiary_registration.php`

**Required core fields:** name (Arabic 3+ parts), national_number (10 digits), gender, mobile (`05xxxxxxxx`), country, birth_date, marital_status, health_status, national ID images, family card images.

**Type 8 extra:** case description, medical reports files, email.

---

## 3. Public web (no auth)

| Method | Path | Name |
|--------|------|------|
| GET | `/register` | `register` |
| POST | `/register` | `register.store` (throttled) |
| GET | `/register/thanks/{uuid}` | `register.thanks` |

Throttle: `beneficiary_registration.throttle` (default 5 / 60 minutes per IP on POST).

---

## 4. Staff web

| Method | Path | Permission |
|--------|------|------------|
| GET | `/beneficiary-applications` | `beneficiary_application.view` |
| GET | `/beneficiary-applications/{id}` | `beneficiary_application.view` |
| POST | `/beneficiary-applications/{id}/accept` | `beneficiary_application.review` body: `campaign_id` |
| POST | `/beneficiary-applications/{id}/reject` | `beneficiary_application.review` body: `rejection_reason` |
| GET | `/beneficiary-applications/{id}/attachments/{attachment}/download` | `beneficiary_application.view` |

---

## 5. Mobile API (Sanctum)

Base: `/api/v1/beneficiary-applications`

| Method | Path | Permission |
|--------|------|------------|
| GET | `/meta` | view |
| GET | `/` | view — query: `search`, `status`, `extra_type`, `per_page` |
| GET | `/{id}` | view — includes `application_payload` + attachment download URLs |
| POST | `/{id}/accept` | review — `{ "campaign_id": 1 }` |
| POST | `/{id}/reject` | review — `{ "rejection_reason": "…" }` |
| GET | `/{id}/attachments/{attachmentId}/download` | view |

### 5.1 Meta response (`GET …/meta`)

Returns `extra_types`, genders, countries, marital_status, health/disease options, statuses, and **`campaigns`** available to the current reviewer.

### 5.2 Accept response

```json
{
  "message": "…",
  "data": {
    "application": { "id": 1, "status": { "code": "accepted" }, … },
    "patient": { "id": 20, "patient_name": "…", "campaign_id": 1, … }
  }
}
```

Patient mapping: name, DOB, gender, mobile → patient columns; national ID / health / case description → `screening_data` keys from config; attachments copied to patient attachments; photo copied when present.

---

## 6. Permissions & roles

| Permission | Roles |
|------------|-------|
| `beneficiary_application.view` | doctor, coordinator, manager, super_admin |
| `beneficiary_application.review` | doctor, coordinator, manager, super_admin |

Doctors can review/accept **without** general `patient.create`.

---

## 7. Push notifications

| Type | When |
|------|------|
| `beneficiary_application_submitted` | New public submission → users with `beneficiary_application.review` |
| `beneficiary_application_accepted` | After accept → campaign staff |

---

## 8. Mobile checklist

- [ ] List pending applications; badge counts from list meta/total filters
- [ ] Show full payload + download attachments
- [ ] Accept: load campaigns from `/meta`, require `campaign_id`
- [ ] Reject: require non-empty reason
- [ ] After accept, open created patient if needed

---

## 9. Source files

| Piece | Path |
|-------|------|
| Config | `config/beneficiary_registration.php` |
| Service | `app/Services/BeneficiaryApplicationService.php` |
| Public UI | `resources/views/pages/beneficiary-registration/*` |
| Staff UI | `resources/views/pages/beneficiary-applications/*` |
| API | `app/Http/Controllers/Api/V1/BeneficiaryApplicationController.php` |

**End of beneficiary applications specification.**
