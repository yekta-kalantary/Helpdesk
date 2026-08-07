import { createHash } from 'node:crypto';
import { mkdir, readdir, readFile, writeFile } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const sourceDir = resolve(root, 'resources/fonts/IRANYekanXVF.woff2.base64');
const target = resolve(root, 'public/fonts/IRANYekanXVF.woff2');
const expectedSha256 = '3a3a62a935a549584d610e38b4b27ea30053c6fe137ca0186687dec038cfcf88';
const expectedSize = 95404;

const parts = (await readdir(sourceDir))
    .filter((file) => file.endsWith('.txt'))
    .sort();

if (parts.length === 0) {
    throw new Error('IRANYekan font source parts are missing.');
}

const base64 = (await Promise.all(
    parts.map((file) => readFile(resolve(sourceDir, file), 'utf8')),
)).join('').replace(/\s+/g, '');

const font = Buffer.from(base64, 'base64');
const sha256 = createHash('sha256').update(font).digest('hex');

if (font.length !== expectedSize || sha256 !== expectedSha256) {
    throw new Error(`IRANYekan font integrity check failed: size=${font.length}, sha256=${sha256}`);
}

await mkdir(dirname(target), { recursive: true });
await writeFile(target, font);

console.log(`IRANYekan font prepared: ${font.length} bytes (${sha256})`);
