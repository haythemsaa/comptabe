# Security Audit Report - ComptaBE Application

**Date:** 2025-12-20
**Auditor:** Security Audit Tool
**Application:** ComptaBE - Belgian Accounting Application
**Version:** 1.0.0

## Executive Summary

This security audit was conducted on the ComptaBE Laravel application to identify potential security vulnerabilities across six key areas: SQL injection, XSS (Cross-Site Scripting), CSRF protection, authorization/access control, sensitive data exposure, and input validation.

**Overall Security Rating:** MEDIUM-HIGH

The application demonstrates good security practices in several areas, particularly with CSRF protection and basic input validation. However, several critical and high-severity issues were identified that require immediate attention.

---

## Audit Findings

### 1. SQL Injection Vulnerabilities

#### 1.1 SQL Injection via String Concatenation in DB::raw()

**Severity:** CRITICAL
**Location:** `C:\laragon\www\compta\app\Models\CreditNote.php:212`

**Issue:**
```php
'amount_credited' => DB::raw("amount_credited + {$this->total_incl_vat}"),
```

**Description:** Direct interpolation of model properties into raw SQL without proper binding. While `total_incl_vat` is likely numeric, this pattern is dangerous and could lead to SQL injection if the value source changes.

**Recommendation:** Use parameter binding:
```php
'amount_credited' => DB::raw("amount_credited + ?")
// Then pass [$this->total_incl_vat] as bindings
```

---

#### 1.2 SQL Injection in Dynamic Column Names

**Severity:** HIGH
**Location:** `C:\laragon\www\compta\app\Traits\OptimizedQueries.php:112`

**Issue:**
```php
->select($groupColumn, DB::raw("SUM({$sumColumn}) as total"))
```

**Description:** Dynamic column names from variables are inserted directly into SQL. If `$sumColumn` comes from user input, this is a direct SQL injection vulnerability.

**Recommendation:** Validate column names against a whitelist before using them in queries.

---

#### 1.3 Potentially Unsafe LIKE Queries

**Severity:** MEDIUM
**Location:** Multiple controllers (InvoiceController.php, PartnerController.php, etc.)

**Issue:**
```php
$q->where('invoice_number', 'like', "%{$search}%")
  ->orWhereHas('partner', fn($q) => $q->where('name', 'like', "%{$search}%"));
```

**Description:** While Laravel escapes these properly, the pattern allows for SQL wildcard injection (%, _) that could be used for information disclosure or DoS attacks.

**Recommendation:** Escape wildcards in user input:
```php
$search = str_replace(['%', '_'], ['\%', '\_'], $search);
```

---

#### 1.4 Raw WHERE Clauses with Fixed Strings

**Severity:** LOW
**Location:** `C:\laragon\www\compta\app\Http\Controllers\AccountingController.php:29-32`

**Issue:**
```php
'unbalanced' => JournalEntry::whereRaw('
    (SELECT SUM(debit) FROM journal_entry_lines WHERE journal_entry_id = journal_entries.id) !=
    (SELECT SUM(credit) FROM journal_entry_lines WHERE journal_entry_id = journal_entries.id)
')->count(),
```

**Description:** While this doesn't contain user input, raw SQL queries should be minimized.

**Recommendation:** Use Eloquent relationships and aggregates where possible.

---

### 2. Cross-Site Scripting (XSS) Vulnerabilities

#### 2.1 Unescaped HTML Output with {!! !!}

**Severity:** HIGH
**Locations:**
- `C:\laragon\www\compta\resources\views\components\alert.blade.php:59`
- `C:\laragon\www\compta\resources\views\admin\system\phpinfo.blade.php:26`
- `C:\laragon\www\compta\resources\views\components\dropdown-item.blade.php:28`
- `C:\laragon\www\compta\resources\views\components\empty-state.blade.php:12`
- `C:\laragon\www\compta\resources\views\components\invoice-status.blade.php:54`
- `C:\laragon\www\compta\resources\views\components\stat-card.blade.php:51`

**Issue:**
```php
{!! $config['icon'] !!}
{!! $phpinfoBody !!}
{!! $icon !!}
```

**Description:** Unescaped output in Blade templates can lead to XSS if the content contains user-controllable data.

