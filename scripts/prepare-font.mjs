import { createHash } from 'node:crypto';
import { copyFile, mkdir, readdir, readFile, rm, writeFile } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');

const iranYekanSourceDir = resolve(root, 'resources/fonts/IRANYekanXVF.woff2.base64');
const iranYekanTarget = resolve(root, 'public/fonts/IRANYekanXVF.woff2');
const obsoleteViteTarget = resolve(root, 'resources/fonts/IRANYekanXVF.woff2');
const iranYekanExpectedSha256 = '3a3a62a935a549584d610e38b4b27ea30053c6fe137ca0186687dec038cfcf88';
const iranYekanExpectedSize = 95404;

const iranYekanParts = (await readdir(iranYekanSourceDir))
    .filter((file) => file.endsWith('.txt'))
    .sort();

if (iranYekanParts.length === 0) {
    throw new Error('IRANYekan font source parts are missing.');
}

const iranYekanBase64 = (await Promise.all(
    iranYekanParts.map((file) => readFile(resolve(iranYekanSourceDir, file), 'utf8')),
)).join('').replace(/\s+/g, '');

const iranYekanFont = Buffer.from(iranYekanBase64, 'base64');
const iranYekanSha256 = createHash('sha256').update(iranYekanFont).digest('hex');

if (iranYekanFont.length !== iranYekanExpectedSize || iranYekanSha256 !== iranYekanExpectedSha256) {
    throw new Error(`IRANYekan font integrity check failed: size=${iranYekanFont.length}, sha256=${iranYekanSha256}`);
}

await mkdir(dirname(iranYekanTarget), { recursive: true });
await writeFile(iranYekanTarget, iranYekanFont);
await rm(obsoleteViteTarget, { force: true });

console.log(`IRANYekan font prepared: ${iranYekanFont.length} bytes (${iranYekanSha256})`);

const fontAwesomeSourceRoot = resolve(root, 'resources/fonts/fontawesome');
const fontAwesomeCssSource = resolve(root, 'resources/css/fontawesome/css');
const fontAwesomeRoot = resolve(root, 'public/fontawesome');
const fontAwesomeCssTarget = resolve(fontAwesomeRoot, 'css');
const fontAwesomeWebfontsTarget = resolve(fontAwesomeRoot, 'webfonts');
const fontAwesomeFonts = {
    'fa-brands-400.woff2': {
        size: 115380,
        sha256: '57f8508ef396d096c48c6ad56257e0fcc510a5560bb220c13339be93543ff868',
    },
    'fa-light-300.woff2': {
        size: 380172,
        sha256: '4bf8e8608d8ddb833c06b636235b13f3d6926de361fbf590f369405fb06c4707',
    },
};

await rm(fontAwesomeRoot, { recursive: true, force: true });
await mkdir(fontAwesomeCssTarget, { recursive: true });
await mkdir(fontAwesomeWebfontsTarget, { recursive: true });

for (const filename of ['brands.css', 'light.css']) {
    const source = resolve(fontAwesomeCssSource, filename);
    const css = await readFile(source, 'utf8');

    if (!css.includes('Font Awesome Pro 7.3.1')) {
        throw new Error(`Unexpected Font Awesome stylesheet source: ${source}`);
    }

    await copyFile(source, resolve(fontAwesomeCssTarget, filename));
}

for (const [filename, expected] of Object.entries(fontAwesomeFonts)) {
    const source = resolve(fontAwesomeSourceRoot, filename);
    const font = await readFile(source);

    if (font.length < 12 || font.subarray(0, 4).toString('ascii') !== 'wOF2') {
        throw new Error(`${filename} is not a valid WOFF2 file.`);
    }

    const declaredLength = font.readUInt32BE(8);
    const sha256 = createHash('sha256').update(font).digest('hex');

    if (declaredLength !== font.length || font.length !== expected.size || sha256 !== expected.sha256) {
        throw new Error(`${filename} integrity check failed: declared=${declaredLength}, size=${font.length}, sha256=${sha256}`);
    }

    await copyFile(source, resolve(fontAwesomeWebfontsTarget, filename));
    console.log(`Font Awesome prepared: ${filename} (${font.length} bytes, ${sha256})`);
}

const fontAwesomeCoreCss = `/* Minimal Font Awesome core required by the bundled style files. */
.fa,
.fal,
.fab,
.fa-light,
.fa-brands {
    display: var(--fa-display, inline-block);
    font-family: var(--fa-family);
    font-style: normal;
    font-variant: normal;
    font-weight: var(--fa-style);
    line-height: 1;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.fa::before,
.fal::before,
.fab::before,
.fa-light::before,
.fa-brands::before {
    content: var(--fa-content, var(--fa));
}
`;

await writeFile(resolve(fontAwesomeCssTarget, 'fontawesome.css'), fontAwesomeCoreCss);
