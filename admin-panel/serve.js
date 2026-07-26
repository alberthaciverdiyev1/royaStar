import express from 'express';
import http from 'http';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const distDir = path.join(__dirname, 'dist');
const PORT = process.env.PORT || 3000;
const API_TARGET = process.env.API_TARGET || 'http://127.0.0.1:8000';
const VITE_DEV = process.env.VITE_DEV_URL || 'http://127.0.0.1:5173';

let devMode = process.env.DEV !== undefined; // force via DEV=true

const app = express();

function proxyRequest(req, res, target) {
  const t = new URL(target);
  const opts = {
    hostname: t.hostname,
    port: t.port,
    path: req.originalUrl || req.url,
    method: req.method,
    headers: { ...req.headers, host: t.host, connection: 'close' },
  };
  const pr = http.request(opts, (pr2) => {
    res.writeHead(pr2.statusCode, pr2.headers);
    pr2.pipe(res, { end: true });
  });
  pr.on('error', () => { if (!res.headersSent) res.writeHead(502); res.end(); });
  req.pipe(pr, { end: true });
}

function proxyWs(req, socket, head, target) {
  const t = new URL(target);
  const opts = {
    hostname: t.hostname,
    port: t.port,
    path: req.url,
    method: 'GET',
    headers: req.headers,
  };
  const pr = http.request(opts);
  pr.on('upgrade', (_, wsSocket) => {
    socket.write('HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nConnection: Upgrade\r\n\r\n');
    wsSocket.pipe(socket).pipe(wsSocket);
  });
  pr.on('error', () => socket.destroy());
  pr.end();
}

// API proxy
app.use('/api', (req, res) => proxyRequest(req, res, API_TARGET));

// Docs & storage redirects to Laravel directly
app.use('/docs', (req, res) => res.redirect(301, `${API_TARGET}${req.originalUrl}`));
app.use('/storage', (req, res) => res.redirect(301, `${API_TARGET}${req.originalUrl}`));

// Static assets (immutable, always from dist even in dev — saves a Vite round-trip)
app.use('/assets', express.static(path.join(distDir, 'assets'), { maxAge: '1y', immutable: true }));

// Everything else: Vite dev server (if available) or static SPA
app.use((req, res) => {
  if (devMode) {
    proxyRequest(req, res, VITE_DEV);
  } else {
    express.static(distDir)(req, res, () => {
      res.sendFile(path.join(distDir, 'index.html'));
    });
  }
});

// Detect Vite dev server on startup (unless already forced)
function detectDevMode() {
  if (devMode) return Promise.resolve();
  return new Promise((resolve) => {
    const req = http.get(VITE_DEV, (r) => { r.resume(); devMode = true; resolve(); });
    req.on('error', () => resolve());
    req.setTimeout(500, () => { req.destroy(); resolve(); });
  });
}

detectDevMode().then(() => {
  const server = app.listen(PORT, () => {
    console.log(`\n  RoyaStars Admin Panel`);
    console.log(`  ${'='.repeat(30)}`);
    console.log(`  URL   http://127.0.0.1:${PORT}`);
    console.log(`  API   ${API_TARGET}`);
    console.log(`  Mode  ${devMode ? 'DEV (Vite hot-reload)' : 'PROD (static dist/)'}\n`);
  });

  // WebSocket upgrade for Vite HMR
  server.on('upgrade', (req, socket, head) => {
    if (devMode) proxyWs(req, socket, head, VITE_DEV);
    else socket.destroy();
  });
});