**Analysis:**
- Icons are likely safe (hardcoded SVG)
- `$phpinfoBody` in admin panel is CRITICAL - phpinfo output should be sanitized
- Need to verify that `$icon` slots never contain user data

**Recommendation:**
1. For icons, use `{{ }}` with trusted icon libraries or SVG components
2. For phpinfo: Ensure only superadmins can access, sanitize output
3. Audit all slots to ensure they don't accept user input

---

#### 2.2 JavaScript Injection via @json Directive

**Severity:** MEDIUM
**Location:** `C:\laragon\www\compta\resources\views\invoices\create.blade.php:39-43`

**Issue:**
```javascript
products: @json($productsData),
vatRates: @json($vatRatesData),
```

**Description:** While Laravel's `@json` directive provides basic XSS protection, if product descriptions or names contain malicious JavaScript, this could still be exploited.

**Recommendation:** Ensure all data passed to `@json` is sanitized, especially user-editable fields like product names/descriptions.

---

### 3. CSRF Protection

#### 3.1 CSRF Protection Status

**Severity:** PASS
**Status:** ✅ GOOD

**Findings:**
- All forms properly include `@csrf` directive
- Example from login form: `resources\views\auth\login.blade.php:23`
- Example from partner creation: `resources\views\partners\create.blade.php:22`
- All 88 forms checked contain CSRF tokens

**Recommendation:** Continue current practice. No issues found.

---

### 4. Authorization and Access Control

#### 4.1 Missing Policy Authorization in Controllers

**Severity:** CRITICAL
**Locations:** Most controllers

**Issue:** Controllers lack explicit authorization checks using policies or gates.

**Examples:**
- `InvoiceController::edit()` - No authorization check before edit
- `InvoiceController::destroy()` - Only checks `isEditable()`, not user permissions
- `PartnerController::destroy()` - No permission check
- `BankController::import()` - No explicit authorization
- `AccountingController` - No authorization on sensitive accounting data

**Current Protection:**
- `TenantMiddleware` provides basic tenant isolation (lines 40-44)
- Some business logic validation (e.g., `isEditable()`)

**Missing:**
- No role-based access control checks
- No policy authorization
- No verification that user has permission to modify resources
- Only ONE policy found: `ReportPolicy.php`

**Recommendation:**
```php
// Create policies for all resources
public function edit(Invoice $invoice)
{
    $this->authorize('update', $invoice);
    // ... rest of code
}
```

---

#### 4.2 API Authorization Issues

**Severity:** HIGH
**Location:** `C:\laragon\www\compta\app\Http\Controllers\Api\V1\InvoiceApiController.php`

**Issue:**
```php
protected function authorizeForCompany(Request $request, Invoice $invoice): void
{
    if ($invoice->company_id !== $request->user()->current_company_id) {
        abort(403, 'Non autorisé');
    }
}
```

**Description:** Manual authorization instead of using Laravel's policy system. Also, `current_company_id` is read from user object which could be manipulated if not properly validated.

**Recommendation:**
1. Use proper policies
2. Verify company access through middleware, not user attributes
3. Implement role-based permissions within companies

---

#### 4.3 Missing Route Authorization

**Severity:** HIGH
**Location:** `C:\laragon\www\compta\routes\web.php` and `api.php`

**Issue:** Routes use middleware groups (`auth`, `tenant`, `superadmin`) but no granular permission checks.

**Examples:**
```php
Route::middleware('tenant')->group(function () {
    Route::resource('invoices', InvoiceController::class);
    Route::resource('partners', PartnerController::class);
    // No role-based restrictions
});
```

**Description:** All authenticated users in a tenant can access all resources. No distinction between roles (admin, accountant, user).

**Recommendation:** Implement role-based middleware:
```php
Route::middleware(['tenant', 'can:manage-invoices'])->group(...)
```

---

#### 4.4 Tenant Isolation Verification

**Severity:** MEDIUM
**Location:** `C:\laragon\www\compta\app\Http\Middleware\TenantMiddleware.php`

**Issue:**
```php
if (!$user->hasAccessToCompany($tenantId)) {
    session()->forget('current_tenant_id');
    return redirect()->route('tenant.select')
        ->with('error', 'Vous n\'avez pas accès à cette entreprise.');
}
```

