# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- Platform administrators manage the full service across every client organization.
- Clients operate their own business workspace and must only access their own organization's data.
- Client-side staff with narrower permissions may be added later; the permission model should allow this extension.

## Product Purpose

Startup:business management system gives small-business clients one workspace for onboarding, planning, accounting, payments, rewards, announcements, guides, accounts, usage, and support. Administrators operate and support the platform across all client organizations.

## Positioning

One role-aware workspace combines startup guidance with day-to-day business operations. Platform-level administration and organization-scoped client work use the same modules without mixing client data.

## Operating Context

Administrators configure platform rules, publish guides, monitor health, manage accounts, and answer support requests. Clients follow onboarding, build their business plan, manage business records, communicate with their own audiences, and submit support requests.

## Capabilities and Constraints

- Authentication has two primary roles: Administrator and Client.
- Every client-owned record is scoped to the authenticated client's organization.
- Administrators can configure permissions in User & Account Management.
- Setup Guide: administrators manage guide steps; clients follow visible onboarding steps.
- Business Plan Guide: administrators manage templates; clients complete their own plan.
- Announcements: administrators publish platform-wide notices; clients message only their own customers or staff.
- Accounting: administrators have system-wide read-only visibility; clients manage their own books.
- Payments: administrators configure platform options; clients choose options for their organization.
- Rewards: administrators set platform limits; clients manage their own programs.
- Content & Guides: administrators have full control; client access is read-only in v1. Client edit suggestions remain an open future decision.
- Accounts: administrators manage all accounts and permissions; clients manage their own profile and permitted sub-users when introduced.
- Usage & Performance: administrators see platform-wide data; clients see only their usage.
- Support: administrators manage all tickets; clients submit and view only their tickets.
- Frontend uses HTML, CSS, and JavaScript. Backend uses PHP and MySQL in XAMPP.

## Brand Commitments

- Product name: Startup:business management system.
- Interface language should be plain, professional, and easy to understand.

## Evidence on Hand

- Existing working PHP/MySQL application and interface in this project.
- User-supplied role-access matrix in the task conversation.
- No testimonials, customer claims, or public performance evidence should be fabricated.

## Product Principles

- Keep client data isolated by organization at every layer.
- Make permissions explicit and administrator-controlled.
- Use plain language and reveal only actions available to the current role.
- Keep platform administration and client operations consistent without making them identical.
- Design the permission model so limited client staff roles can be added later.
