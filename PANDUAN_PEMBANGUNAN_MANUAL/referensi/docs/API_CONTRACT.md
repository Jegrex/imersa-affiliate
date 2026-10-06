# Application / Route Contract — TASK-007

**Status:** APPROVED — TASK-007 Application / Route Contract.

## 1. Contract conventions

This contract follows `AGENTS.md`, all approved Sprint 00 source-of-truth documents, `DATABASE.md`, `ARCHITECTURE.md`, and `CURRENT_SPRINT.md`. Controllers are thin: bind/validate, authorize, delegate to Actions/Services, then return Blade/redirect/controlled error. They do not own business rules, matching, approval, or transactions.

All admin writes require dedicated Admin authentication, active-account middleware, `role = 'admin'`, domain policy, and CSRF. The public affiliate CTA is also a CSRF-protected POST form, although its Visitor needs no authentication. Successful writes use PRG with flash success. Validation redirects back with safe old input; 403 is policy denial; 404 is absent/unavailable; 5xx never exposes exceptions/raw payload.

The MVP domain is electronics-only: public catalog results, product details,
category filters, and admin publication workflows are limited to approved
electronic products. Existing route names and affiliate behavior remain stable.

## 2. Public routes

| Route | Actor/auth | Input/validation | Controller → owner; DB/transaction | Success/failure |
| --- | --- | --- | --- | --- |
| `home` — `GET /` | Visitor; no auth | None | `Public\HomeController` → `CatalogService`; selected active reads/no transaction | Home Blade; empty selection valid. |
| `catalog.index` — `GET /catalog` | Visitor; no auth | `q`, `category`, `page` per §3 | `Public\CatalogController` → `CatalogQueryService`; indexed paginated read | Electronics catalog Blade or 200 empty result. |
| `products.show` — `GET /products/{slug}` | Visitor; no auth | Server-side slug | `Public\ProductController` → `ProductDetailService`; read-only | Detail Blade; missing/inactive/deleted is 404. |
| `affiliate.redirect` — `POST /go/{product}` | Visitor; no auth; CSRF | Internal product identity only; no target URL | `Affiliate\RedirectController` → `RecordAffiliateClickAndRedirectAction`; §5 transaction | Redirect only after commit; otherwise controlled no-redirect error. |

No public authentication routes/domain exist.

## 3. Search and filter contract

### ACCEPTED final MVP searchable fields

Search only normalized electronics `products.name`, case-insensitive under database collation. `products.description_text` is not searched in MVP. Never search `import_records.raw_payload`, `KEYWORD1..3`, legacy `SECTION*`, or legacy SEO/landing-page fields.

| Parameter | Validation | Behavior |
| --- | --- | --- |
| `q` | Optional trimmed string, 1–100 chars when supplied | Partial match on `products.name` only. |
| `category` | Optional slug/max 255; resolves active public category/subcategory | Filters only; never infers a category. |
| `page` | Optional positive integer | Standard pagination preserving valid filters. |

Page size is **24**. Valid no-match queries return 200 empty state. `KEYWORD1`, `KEYWORD2`, and `KEYWORD3` remain raw-only and cannot silently become public search fields.

## 4. Product detail contract

`products.show` resolves an active electronics product where `is_active = true` and `deleted_at IS NULL`, subject to the ADR-009 active, non-deleted Category invariant. It eager-loads category, ordered images, specifications, visible reviews/review images, and exposes only approved display data. Nullable price/currency, aggregate rating/count, review rating/date/photos, and video are omitted or marked unavailable. Category is nullable for drafts; missing/ineligible Category is not an authorized active publication state. Media uses fallback/omission. Raw imports/payload, validation/match state, internal notes, and unapproved URLs are never public. The view receives CTA eligibility state, not raw URL.

## 5. Affiliate redirect contract

Public input is only `{product}`; the Blade CTA submits a CSRF-protected POST form. Target URL from request input or legacy HTML is never accepted. `RecordAffiliateClickAndRedirectAction` owns:

```text
request /go/{product}
→ preliminary eligibility check
→ BEGIN TRANSACTION
→ reload/lock product
→ final CTA eligibility validation
→ copy final affiliate_url to target_url_snapshot
→ insert affiliate_clicks
→ COMMIT
→ redirect using exactly target_url_snapshot
```

