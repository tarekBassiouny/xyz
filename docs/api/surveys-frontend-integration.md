# Surveys Frontend Integration (Backend-Aligned)

This document defines the survey behavior currently enforced by backend code and the required frontend flows to avoid invalid payloads.

## Scope and Roles

- `scope_type=1` (`System`):
  - Can be created/managed by `super_admin` only.
  - Targets only:
    - students in unbranded centers
    - students with `center_id = null`
- `scope_type=2` (`Center`):
  - Created/managed in a specific center context.
  - Targets only students/content within the selected center.

## Assignment Types

Supported enum values:
- `all`
- `center`
- `course`
- `video`
- `user`

Removed:
- `section` (frontend must not send this value)

### Allowed Matrix

- `System` surveys allow:
  - `all`
  - `center` (unbranded center only)
  - `course` (course must belong to an unbranded center)
  - `user` (student in unbranded center or `center_id = null`)
- `System` surveys reject:
  - `video`
  - `section`

- `Center` surveys allow:
  - `all`
  - `course` (must belong to survey center)
  - `video` (must belong to survey center)
  - `user` (student must belong to survey center)
- `Center` surveys reject:
  - `center`
  - `section`

## API Endpoints

Admin base: `/api/v1/admin`

- `GET /surveys`
- `POST /surveys`
- `GET /surveys/{survey}`
- `PUT /surveys/{survey}`
- `DELETE /surveys/{survey}`
- `POST /surveys/{survey}/assign`
- `POST /surveys/{survey}/close`
- `GET /surveys/{survey}/analytics`
- `GET /surveys/target-students` (new, for assignment user picker)

Mobile base: `/api/v1`

- `GET /surveys/assigned`
- `GET /surveys/{survey}`
- `POST /surveys/{survey}/submit`

## New Endpoint: Target Students

`GET /api/v1/admin/surveys/target-students`

Purpose:
- Fetch only students eligible for the selected survey scope before submitting assignments.

Required query params:
- `scope_type`:
  - `1` for system targeting
  - `2` for center targeting

Optional query params:
- `center_id`
  - required when `scope_type=2`
  - optional for `scope_type=1` (if sent, must be unbranded center)
- `status` (`0,1,2`)
- `search`
- `page`
- `per_page` (max `50`, recommended `20`)

Authorization rules:
- `scope_type=1`: super admin only
- `scope_type=2`: admins can query only centers they administrate

Response shape:
- `data[]` item:
  - `id`
  - `name`
  - `username`
  - `email`
  - `phone`
  - `center_id`
  - `center { id, name, slug }` (or null)
  - `status`
  - `status_key`
  - `status_label`
- `meta`: `page`, `per_page`, `total`, `last_page`

## Required Frontend Prefetch Flow

Use these endpoints before submit:

1. `GET /api/v1/admin/auth/me`
   - detect `super_admin` and available permissions.

2. For system survey assignment (`scope_type=1`):
   - centers: `GET /api/v1/admin/centers?type=0` (unbranded only)
   - students (user assignment): `GET /api/v1/admin/surveys/target-students?scope_type=1[&center_id=...]`
   - courses:
     - select unbranded center first
     - then `GET /api/v1/admin/centers/{center}/courses`

3. For center survey assignment (`scope_type=2`):
   - students: `GET /api/v1/admin/surveys/target-students?scope_type=2&center_id={centerId}`
   - courses: `GET /api/v1/admin/centers/{centerId}/courses`
   - videos: `GET /api/v1/admin/centers/{centerId}/videos`

## Survey Page Listing (Pagination / Infinite Scroll)

Use paginated loading for survey page lists. Do not request bulk pages like `per_page=100`.

- Endpoint: `GET /api/v1/admin/surveys`
- Query:
  - `page` (start at `1`)
  - `per_page` (recommended `20`, maximum `50`)
  - optional filters: `scope_type`, `center_id`, `is_active`, `type`
- Continue loading while:
  - `page <= meta.last_page`
  - or loaded count `< meta.total`

Suggested infinite scroll loop:
1. Initial request with `page=1&per_page=20`.
2. Append returned `data`.
3. On scroll threshold, request `page + 1`.
4. Stop when current page reaches `meta.last_page`.

## Create/Assign Payload Rules

Assignments payload item:
- `type` required
- `id` required except `type=all`

Valid:
- `{ "type": "all" }`
- `{ "type": "course", "id": 42 }`

Invalid:
- `{ "type": "all", "id": 42 }` (ignored by business intent, FE should avoid)
- `{ "type": "section", "id": 10 }`
- System + `{ "type": "video", "id": 10 }`
- Center + `{ "type": "center", "id": 10 }`

## Submission and Visibility Notes (Mobile)

- `GET /surveys/assigned` returns only one highest-priority survey.
- Priority order:
  - mandatory first
  - earliest deadline first
  - newest created first
- Video assignment eligibility requires playback with:
  - same `video_id`
  - `is_full_play=true`
  - course center matching student center
- Multiple submissions are still blocked by current response logic even if `allow_multiple_submissions=true`.

## Frontend Safety Checklist

- Never send `section` assignment type.
- Always coerce assignment IDs to integer.
- Do not render invalid type/scope combinations in UI.
- Always scope student picker by survey scope using `/surveys/target-students`.
- For system scope, do not allow branded center entities in selectors.
- For center scope, force center-bound lists (`students`, `courses`, `videos`) by selected center.
- Use paginated loading (`per_page=20`) for survey-page and target-student lists.
- Treat 422 as validation error and show field messages.
- Guard for possible 500 from invalid business combinations if client-side validation misses a case.