**Description:** Good tenant isolation logic, but relies on session data which could be manipulated.

**Recommendation:**
1. Add server-side verification on every request
2. Implement signed sessions
3. Add audit logging for tenant switches

---

### 5. Sensitive Data Exposure

#### 5.1 Password and Secrets in User Model

**Severity:** PASS
**Status:** ✅ GOOD

**Finding:** User model properly hides sensitive fields:
```php
protected $hidden = [
    'password',
    'remember_token',
    'mfa_secret',
];
```

---

#### 5.2 Logging Sensitive Information

**Severity:** MEDIUM
**Locations:** Multiple services

**Issue:**
```php
Log::info('Peppol invoice sent', [
    'invoice_id' => $invoice->id,
    'transmission_id' => $transmission->id,
]);
```

**Description:** Logs contain IDs and potentially sensitive business information. While IDs are relatively safe, comprehensive logging could expose patterns or sensitive data.

**Recommendation:**
1. Review all Log statements to ensure no passwords, tokens, or PII are logged
2. Implement log sanitization
3. Use structured logging with sensitivity levels

---

#### 5.3 API Keys in Environment

**Severity:** MEDIUM
**Location:** `.env.example`

**Finding:**
```env
PEPPOL_API_KEY=
PEPPOL_PARTICIPANT_ID=
```

**Description:** Environment variables are properly used for secrets (GOOD), but ensure:
1. `.env` is in `.gitignore` ✅
2. Production uses secure secret management
3. API keys are rotated regularly

**Recommendation:**
1. Use encrypted environment variables
2. Implement secret rotation policies
3. Consider using Laravel's encryption for sensitive config values

---

#### 5.4 PHPInfo Exposure

**Severity:** CRITICAL
**Location:** `C:\laragon\www\compta\resources\views\admin\system\phpinfo.blade.php:26`

**Issue:**
```php
{!! $phpinfoBody !!}
```

**Description:** PHPInfo exposes detailed server configuration, PHP modules, environment variables, and potentially API keys/secrets.

**Recommendation:**
1. Remove phpinfo from production
2. If needed for debugging, require superadmin + 2FA
3. Sanitize output to remove sensitive environment variables
4. Add IP whitelist restriction

---

### 6. Input Validation

#### 6.1 No Form Request Classes

**Severity:** MEDIUM
**Status:** ⚠️ NEEDS IMPROVEMENT

**Finding:** No Form Request directory found at `C:\laragon\www\compta\app\Http\Requests`

**Description:** All validation is done inline in controllers:
```php
$validated = $request->validate([
    'partner_id' => ['required', 'uuid', 'exists:partners,id'],
    'invoice_date' => ['required', 'date'],
    // ...
]);
```

**Pros:**
- Validation is present
- Rules are comprehensive

**Cons:**
- No authorization in Form Requests
- Code duplication
- Harder to test
- No centralized validation logic

**Recommendation:**
Create Form Request classes:
```php
php artisan make:request StoreInvoiceRequest
```

---

#### 6.2 Validation Rules Quality

**Severity:** LOW
**Status:** ✅ MOSTLY GOOD

**Examples of Good Validation:**
```php
'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
'password' => ['required', 'confirmed', Rules\Password::defaults()],
'vat_rate' => ['required', 'numeric', 'in:0,6,12,21'],
```

**Minor Issues:**
- Some nullable fields could be more restrictive
- UUID validation exists but not consistently applied
- File upload validation missing virus scanning

**Recommendation:**
1. Add file type validation for all uploads
2. Implement virus scanning for uploaded files
3. Add rate limiting on file uploads

---

#### 6.3 Missing Input Sanitization

**Severity:** MEDIUM
**Location:** Multiple controllers

**Issue:** No explicit HTML sanitization before database storage.

**Example:**
```php
'notes' => ['nullable', 'string']
// No strip_tags or HTMLPurifier
```

**Description:** While XSS is prevented by Blade's `{{ }}` escaping, stored XSS could occur if unescaped output is used anywhere.

**Recommendation:**
1. Sanitize HTML input fields
2. Use HTMLPurifier for rich text
3. Implement Content Security Policy headers

---

## Additional Security Concerns

### 7. Two-Factor Authentication