Final eligibility requires active/non-deleted product, valid URL, `affiliate_url_status = approved`, provenance, approving Admin, and approval time. **Invariant: `target_url_snapshot` MUST equal the actual redirect URL.** HTTP redirect is outside the transaction.

| State | Controlled response |
| --- | --- |
| Missing product | 404; no redirect. |
| Inactive/deleted | Unavailable response; no redirect. |
| Missing/pending/rejected/invalid URL or provenance | CTA unavailable; no redirect. |
| Final validation, insert, or commit fails | `ROLLBACK` → controlled error → **DO NOT REDIRECT**. |
| Success | Redirect using exact committed snapshot. |

## 6. Admin authentication contracts

`Admin` is backed by `admins`. Laravel uses a dedicated admin guard/provider (or equivalent explicit configuration). `/admin` middleware is bound to that guard. Default Laravel user scaffolding must not silently add a public `users` table, provider, guard, or authentication domain. `admins.is_active` is enforced at login and before every management action. If an authenticated Admin becomes inactive, the application invalidates/rejects that session, permits the necessary logout/session cleanup, then sends the Admin to `admin.login` with a generic account-unavailable message.

| Route | Input/validation | Controller → owner; DB/transaction | Success/failure |
| --- | --- | --- | --- |
| `admin.login` — `GET /admin/login` | None | `Admin\AuthController`; guest read | Login Blade; active authenticated Admin goes dashboard. |
| `admin.login.store` — `POST /admin/login` | Email required/email/max 255; password required/string; CSRF | Controller → dedicated Admin guard; regenerate session after success | Redirect safe intended admin path or dashboard; generic invalid/inactive error prevents enumeration. |
| `admin.logout` — `POST /admin/logout` | CSRF; authenticated Admin session context, including an account that has become inactive | `Admin\AuthController`; dedicated Admin guard logout, invalidate/regenerate session; does not require `admins.is_active = true` | Redirect `admin.login` with generic account-unavailable/logout message; no account-status detail. |

Only Admin with `role = 'admin'` is required. Domain policies are boundaries, not extra business roles. Inactive Admins cannot access management routes, but their authenticated session may always be cleaned up through `admin.logout`. No public-user authentication is created.

## 7. Admin dashboard contract

`admin.dashboard` — `GET /admin/dashboard`: active Admin, dashboard policy, no input; `Admin\DashboardController` → `DashboardMetricsService`; read-only/no transaction. The Blade response includes product count, category count, total affiliate clicks, popular products, and counts with/without review video. Empty/zero metrics are valid.

## 8. Product management contracts

All routes require active Admin, products policy, and CSRF for writes. Generic editing cannot set approved affiliate status, approver, or approval timestamp.

| Route | Input/validation | Controller → owner; DB/transaction | Success/failure |
| --- | --- | --- | --- |
| `admin.products.index` — `GET /admin/products` | Optional bounded page/status/category | `Admin\ProductController` → query service; read-only | Index Blade/empty state. |
| `admin.products.create` — `GET /admin/products/create` | None | Controller reads non-deleted categories with active/inactive status; inactive choices are draft-only | Create Blade. |
| `admin.products.store` — `POST /admin/products` | Fields below | Controller → `CreateProductAction`; product/images/specs atomic | PRG/edit or validation/DB error. |
| `admin.products.edit` — `GET /admin/products/{product}/edit` | Binding | Controller reads permitted relations and Category status; inactive choices are draft-only | Edit Blade/404. |
| `admin.products.update` — `PUT /admin/products/{product}` | Fields below | Controller → `UpdateProductAction`; multi-table atomic | PRG/controlled failure. |
| `admin.products.destroy` — `DELETE /admin/products/{product}` | Binding | `DeactivateProductAction`; soft-delete/deactivate only | PRG; audit history retained/RESTRICT. |

Fields: required `name`/max 255; unique `slug`/max 255; `category_id` and request-only `electronics_scope_confirmed` per §8.1; nullable label/store name; nullable validated `store_url`; sanitized nullable description (server derives text); nullable nonnegative price with currency only when price exists; nullable `shopee_rating` aggregate 0.00–5.00 and `shopee_review_count` >=0; nullable validated video; `is_active` boolean. `products.marketplace` remains in the database for future extensibility but is server-controlled as `'shopee'` for MVP and is not an arbitrary editable admin form field. Images are external validated URLs/order/alt text; specifications are name/value/order. Existing import provenance is never rewritten here; aggregate values never derive from sample reviews. No hard-coded electronics taxonomy is introduced by this contract.

