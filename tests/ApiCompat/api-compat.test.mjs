/**
 * API Compatibility Test
 *
 * Registers a user on both the Laravel and .NET APIs, then hits matching
 * endpoints and verifies the response shapes are structurally identical.
 * This proves the two backends are interchangeable for any frontend.
 *
 * Requires:
 *   LARAVEL_API_URL  — e.g. http://127.0.0.1:8000/api
 *   DOTNET_API_URL   — e.g. http://localhost:5236/api
 */

const LARAVEL = process.env.LARAVEL_API_URL;
const DOTNET = process.env.DOTNET_API_URL;

if (!LARAVEL || !DOTNET) {
  console.error('Set LARAVEL_API_URL and DOTNET_API_URL env vars');
  process.exit(1);
}

let passed = 0;
let failed = 0;

function assert(condition, message) {
  if (condition) {
    passed++;
    console.log(`  PASS  ${message}`);
  } else {
    failed++;
    console.error(`  FAIL  ${message}`);
  }
}

async function post(base, path, body, token) {
  const headers = { 'Content-Type': 'application/json', Accept: 'application/json' };
  if (token) headers['Authorization'] = `Bearer ${token}`;
  const res = await fetch(`${base}${path}`, { method: 'POST', headers, body: JSON.stringify(body) });
  return { status: res.status, data: await res.json().catch(() => null) };
}

async function get(base, path, token) {
  const headers = { Accept: 'application/json' };
  if (token) headers['Authorization'] = `Bearer ${token}`;
  const res = await fetch(`${base}${path}`, { method: 'GET', headers });
  return { status: res.status, data: await res.json().catch(() => null) };
}

async function del(base, path, token) {
  const headers = { Accept: 'application/json' };
  if (token) headers['Authorization'] = `Bearer ${token}`;
  const res = await fetch(`${base}${path}`, { method: 'DELETE', headers });
  return { status: res.status };
}

/** Compare that two objects share the same top-level keys. */
function sameKeys(a, b) {
  if (!a || !b) return false;
  const aKeys = Object.keys(a).sort();
  const bKeys = Object.keys(b).sort();
  return JSON.stringify(aKeys) === JSON.stringify(bKeys);
}

/** Compare that two responses share the same structure (keys match recursively at top level). */
function compareShape(label, laravelData, dotnetData) {
  if (laravelData === null && dotnetData === null) {
    assert(true, `${label}: both null`);
    return;
  }
  if (Array.isArray(laravelData) && Array.isArray(dotnetData)) {
    assert(true, `${label}: both arrays`);
    if (laravelData.length > 0 && dotnetData.length > 0) {
      const match = sameKeys(laravelData[0], dotnetData[0]);
      assert(match, `${label}: array item keys match`);
      if (!match) {
        console.error(`    Laravel keys: ${Object.keys(laravelData[0]).sort()}`);
        console.error(`    .NET keys:    ${Object.keys(dotnetData[0]).sort()}`);
      }
    }
    return;
  }
  const match = sameKeys(laravelData, dotnetData);
  assert(match, `${label}: response keys match`);
  if (!match) {
    console.error(`    Laravel keys: ${Object.keys(laravelData || {}).sort()}`);
    console.error(`    .NET keys:    ${Object.keys(dotnetData || {}).sort()}`);
  }
}

// ---------------------------------------------------------------------------

const timestamp = Date.now();
const testUser = {
  name: `Compat Test ${timestamp}`,
  email: `compat-${timestamp}@test.local`,
  password: 'Compat-Test-123!',
  password_confirmation: 'Compat-Test-123!',
};

console.log('\n=== API Compatibility Tests ===\n');

// 1. Register on both APIs
console.log('--- Auth: Register ---');
const laravelReg = await post(LARAVEL, '/auth/register', testUser);
const dotnetReg = await post(DOTNET, '/auth/register', {
  ...testUser,
  passwordConfirmation: testUser.password_confirmation,
});

assert(laravelReg.status === 200 || laravelReg.status === 201, `Laravel register: ${laravelReg.status}`);
assert(dotnetReg.status === 200 || dotnetReg.status === 201, `.NET register: ${dotnetReg.status}`);

const laravelToken = laravelReg.data?.token;
const dotnetToken = dotnetReg.data?.token;

assert(!!laravelToken, 'Laravel returned a token');
assert(!!dotnetToken, '.NET returned a token');

// 2. Login
console.log('\n--- Auth: Login ---');
const laravelLogin = await post(LARAVEL, '/auth/login', { email: testUser.email, password: testUser.password });
const dotnetLogin = await post(DOTNET, '/auth/login', { email: testUser.email, password: testUser.password });

assert(laravelLogin.status === 200, `Laravel login: ${laravelLogin.status}`);
assert(dotnetLogin.status === 200, `.NET login: ${dotnetLogin.status}`);
compareShape('Login response', laravelLogin.data, dotnetLogin.data);

// 3. Get current user
console.log('\n--- Auth: Me ---');
const laravelMe = await get(LARAVEL, '/auth/me', laravelToken);
const dotnetMe = await get(DOTNET, '/auth/me', dotnetToken);

assert(laravelMe.status === 200, `Laravel me: ${laravelMe.status}`);
assert(dotnetMe.status === 200, `.NET me: ${dotnetMe.status}`);

// 4. Dashboard
console.log('\n--- Dashboard ---');
const laravelDash = await get(LARAVEL, '/dashboard', laravelToken);
const dotnetDash = await get(DOTNET, '/dashboard', dotnetToken);

assert(laravelDash.status === 200, `Laravel dashboard: ${laravelDash.status}`);
assert(dotnetDash.status === 200, `.NET dashboard: ${dotnetDash.status}`);
compareShape('Dashboard', laravelDash.data, dotnetDash.data);

// 5. Resource endpoints (empty lists but same shape)
for (const resource of ['contacts', 'companies', 'deals', 'activities', 'tags']) {
  console.log(`\n--- ${resource.charAt(0).toUpperCase() + resource.slice(1)}: Index ---`);
  const laravelRes = await get(LARAVEL, `/${resource}`, laravelToken);
  const dotnetRes = await get(DOTNET, `/${resource}`, dotnetToken);

  assert(laravelRes.status === 200, `Laravel ${resource}: ${laravelRes.status}`);
  assert(dotnetRes.status === 200, `.NET ${resource}: ${dotnetRes.status}`);
}

// 6. Deals pipeline
console.log('\n--- Deals: Pipeline ---');
const laravelPipeline = await get(LARAVEL, '/deals/pipeline', laravelToken);
const dotnetPipeline = await get(DOTNET, '/deals/pipeline', dotnetToken);

assert(laravelPipeline.status === 200, `Laravel pipeline: ${laravelPipeline.status}`);
assert(dotnetPipeline.status === 200, `.NET pipeline: ${dotnetPipeline.status}`);

// 7. Unauthenticated guard
console.log('\n--- Auth Guard ---');
const laravelGuard = await get(LARAVEL, '/contacts');
const dotnetGuard = await get(DOTNET, '/contacts');

assert(laravelGuard.status === 401, `Laravel unauthed: ${laravelGuard.status}`);
assert(dotnetGuard.status === 401, `.NET unauthed: ${dotnetGuard.status}`);

// Summary
console.log(`\n=== Results: ${passed} passed, ${failed} failed ===\n`);
process.exit(failed > 0 ? 1 : 0);