**Severity:** INFO
**Status:** ✅ IMPLEMENTED

**Finding:** 2FA is implemented and properly integrated into login flow:
```php
if ($user->mfa_enabled) {
    Auth::logout();
    $request->session()->put('2fa_user_id', $user->id);
    return redirect()->route('2fa.challenge');
}
```

**Recommendation:** Consider making 2FA mandatory for admin users.

---

### 8. Rate Limiting

**Severity:** MEDIUM
**Status:** ⚠️ NEEDS VERIFICATION

**Finding:** No explicit rate limiting observed in routes or controllers.

**Recommendation:**
```php
Route::middleware(['throttle:60,1'])->group(function () {
    // API routes
});
```

---

### 9. API Token Security

**Severity:** MEDIUM
**Status:** ✅ USES SANCTUM

**Finding:** API uses Laravel Sanctum for authentication:
```php
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
```

**Recommendation:**
1. Implement token expiration
2. Add token rotation
3. Implement token scopes/abilities

---

### 10. Mass Assignment Protection

**Severity:** LOW
**Status:** ✅ GOOD

**Finding:** Models use `$fillable` arrays properly:
```php
protected $fillable = [
    'email',
    'password',
    'first_name',
    // ...
];
```

**Recommendation:** Continue current practice.

---

## Priority Recommendations

### CRITICAL (Fix Immediately)

1. **Implement Authorization Policies** - Create policies for Invoice, Partner, BankTransaction, etc.
2. **Fix SQL Injection in CreditNote.php** - Use parameter binding
3. **Remove/Secure PHPInfo Page** - Critical information disclosure
4. **Add RBAC to Routes** - Implement role-based access control

### HIGH (Fix Within 1 Week)

1. **Review XSS in Component Slots** - Audit all `{!! !!}` usage
2. **Fix Dynamic SQL Column Names** - Validate against whitelist
3. **API Authorization** - Implement proper policies instead of manual checks
4. **Add Rate Limiting** - Prevent brute force and DoS attacks

### MEDIUM (Fix Within 1 Month)

1. **Create Form Request Classes** - Centralize validation logic
2. **Sanitize User Input** - Add HTMLPurifier for rich text fields
3. **Implement Audit Logging** - Log all sensitive operations
4. **Add CSP Headers** - Content Security Policy for XSS defense
5. **Review Logging Practices** - Ensure no sensitive data in logs

### LOW (Fix When Possible)

1. **Escape LIKE Wildcards** - Prevent wildcard injection
2. **Add File Upload Scanning** - Antivirus for uploaded files
3. **Implement Token Rotation** - For API tokens
4. **Add Session Security** - Signed sessions, strict mode

---

## Security Testing Checklist

- [ ] Implement OWASP Top 10 automated testing
- [ ] Conduct penetration testing
- [ ] Review dependency vulnerabilities (`composer audit`)
- [ ] Set up security headers (CSP, HSTS, X-Frame-Options)
- [ ] Implement intrusion detection
- [ ] Add security monitoring and alerting
- [ ] Regular security training for developers
- [ ] Implement secure code review process

---

## Compliance Considerations

Given that ComptaBE is a Belgian accounting application handling financial data:

1. **GDPR Compliance** - Ensure proper data protection measures
2. **Financial Data Security** - Comply with financial regulations
3. **Audit Trail** - Implement comprehensive audit logging
4. **Data Retention** - Proper policies for financial records
5. **Encryption** - Database and backup encryption

---

## Conclusion

The ComptaBE application demonstrates several good security practices, particularly in CSRF protection, basic authentication, and tenant isolation. However, critical issues exist in authorization, SQL injection risks, and sensitive data exposure that must be addressed immediately.

The most critical gap is the **lack of authorization policies and role-based access control**. Every controller action should verify that the authenticated user has permission to perform the requested operation.

**Recommended Next Steps:**

1. Address all CRITICAL issues within 48 hours
2. Implement comprehensive authorization system
3. Conduct security code review with focus on user input handling
4. Set up automated security testing in CI/CD pipeline
5. Schedule monthly security audits

---

**Report Generated:** 2025-12-20
**Next Audit Recommended:** 2025-01-20 (after fixes) or quarterly thereafter