### 8.1 Manual activation, confirmation, and failure contract — ADR-009

Every manual create/update with a resulting `is_active = true` requires both:

- `category_id`: required valid identifier for an existing, non-soft-deleted
  Category with `is_active = true` in the Admin-managed electronics taxonomy;
- `electronics_scope_confirmed`: required explicit accepted confirmation from
  the authenticated Admin, validated server-side (Laravel `accepted` semantics).
  It is a request-only field, not a Product attribute or persisted audit record.

This applies to active creation, inactive-to-active transitions, and updates that
keep a Product active. If omitted `is_active` preserves an existing active state,
confirmation is still required. No confirmation is inferred from stored Product
state, its name, keywords, selected Category alone, or AI/classifier output.
Admin remains responsible for scope; no hard-coded whitelist or Shopee scraping.

For explicitly inactive/draft intent (`is_active = false`), confirmation is not
required and `category_id` may be null. A supplied `category_id` must be an existing,
non-soft-deleted Category; it may be inactive. Draft/inactive references may remain
on inactive Categories, but activation is refused until an active eligible Category
is explicitly selected.

| Requested operation | Required result |
| --- | --- |
| Save valid draft/inactive input | Save inactive; no electronics confirmation required. |
| Create active, activate inactive, or update while remaining active | Validate eligible Category and fresh request confirmation, then save atomically with other valid input. |
| Activation eligibility/confirmation fails | Controlled validation error; no Product/media/specification partial write, no automatic `is_active = false` fallback. Failed create creates nothing; failed update retains all pre-request data. |
| Save draft after failed activation | Admin explicitly selects draft and submits again with `is_active = false`; ordinary validation still applies. |
| Ordinary input/policy/database failure | No partial write; retain existing data and return the applicable controlled failure. |

Validation follows existing Blade/PRG behavior with field errors and safe old input.
The form presents explicit draft/active intent and a confirmation checkbox that
is not prechecked or inferred from prior confirmation. It clearly distinguishes
activation rejection from successful draft saving; preserving form input is not a
database save. An active Product moved to another Category must also be confirmed.

Product Actions recheck Category eligibility as part of the atomic write. Product
activation and Category inactivation must coordinate final checks through commit
under concurrent requests; a stale form/earlier existence check is insufficient.

No persistent electronics confirmation audit trail is introduced. Do not add
`is_electronic`, `product_domain`, electronics enum,
`confirmed_electronics_by_admin_id`, `confirmed_electronics_at`, or an audit table
without a new Product Owner decision. Authentication and normal created/updated
metadata are not confirmation history. Generic Product editing still cannot
approve affiliate URLs. Manual drafts/CRUD cannot normalize imported rows or
bypass the unresolved imported-row eligibility gate in §11.

## 9. Affiliate approval contract

`admin.products.affiliate-approval.store` — `POST /admin/products/{product}/affiliate-approval`: active Admin, CSRF, explicit `approveAffiliate` policy; `action` required `approve`/`reject`; URL required/valid/max 2048 to approve; allowed provenance and source field max 64. `Admin\AffiliateApprovalController` → `ReviewAffiliateUrlAction` atomically applies the selected state. Success PRG to edit; validation/policy/database failure rolls back.

Approve sets `affiliate_url_status = approved`, validated `affiliate_url`, provenance/source field, `affiliate_url_approved_by_admin_id = current Admin`, and `affiliate_url_approved_at = current timestamp`. Reject sets `affiliate_url_status = rejected`, `affiliate_url_approved_by_admin_id = NULL`, and `affiliate_url_approved_at = NULL`; it has no CTA eligibility. Imported `LINKORDER` is canonical CSV source, initially `import_candidate`, and requires authenticated approval. Generic product edit never approves it. The current schema has no `rejected_by`, `rejected_at`, or `rejection_reason`; none are invented here.

## 10. Category and review management contracts

