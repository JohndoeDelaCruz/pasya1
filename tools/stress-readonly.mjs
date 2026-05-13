import { performance } from 'node:perf_hooks';

const args = Object.fromEntries(
  process.argv.slice(2).map((arg) => {
    const [key, ...value] = arg.replace(/^--/, '').split('=');
    return [key, value.join('=') || 'true'];
  }),
);

const baseUrl = (args.baseUrl || process.env.BASE_URL || '').replace(/\/+$/, '');
const vus = Number.parseInt(args.vus || '10', 10);
const durationSeconds = Number.parseInt(args.duration || '30', 10);
const thinkMs = Number.parseInt(args.thinkMs || '750', 10);

if (!baseUrl) {
  console.error('Missing --baseUrl=https://...');
  process.exit(1);
}

const paths = (args.paths ? args.paths.split(',').map((path) => path.trim()).filter(Boolean) : [
  '/up',
  '/',
  '/app',
  '/api/map/filters',
  '/api/map/data?view=production',
  '/api/weather/current?municipality=La%20Trinidad',
]);

const startedAt = performance.now();
const stopAt = startedAt + durationSeconds * 1000;
const results = [];

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

function percentile(values, p) {
  if (values.length === 0) {
    return 0;
  }

  const sorted = [...values].sort((a, b) => a - b);
  const index = Math.ceil((p / 100) * sorted.length) - 1;
  return sorted[Math.max(0, Math.min(index, sorted.length - 1))];
}

async function hit(path, vu) {
  const url = `${baseUrl}${path}`;
  const requestStartedAt = performance.now();

  try {
    const response = await fetch(url, {
      redirect: 'manual',
      headers: {
        'Accept': 'text/html,application/json;q=0.9,*/*;q=0.8',
        'User-Agent': `PASYA-readonly-stress/${vus}vu vu-${vu}`,
      },
    });

    await response.arrayBuffer();

    results.push({
      path,
      status: response.status,
      ms: performance.now() - requestStartedAt,
      ok: response.status < 500,
    });
  } catch (error) {
    results.push({
      path,
      status: 'ERR',
      ms: performance.now() - requestStartedAt,
      ok: false,
      error: error?.message || String(error),
    });
  }
}

async function virtualUser(vu) {
  let index = vu % paths.length;

  while (performance.now() < stopAt) {
    await hit(paths[index], vu);
    index = (index + 1) % paths.length;
    await sleep(thinkMs);
  }
}

console.log(JSON.stringify({
  baseUrl,
  vus,
  durationSeconds,
  thinkMs,
  paths,
  startedAt: new Date().toISOString(),
}, null, 2));

await Promise.all(Array.from({ length: vus }, (_, index) => virtualUser(index + 1)));

const elapsedSeconds = (performance.now() - startedAt) / 1000;
const byPath = new Map();

for (const result of results) {
  if (!byPath.has(result.path)) {
    byPath.set(result.path, []);
  }

  byPath.get(result.path).push(result);
}

const summary = {
  totalRequests: results.length,
  elapsedSeconds: Number(elapsedSeconds.toFixed(2)),
  requestsPerSecond: Number((results.length / elapsedSeconds).toFixed(2)),
  failures: results.filter((result) => !result.ok).length,
  statusCounts: results.reduce((counts, result) => {
    counts[result.status] = (counts[result.status] || 0) + 1;
    return counts;
  }, {}),
  paths: {},
};

for (const [path, pathResults] of byPath.entries()) {
  const timings = pathResults.map((result) => result.ms);

  summary.paths[path] = {
    requests: pathResults.length,
    failures: pathResults.filter((result) => !result.ok).length,
    statuses: pathResults.reduce((counts, result) => {
      counts[result.status] = (counts[result.status] || 0) + 1;
      return counts;
    }, {}),
    minMs: Number(Math.min(...timings).toFixed(1)),
    p50Ms: Number(percentile(timings, 50).toFixed(1)),
    p95Ms: Number(percentile(timings, 95).toFixed(1)),
    maxMs: Number(Math.max(...timings).toFixed(1)),
  };
}

console.log(JSON.stringify(summary, null, 2));

if (summary.failures > 0 || Object.keys(summary.statusCounts).some((status) => Number(status) >= 500)) {
  process.exitCode = 1;
}
