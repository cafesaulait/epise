import { cp, mkdir, readdir, rm } from 'node:fs/promises';
import path from 'node:path';

const dist = path.resolve('dist');
const french = path.join(dist, 'fr');
const english = path.join(dist, 'en', 'en');

async function copyContents(source, destination) {
  await mkdir(destination, { recursive: true });
  for (const entry of await readdir(source)) {
    await cp(path.join(source, entry), path.join(destination, entry), {
      recursive: true,
      force: true,
    });
  }
}

await copyContents(french, dist);
await rm(french, { recursive: true, force: true });
await copyContents(english, path.join(dist, 'en'));
await rm(english, { recursive: true, force: true });