| Route | Input/validation | Controller → owner; DB/transaction | Success/failure |
| --- | --- | --- | --- |
| `admin.categories.index` — `GET /admin/categories` | Optional page/status | `Admin\CategoryController`; read-only | Index Blade. |
| `admin.categories.store` — `POST /admin/categories` | Nullable parent; required name/unique slug; nullable description; active bool; sort >=0 | `CreateCategoryAction`; transaction | PRG/validation failure. |
| `admin.categories.update` — `PUT /admin/categories/{category}` | Same; no self/cycle; ADR-009 inactivation guard | `UpdateCategoryAction`; transaction | PRG; controlled validation error and no partial write if active Products block inactivation. |
| `admin.categories.destroy` — `DELETE /admin/categories/{category}` | Binding | `DeactivateCategoryAction`; administrative deactivate/approved soft-delete only | PRG or controlled reference conflict. |
| `admin.reviews.index` — `GET /admin/products/{product}/reviews` | Binding/page | `Admin\ProductReviewController`; read-only | Review management Blade. |
| `admin.reviews.store` — `POST /admin/products/{product}/reviews` | Required approved provenance; nullable reviewer/avatar/content, individual `rating_value` 1–5 when present, date/image URLs/visible | `CreateProductReviewAction`; review/images atomic | PRG/validation-policy failure. |
| `admin.reviews.update` — `PUT /admin/reviews/{review}` | Same; provenance required | `UpdateProductReviewAction`; atomic | PRG/controlled failure. |
| `admin.reviews.destroy` — `DELETE /admin/reviews/{review}` | Binding | `HideProductReviewAction`; set `is_visible = false`, retain normalized review/provenance | PRG/controlled failure. |

These routes require active Admin, CSRF writes, and category/review policy. Category DELETE does not automatically reassign children/products and does not physically delete while references exist; it preserves `RESTRICT` integrity/history. A later reassignment workflow requires an explicit Admin-selected destination. For the MVP, category and subcategory administration is electronics taxonomy only, without a fixed whitelist or seed requirement. Review DELETE is a hide operation because `product_reviews` has `is_visible` and no `deleted_at`; it never physically deletes normalized review/import provenance. No category inference, public review submissions, or Shopee scraping. Review provenance is required; reviewer/rating/date/images remain nullable.

**Category status versus destroy:** An active-to-inactive Category update must
refuse the entire request when any referencing Product has `is_active = true`
and `deleted_at IS NULL`. Admin must first deactivate those Products or explicitly
move them to another active eligible Category through §8.1. No cascade Product
deactivation, automatic reassignment, or automatic Category selection is allowed.
Draft/inactive Product references do not block an ordinary status transition and
may remain attached. DELETE/soft-delete retains its existing stricter
controlled-reference contract: child/Product references must be handled explicitly;
it is not a shortcut around the status guard. The new update guard is a future
implementation requirement, not a claim that TASK-012 already implemented ADR-009.

## 11. Import contracts

| Route | Input/validation | Controller → owner; DB/transaction | Success/failure |
| --- | --- | --- | --- |
| `admin.imports.index` — `GET /admin/imports` | Optional page/status | `Admin\ImportController`; read-only | Batch list Blade. |
| `admin.imports.show` — `GET /admin/imports/{batch}` | Batch binding/optional status filters | Authorized immutable batch/records read | Review Blade/404; never public raw payload. |
| `admin.imports.store` — `POST /admin/imports` | Client supplies approved CSV file + source metadata; validate file/size/encoding and approved 54-column mentor header set | `RegisterImportBatchAction`; calculate authoritative SHA-256 server-side from uploaded bytes, then register batch/raw rows/validation state atomically | PRG; duplicate checksum conflict; malformed/unsupported schema gets controlled validation result and no normalization. |
| `admin.imports.records.match` — `POST /admin/imports/{batch}/records/{record}/match` | `new`/`link_existing`/`reject`; product only manual link; ambiguity notes | `ResolveImportMatchAction`; match/link atomic | PRG; never name-only merge. |
| `admin.imports.records.normalize` — `POST /admin/imports/{batch}/records/{record}/normalize` | Valid record plus new/approved match and confirmation | `NormalizeImportRecordAction`; normalized writes/provenance atomic | PRG; invalid/conflict/rejected refused/raw unchanged. |

Active Admin, CSRF, and imports policy are required. The client checksum is never trusted: authoritative SHA-256 comes only from server-side uploaded-file bytes. Before normalization, validate the approved mentor CSV schema/header set corresponding to the current 54-column source and establish electronics-scope eligibility through a future approved review process. Unknown columns are never silently remapped. Non-electronics rows remain immutable raw evidence and are not normalized or published. The exact scope-rejection representation is OPEN and must be resolved before import normalization implementation. For malformed/unsupported schema, do not normalize; return controlled import validation and preserve source only according to approved import-registration rules. Stages: file acceptance → checksum registration → batch creation → immutable `import_records` → validation → matching → conflict/manual review → normalization approval → `product_import_links`. `validation_status` and `match_status` stay independent; conflicts never normalize automatically.

