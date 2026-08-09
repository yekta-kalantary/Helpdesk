import { createHash } from 'node:crypto';
import { copyFile, mkdir, readFile, rm } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');

const assets = [
    {
        name: 'IRANYekanXVF.woff2',
        source: resolve(root, 'resources/fonts/IRANYekanXVF.woff2'),
        target: resolve(root, 'public/fonts/IRANYekanXVF.woff2'),
        size: 95404,
        sha256: '3a3a62a935a549584d610e38b4b27ea30053c6fe137ca0186687dec038cfcf88',
    },
    {
        name: 'fa-brands-400.woff2',
        source: resolve(root, 'resources/fonts/fa-brands-400.woff2'),
        target: resolve(root, 'public/fontawesome/webfonts/fa-brands-400.woff2'),
        size: 115380,
        sha256: '57f8508ef396d096c48c6ad56257e0fcc510a5560bb220c13339be93543ff868',
    },
    {
        name: 'fa-light-300.woff2',
        source: resolve(root, 'resources/fonts/fa-light-300.woff2'),
        target: resolve(root, 'public/fontawesome/webfonts/fa-light-300.woff2'),
        size: 380172,
        sha256: '4bf8e8608d8ddb833c06b636235b13f3d6926de361fbf590f369405fb06c4707',
    },
];

async function validateAndCopy({ name, source, target, size, sha256: expectedSha256 }) {
    const font = await readFile(source);

    if (font.length < 12 || font.subarray(0, 4).toString('ascii') !== 'wOF2') {
        throw new Error(`${name} is not a valid WOFF2 file.`);
    }

    const declaredLength = font.readUInt32BE(8);
    const actualSha256 = createHash('sha256').update(font).digest('hex');

    if (declaredLength !== font.length) {
        throw new Error(`${name} WOFF2 length mismatch: declared=${declaredLength}, actual=${font.length}`);
    }

    if (font.length !== size || actualSha256 !== expectedSha256) {
        throw new Error(`${name} integrity check failed: size=${font.length}, sha256=${actualSha256}`);
    }

    await mkdir(dirname(target), { recursive: true });
    await copyFile(source, target);

    console.log(`${name} prepared: ${font.length} bytes (${actualSha256})`);
}

for (const asset of assets) {
    await validateAndCopy(asset);
}

const fontAwesomeRoot = resolve(root, 'public/fontawesome');
const fontAwesomeCssTarget = resolve(fontAwesomeRoot, 'css');
const fontAwesomeCssSource = resolve(root, 'resources/css/fontawesome/css');

await mkdir(fontAwesomeCssTarget, { recursive: true });

for (const [sourceName, targetName] of [
    ['base.css', 'fontawesome.css'],
    ['light.css', 'light.css'],
    ['brands.css', 'brands.css'],
]) {
    await copyFile(resolve(fontAwesomeCssSource, sourceName), resolve(fontAwesomeCssTarget, targetName));
}

// Remove obsolete Vite-era generated font if it exists from an older checkout.
await rm(resolve(root, 'resources/css/fontawesome/webfonts'), { recursive: true, force: true });
