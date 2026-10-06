# Business Rules

- BR-01 Website is an affiliate catalog, not a full transactional e-commerce application.
- BR-02 Initial marketplace is Shopee.
- BR-03 Purchase CTA uses the approved affiliate link rather than an ordinary product link.
- BR-04 Affiliate links are supplied by PT Imersa internal data/source.
- BR-05 Product media is displayed using external URLs/hotlinks.
- BR-06 Review video is optional.
- BR-07 A product may be displayed without a review video.
- BR-08 Displayed ratings/reviews must originate from Shopee-approved/source data.
- BR-09 Reviewer avatar/review photos may use external URLs when available.
- BR-10 Public users do not require an account.
- BR-11 Admin must authenticate before management operations.
- BR-12 Products use project category/subcategory grouping.
- BR-13 Every successful affiliate CTA should be tracked.
- BR-14 The transaction after redirect is outside this system and happens on Shopee.
- BR-15 `is_active` controls catalog visibility subject to the active Product eligibility invariant in BR-29. A visible product may have no purchase CTA.
- BR-16 A purchase CTA is eligible only for an active product with a valid affiliate URL, `affiliate_url_status = approved`, and complete approval/provenance fields.
- BR-17 `LINKORDER` is the canonical affiliate URL field in the current mentor CSV, imported as a candidate and requiring authenticated admin validation/approval before CTA eligibility.
- BR-18 Category/subcategory and missing price/review/video values require an approved mapping, approved source, or authenticated validated admin workflow; they must not be inferred or scraped from Shopee.
- BR-19 Aggregate Shopee rating/review count may not be calculated from the limited imported sample reviews.
- BR-20 Invalid or ambiguous imports remain immutable and flagged; they are never silently repaired, scraped, merged, or published. Identical product names do not authorize automatic merging.
- BR-21 Legacy SEO/landing-page fields remain raw import payload for MVP and are not normalized.
- BR-22 Affiliate click records contain only `product_id`, `clicked_at`, `target_url_snapshot`, and `created_at`; visitor IP address and user agent are out of MVP.
- BR-23 CTA flow: validate an active product and approved affiliate URL; begin a transaction; insert the affiliate click; commit; then redirect to Shopee. If click persistence or commit fails, roll back, return a controlled error, and do not redirect.

## Explicitly Out of MVP
- local checkout/payment
- cart
- payment gateway
- shipping/tracking/invoice/return workflows
- public user login/registration
- affiliate-account registration or affiliate-link generation
- product image/video upload to the application
- internal user rating/comment/moderation
- automatic Shopee API synchronization
- automatic Shopee scraping
- automatic marketplace price/stock update

## Electronics-only scope amendment

- **BR-24** The MVP public catalog contains electronic products only. Products
  outside the approved electronics domain must not be published.
- **BR-25** MVP categories and subcategories must represent electronic-product
  taxonomy. The category hierarchy remains flexible; example domains such as
  smartphones/tablets, computers/laptops, computer accessories, audio, gaming
  accessories, wearable electronics, cameras, networking, smart-home
  electronics, storage, and charging/power accessories are guidance only, not
  an immutable whitelist.
- **BR-26** Imported rows outside the electronics scope remain in immutable raw
  import data as source evidence. They must not be silently deleted, converted,
  normalized into the public catalog, or published. No new database status is
  introduced by this amendment; the representation of a scope rejection is an
  open decision for the future import task.
- **BR-27** Automated or AI/keyword domain classification may assist review only
  and is not authoritative without an approved rule. Category/domain inference
  must not silently determine publication.
- **BR-28** Expanding the MVP beyond electronics requires a new Product Owner
  scope decision.

## Manual Product activation and Category lifecycle — ADR-009

- **BR-29** An active Product must reference an existing, active,
  non-soft-deleted Category in the Admin-managed electronics taxonomy.
  Draft/inactive Products may have no category or reference an inactive Category;
  supplied references still require ordinary reference validation.
- **BR-30** Every manual create/update resulting in `is_active = true` requires
  explicit authenticated Admin electronics-scope confirmation validated server-side,
  including an update that keeps a Product active. Retaining active state through
  omitted input cannot bypass this requirement. Category selection alone, Product
  name, keywords, AI/classifiers, hard-coded whitelists, or Shopee scraping cannot
  establish authoritative electronics eligibility or replace confirmation.
- **BR-31** Activation eligibility failure rejects the request with a controlled
  validation error and no partial write. Never silently save a draft or change
  `is_active` to false. Saving valid data as draft requires an explicit new Admin
  submission with `is_active = false`; existing data stays unchanged on failure.
- **BR-32** Category active-to-inactive updates are blocked while any referencing
  Product has `is_active = true` and `deleted_at IS NULL`. Admin must first
  deactivate those Products or explicitly reassign them to another active eligible
  Category. No cascade deactivation, automatic reassignment, or automatic category selection.
  Draft/inactive references may remain on an inactive Category. Category
  soft-delete/destroy retains its existing controlled-reference restrictions and
  must not bypass this invariant.
- **BR-33** Electronics confirmation is request/workflow validation only and has
  no persistent audit trail. No electronics classification/confirmation columns,
  enum, or audit table are introduced. Existing authentication and created/updated
  metadata are not electronics-confirmation history.
- **BR-34** ADR-009 applies to manual Admin Product workflows only. Imported-row
  electronics eligibility remains OPEN — BLOCKER BEFORE IMPORT NORMALIZATION.
  Manual drafts or Product CRUD cannot bypass import review/normalization gates.

## URL dan HTML security — ADR-010

- **BR-35** External URL baru melalui Admin wajib HTTPS, valid server-side, dan
  tidak menarget localhost, loopback, private/internal network. Scheme lain ditolak;
  structured URL parsing/host/address checks wajib, bukan substring matching saja.
- **BR-36** Allowlist host wajib terpisah menurut purpose marketplace/affiliate/
  store, product/review/avatar images, dan review video/media. Hanya approved
  marketplace/media providers; Shopee-first. Configuration tanpa migration;
  missing/empty allowlist menolak URL. Jangan menebak host production atau memakai
  arbitrary public host sebagai fallback; examples hanya untuk isolated tests.
- **BR-37** Product HTML disanitasi server-side dengan testable Laravel/PHP
  sanitizer dan semantic allowlist ADR-010, bukan regex sanitizer sendiri. Buang/
  tolak dangerous elements, scriptable content, inline events, dan unsafe URLs.
  Optional `a`/`href` tetap wajib URL validation sesuai approved purpose.
- **BR-38** `description_html` hanya sanitized approved HTML; `description_text`
  derived server-side, plain text internal, bukan searchable field MVP. Raw HTML
  hanya immutable import evidence sampai normalization disetujui; tidak ada
  silent repair atau perubahan checksum/raw payload.
- **BR-39** URL/host validation tidak mengapprove affiliate URL. Generic Product
  CRUD tidak mengubah affiliate approval/provenance, menerima client-supplied
  trusted HTML/arbitrary redirect target, scrape Shopee, atau upload/proxy media.
  Draft tetap harus memenuhi validasi URL/HTML; ADR-009/import gates tetap berlaku.