## 12. Analytics, validation, and authorization

`admin.analytics.index` — `GET /admin/analytics`: active Admin/analytics policy; optional ISO `from`/`to`, valid order, maximum **366 days**; `Admin\AnalyticsController` → `AnalyticsQueryService`; indexed read-only/no transaction. Blade outputs total clicks, clicks/product, popular products, and products with/without review video. No-data is valid; Excel/PDF export is not introduced.

URLs validate server-side/max 2048, wajib HTTPS dan purpose-specific approved host
sesuai §12.1 / ADR-010. Names/slugs max 255; price nullable/nonnegative with conditional currency; `products.shopee_rating` is a nullable approved aggregate in the range 0.00–5.00 and `shopee_review_count` is nonnegative; `product_reviews.rating_value` is a nullable individual review rating in the range 1–5 when present; Product category/confirmation follow §8.1 and Category hierarchy/lifecycle follow §10; affiliate status changes only in §9; video nullable/validated; review provenance required but reviewer/date/images nullable; pagination positive/bounded.

Only Admin `role = 'admin'` exists. Policies protect products, categories, reviews, imports, analytics, and separate affiliate approval; these are domain boundaries, not extra roles.

### 12.1 External URL dan description input — ADR-010

| Field/purpose | Required policy |
| --- | --- |
| `products.store_url`, `products.affiliate_url` | Marketplace/affiliate/store allowlist; Shopee-first, approved marketplace hosts. Affiliate URL hanya melalui separate §9 approval workflow. |
| `product_images.image_url`, `product_reviews.avatar_url`, `review_images.image_url` | Product/review/avatar image allowlist; approved media providers. Existing image URL ASCII/index constraints tetap berlaku. |
| `products.review_video_url` | Review video/media allowlist; approved media providers. URL optional, bukan arbitrary embed HTML. |
| Optional description `a[href]` | Validator HTTPS/address yang sama dan explicit approved purpose mapping. Tidak otomatis memakai union semua allowlists; tanpa mapping/configuration, link tidak boleh menjadi navigasi aktif. |

Semua supplied URL baru, termasuk pada draft, wajib valid HTTPS. Batas 2048
diterapkan terhadap input URL dan hasil normalisasi ASCII; percent-encoding atau
normalisasi IDN tidak boleh menghasilkan nilai tersimpan lebih dari 2048 byte.
Tolak HTTP/scheme lain, localhost, loopback, private/internal network, termasuk
IPv4/IPv6 dan hostname dengan resolved address terlarang. Parse/normalize lalu
bandingkan exact approved host, bukan substring matching. Subdomain harus approved
eksplisit; userinfo, encoding, atau URL ambigu tidak boleh menjadi bypass.
Validation/resolution failure menghasilkan controlled field error, tanpa partial
write atau automatic draft fallback. Nullable URL tetap boleh kosong sesuai field.

Host lists berada di application configuration terpisah per purpose, bukan schema.
Missing/empty config menolak URL; tidak ada fallback arbitrary public host.
Exact production values wajib dari PT Imersa sebelum production use. Dokumentasi
example/test configuration tidak mengapprove provider nyata. Tidak ada media fetch,
redirect crawling, scraping, proxy/upload, atau silent conversion HTTP lama.

`description_html` merupakan untrusted input: server sanitizer berbasis HTML parser
menerapkan semantic tags/unsafe content policy ADR-010 dan Architecture §12 sebelum
storage/trusted rendering. Input description dibatasi 100000 byte UTF-8 dan input
yang melampaui batas ditolak terkontrol sebelum sanitization, bukan di-truncate.
Tidak ada regex sanitizer sendiri atau trusted flag dari
client. `description_text` derived server-side dari sanitized/normalized description;
client tidak dapat mengganti derived representation. Text tersebut tidak searchable
untuk MVP; search hanya `products.name`. Optional links mengikuti table di atas.
Konten di luar allowlist dibuang/ditolak; sanitizer failure menolak seluruh save,
tanpa raw HTML fallback. Old input di Blade tetap escaped, termasuk saat error.

