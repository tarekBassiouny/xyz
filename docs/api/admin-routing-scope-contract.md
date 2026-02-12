# Admin Routing Scope Contract

This document defines the agreed admin API routing model for Najaah LMS.

## Core Scope Rule

- `admin.center_id = null` means system scope (system admin context).
- `admin.center_id != null` means center scope (center admin context).

## Design Principles

- No duplicated endpoint patterns for the same data ownership model.
- No mixed contract such as both:
  - `/api/v1/admin/courses?center_id=...`
  - `/api/v1/admin/centers/{center}/courses`
- Center-owned data must be routed through center paths.
- System-only data must be routed through system paths.
- Default rule: if an endpoint is not explicitly global/system-admin, it must be center-scoped under `/api/v1/admin/centers/{center}/...`.

## Route Ownership Model

1. Global Admin Resources

- Prefix: `/api/v1/admin/...`
- Purpose: platform-level resources, not tenant-owned records.
- Examples:
  - `/api/v1/admin/auth/*`
  - `/api/v1/admin/centers` (registry/management)
  - `/api/v1/admin/roles`
  - `/api/v1/admin/permissions`

2. Center-Owned Resources

- Prefix: `/api/v1/admin/centers/{center}/...`
- Purpose: any entity owned by a specific center.
- Examples:
  - courses, videos, categories, sections, PDFs
  - students (center students)
  - enrollments
  - extra-view requests
  - device-change requests (center students)

3. System-Admin Aggregation Resources

- Prefix: `/api/v1/admin/...` (system admin only)
- Purpose: cross-center list/filter views used by the Najaah App sidebar.
- Notes:
  - These are aggregation entry points for system admin UX.
  - Mutations should still target canonical center ownership paths where applicable.
  - This is the only exception to the center-path default.

## Access Enforcement Rules

- System admin (`center_id = null`):
  - Can access any `/centers/{center}/...` path.
- Center admin (`center_id = X`):
  - Can access only `/centers/X/...`.
  - Any other center path must return `403 CENTER_MISMATCH`.

## Canonical Endpoint Examples

### Courses, Videos, Categories

```http
GET    /api/v1/admin/centers/{center}/courses
POST   /api/v1/admin/centers/{center}/courses
GET    /api/v1/admin/centers/{center}/videos
POST   /api/v1/admin/centers/{center}/videos
GET    /api/v1/admin/centers/{center}/categories
POST   /api/v1/admin/centers/{center}/categories
```

### Students

```http
GET    /api/v1/admin/centers/{center}/students
POST   /api/v1/admin/centers/{center}/students
GET    /api/v1/admin/centers/{center}/students/{student}
PUT    /api/v1/admin/centers/{center}/students/{student}
DELETE /api/v1/admin/centers/{center}/students/{student}
```

System-admin aggregation list:

```http
GET /api/v1/admin/students
```

- System admin: list/filter all students across branded, unbranded, and system (`center_id = null`).
- Center admin: either disabled or implicitly scoped to their center by policy.

### Enrollments

```http
GET    /api/v1/admin/centers/{center}/enrollments
POST   /api/v1/admin/centers/{center}/enrollments
POST   /api/v1/admin/centers/{center}/enrollments/bulk
GET    /api/v1/admin/centers/{center}/enrollments/{enrollment}
PUT    /api/v1/admin/centers/{center}/enrollments/{enrollment}
DELETE /api/v1/admin/centers/{center}/enrollments/{enrollment}
```

### Extra View Requests

```http
GET  /api/v1/admin/centers/{center}/extra-view-requests
POST /api/v1/admin/centers/{center}/extra-view-requests/{request}/approve
POST /api/v1/admin/centers/{center}/extra-view-requests/{request}/reject
```

### Device Change Requests

Center-owned:

```http
GET  /api/v1/admin/centers/{center}/device-change-requests
POST /api/v1/admin/centers/{center}/device-change-requests/{request}/approve
POST /api/v1/admin/centers/{center}/device-change-requests/{request}/reject
POST /api/v1/admin/centers/{center}/device-change-requests/{request}/pre-approve
```

System-admin aggregation list (optional, read-only entry point):

```http
GET /api/v1/admin/device-change-requests
```

## Frontend Contract

- Frontend reads admin scope from `/api/v1/admin/auth/me`:
  - `scope_type: "system" | "center"`
  - `scope_center_id: number | null`
- Center scope UI:
  - no center selector
  - requests use fixed `/centers/{scope_center_id}/...`
- System scope UI:
  - can choose center for center-owned modules
  - uses global aggregation endpoints for cross-center views

## Najaah App Sidebar Mapping (System Admin)

This maps the system-admin sidebar to endpoint ownership:

1. Dashboard
- Ownership: global aggregation
- Endpoint family: `/api/v1/admin/analytics/*`

2. Analysis
- Ownership: global aggregation with center/type filters
- Endpoint family: `/api/v1/admin/analytics/*`

3. Centers (CRUD)
- Ownership: global
- Endpoint family: `/api/v1/admin/centers*`

4. Surveys (CRUD)
- Ownership: global (includes center-scoped and system-scoped surveys)
- Endpoint family: `/api/v1/admin/surveys*`
- Filters: `scope_type`, `center_id`

5. Agents
- Ownership: global orchestration
- Endpoint family: `/api/v1/admin/agents/*`

6. Roles & Permissions
- Ownership: global
- Endpoint family: `/api/v1/admin/roles*`, `/api/v1/admin/permissions`

7. Admins (CRUD)
- Ownership: global with center affiliation
- Endpoint family: `/api/v1/admin/users*`

8. Students (CRUD)
- Ownership: center-scoped operations, with system-admin entry via global aggregation list
- Endpoint family:
  - list/filter: `/api/v1/admin/students`
  - CRUD: `/api/v1/admin/centers/{center}/students*`

9. Settings (platform)
- Ownership: global
- Endpoint family: `/api/v1/admin/settings/*`

10. Audit Log
- Ownership: global aggregation
- Endpoint family: `/api/v1/admin/audit-logs`

## Migration Notes

- Canonical routes should be introduced first.
- Legacy mixed routes can be kept temporarily as compatibility aliases.
- Compatibility aliases should return deprecation metadata and be removed after frontend migration.
- During migration, any non-sidebar operational endpoint should be moved to center paths and removed from flat `/api/v1/admin/{resource}` forms.