Raw imported HTML tetap immutable raw evidence sampai approved normalization.
Validasi URL dan sanitasi tidak memberikan affiliate approval. Generic edit tidak
mengubah approval/provenance atau menerima arbitrary redirect target. Security
contract ini tidak mengotorisasi review CRUD, affiliate workflow, import, atau
TASK-013 implementation.

## 13. Response, CSRF, and transaction ownership

404 handles absent/unavailable resources; 403 policy denial; unauthenticated admin requests redirect to `admin.login`; inactive Admin logs out/rejects; writes use flash success/error. Failed click persistence rolls back with controlled no-redirect error; import conflicts remain admin-review-only.

All state-changing admin forms require CSRF. Normal GET routes are read-only. `affiliate.redirect` is the intentional public POST side effect: the visitor submits a CSRF-protected CTA form that appends minimal click history only after final eligibility/transaction success. No target affiliate URL comes from request input.

| Use case | Transaction owner |
| --- | --- |
| Affiliate redirect/click | `RecordAffiliateClickAndRedirectAction`: lock, final validation, snapshot, insert, commit; redirect afterward. |
| Affiliate approval | `ReviewAffiliateUrlAction`: approve/reject state, URL/provenance/source/approver/time changes atomic. |
| Import normalization/provenance | Matching/normalization actions: normalized data plus provenance link all-or-nothing. |
| Product/images/specifications | Create/update actions: multi-table write atomic. |
| Product activation / Category inactivation | Respective Product/Category Actions coordinate eligibility/reference checks and writes through commit; validation failure saves nothing. |

## 14. Route naming registry

Public: `home`, `catalog.index`, `products.show`, `affiliate.redirect` (`POST /go/{product}`, CSRF-protected form).

Admin: `admin.login`, `admin.login.store`, `admin.logout`, `admin.dashboard`, `admin.products.index/create/store/edit/update/destroy`, `admin.products.affiliate-approval.store`, `admin.categories.index/store/update/destroy`, `admin.reviews.index/store/update/destroy`, `admin.imports.index/show/store`, `admin.imports.records.match`, `admin.imports.records.normalize`, `admin.analytics.index`.

## 15. Decisions and open questions

### ACCEPTED

- ADR-010 resolves URL/HTML security: HTTPS, purpose-specific configurable hosts,
  internal-target rejection, parser-based server sanitizer, dan derived internal
  plain text yang tidak searchable. §12.1 mengikat seluruh affected workflows.
- Blade-first routes/forms; anonymous public browsing; dedicated active Admin guard.
- `LINKORDER` approval, immutable provenance, no scraping/name-only merge, click snapshot-before-redirect invariant, and MySQL 8.x family.
- MySQL 8.4.3 Community Server / InnoDB has been verified for the approved
  Laravel database requirements during TASK-008/TASK-009.
- Public search uses `products.name` only; `products.description_text` and legacy/raw fields are excluded; catalog page size is 24; analytics maximum date range is 366 days.
- Public search uses `products.name` only within the electronics catalog; category filtering operates over approved electronics categories.
- The documented route names and Action/Service ownership are accepted for MVP; endpoints are not renamed or expanded without a new approved decision.
- ADR-009 resolves manual Product activation and Category lifecycle eligibility;
  §§8.1/10 define the aligned contract. Product Management/TASK-013 implementation
  has not started and still requires a separately authorized task contract.

### OPEN

- Exact production marketplace/image/video host entries dari PT Imersa sebelum
  production use; URL/HTML policy sudah ACCEPTED / RESOLVED oleh ADR-010 dan §12.1.
  Package/version sanitizer dan detail validator direview dalam authorized task;
  tidak ada izin arbitrary public host atau regex sanitizer.
- Deployment environment/hosting vendor; out-of-range catalog-page behavior.
- Electronics-scope eligibility process during import review is **OPEN — BLOCKER BEFORE IMPORT NORMALIZATION IMPLEMENTATION**; it is not a blocker for Category Management.

## 16. Implementation gate

TASK-007 is approved. TASK-008 follows and finalizes MySQL runtime compatibility. No Laravel project files, routes, controllers, requests, models, migrations, views, tests, middleware, importers, seeders, Actions, or Services are authorized by this contract alone; implementation remains blocked until the next approved implementation authorization.

This is the historical TASK-007 implementation gate. Subsequent approved task
authorizations (including TASK-008 through TASK-011) supersede the temporary
block for their explicitly approved scopes; it does not authorize TASK-012.
